<?php
require_once 'config.php';
require_once 'auth.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please sign in.']);
    exit;
}

$user_id = getCurrentUserId();

function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'DB connection failed']);
        exit;
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

function addToWatchlist($title, $image, $user_id) {
    $conn = getConnection();
    $title = trim($title);
    $image = trim($image);

    if (empty($title) || empty($image)) {
        $conn->close();
        return ['success' => false, 'message' => 'Title and image URL are required.'];
    }

    $stmt = $conn->prepare("INSERT INTO watchlist (title, image, id_user) VALUES (?, ?, ?)");
    if (!$stmt) {
        $error = $conn->error;
        $conn->close();
        return ['success' => false, 'message' => 'Prepare failed: ' . $error];
    }

    $stmt->bind_param('ssi', $title, $image, $user_id);
    if ($stmt->execute()) {
        $id = $conn->insert_id;
        $stmt->close();
        $conn->close();
        return ['success' => true, 'message' => 'Movie added!', 'id' => $id];
    } else {
        $error = $stmt->error;
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Insert failed: ' . $error];
    }
}

function getWatchlist($user_id) {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT ID, title, image FROM watchlist WHERE id_user = ? ORDER BY ID DESC");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();
    return ['success' => true, 'data' => $rows];
}

function updateWatchlistEntry($id, $title, $image, $user_id) {
    $conn = getConnection();
    $title = trim($title);
    $image = trim($image);
    if (empty($title) || empty($image)) {
        $conn->close();
        return ['success' => false, 'message' => 'Title and image are required.'];
    }

    $stmt = $conn->prepare("UPDATE watchlist SET title = ?, image = ? WHERE ID = ? AND id_user = ?");
    if (!$stmt) {
        $error = $conn->error;
        $conn->close();
        return ['success' => false, 'message' => 'Prepare failed: ' . $error];
    }

    $stmt->bind_param('ssii', $title, $image, $id, $user_id);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $stmt->close();
        $conn->close();
        return ['success' => true, 'message' => 'Updated successfully.'];
    }
    $error = $stmt->error;
    $stmt->close();
    $conn->close();
    return ['success' => false, 'message' => 'Update failed: ' . ($error ?: 'Nothing changed.')];
}

function deleteFromWatchlist($id, $user_id) {
    $conn = getConnection();
    $stmt = $conn->prepare("DELETE FROM watchlist WHERE ID = ? AND id_user = ?");
    $stmt->bind_param('ii', $id, $user_id);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $stmt->close();
        $conn->close();
        return ['success' => true, 'message' => 'Movie removed.'];
    }
    $error = $stmt->error;
    $stmt->close();
    $conn->close();
    return ['success' => false, 'message' => 'Delete failed: ' . ($error ?: 'No such entry.')];
}

// Router
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'add':
        if (empty($input['title']) || empty($input['image'])) {
            echo json_encode(['success' => false, 'message' => 'Title and image are required.']);
        } else {
            echo json_encode(addToWatchlist($input['title'], $input['image'], $user_id));
        }
        break;

    case 'read':
        echo json_encode(getWatchlist($user_id));
        break;

    case 'update':
        if (empty($input['id']) || empty($input['title']) || empty($input['image'])) {
            echo json_encode(['success' => false, 'message' => 'ID, title, and image are required.']);
        } else {
            echo json_encode(updateWatchlistEntry($input['id'], $input['title'], $input['image'], $user_id));
        }
        break;

    case 'delete':
        if (empty($input['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID missing.']);
        } else {
            echo json_encode(deleteFromWatchlist($input['id'], $user_id));
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}