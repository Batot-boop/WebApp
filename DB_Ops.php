<?php
// DB_Ops.php

// ── Connection ────────────────────────────────────────────────
function getConnection() {
    $host   = 'localhost';
    $db     = 'movie_tracker';
    $user   = 'root';
    $pass   = '';                // change in production

    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'DB connection failed']);
        exit;
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

// ── Validation Helper ─────────────────────────────────────────
function validateMovieInput($data) {
    $errors = [];

    if (empty(trim($data['title'] ?? '')))
        $errors[] = 'Movie title is required.';

    if (empty($data['tmdb_id']) || !is_numeric($data['tmdb_id']))
        $errors[] = 'Invalid movie ID.';

    $allowed_statuses = ['want_to_watch', 'watching', 'watched'];
    if (!empty($data['status']) && !in_array($data['status'], $allowed_statuses))
        $errors[] = 'Invalid status value.';

    if (!empty($data['user_rating'])) {
        $r = (int)$data['user_rating'];
        if ($r < 1 || $r > 10)
            $errors[] = 'Rating must be between 1 and 10.';
    }

    return $errors;
}

// ── CREATE ────────────────────────────────────────────────────
function addToWatchlist($data) {
    $errors = validateMovieInput($data);
    if (!empty($errors))
        return ['success' => false, 'message' => implode(' ', $errors)];

    $conn = getConnection();

    // Check for duplicate
    $check = $conn->prepare(
        "SELECT id FROM watchlist WHERE user_id = ? AND tmdb_id = ?"
    );
    $check->bind_param('ii', $data['user_id'], $data['tmdb_id']);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $check->close(); $conn->close();
        return ['success' => false, 'message' => 'Movie already in your watchlist.'];
    }
    $check->close();

    $stmt = $conn->prepare(
        "INSERT INTO watchlist 
            (user_id, tmdb_id, title, poster_path, genre, release_year, status, notes)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        'iissssss',
        $data['user_id'],
        $data['tmdb_id'],
        $data['title'],
        $data['poster_path'],
        $data['genre'],
        $data['release_year'],
        $data['status'],
        $data['notes']
    );

    if ($stmt->execute()) {
        $result = ['success' => true, 'message' => 'Movie added!', 'id' => $conn->insert_id];
    } else {
        $result = ['success' => false, 'message' => 'Failed to add movie.'];
    }

    $stmt->close(); $conn->close();
    return $result;
}

// ── READ (all + search) ───────────────────────────────────────
function getWatchlist($user_id, $search = '', $status_filter = '') {
    $conn  = getConnection();
    $query = "SELECT * FROM watchlist WHERE user_id = ?";
    $types = 'i';
    $params = [$user_id];

    if (!empty($search)) {
        $query  .= " AND title LIKE ?";
        $types  .= 's';
        $params[] = "%$search%";
    }

    if (!empty($status_filter)) {
        $query  .= " AND status = ?";
        $types  .= 's';
        $params[] = $status_filter;
    }

    $query .= " ORDER BY added_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt->close(); $conn->close();
    return ['success' => true, 'data' => $rows];
}

// ── UPDATE ────────────────────────────────────────────────────
function updateWatchlistEntry($data) {
    $conn = getConnection();

    $stmt = $conn->prepare(
        "UPDATE watchlist 
         SET status = ?, user_rating = ?, notes = ?
         WHERE id = ? AND user_id = ?"
    );
    $stmt->bind_param(
        'sisii',
        $data['status'],
        $data['user_rating'],
        $data['notes'],
        $data['id'],
        $data['user_id']
    );

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $result = ['success' => true, 'message' => 'Updated successfully.'];
    } else {
        $result = ['success' => false, 'message' => 'Update failed or nothing changed.'];
    }

    $stmt->close(); $conn->close();
    return $result;
}

// ── DELETE ────────────────────────────────────────────────────
function deleteFromWatchlist($id, $user_id) {
    $conn = getConnection();

    $stmt = $conn->prepare(
        "DELETE FROM watchlist WHERE id = ? AND user_id = ?"
    );
    $stmt->bind_param('ii', $id, $user_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $result = ['success' => true, 'message' => 'Movie removed.'];
    } else {
        $result = ['success' => false, 'message' => 'Delete failed.'];
    }

    $stmt->close(); $conn->close();
    return $result;
}

// ── ROUTER (called via AJAX) ──────────────────────────────────
// Other team members will call this file via fetch()
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input  = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    switch ($action) {
        case 'add':    echo json_encode(addToWatchlist($input));                             break;
        case 'read':   echo json_encode(getWatchlist($input['user_id'], $input['search'] ?? '', $input['status'] ?? '')); break;
        case 'update': echo json_encode(updateWatchlistEntry($input));                       break;
        case 'delete': echo json_encode(deleteFromWatchlist($input['id'], $input['user_id'])); break;
        default:       echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }
}
?>
