<?php
// config.php
define('RAPIDAPI_KEY', '105e88c40dmsha4458d8b30434eap1222b6jsn580b40576c91');
define('DB_HOST', 'localhost');
define('DB_NAME', 'movie_tracker');
define('DB_USER', 'root');
define('DB_PASS', '');

// Auth and Watchlist functions
session_start();

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function getCurrentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function registerUser(string $username, string $email, string $password): array {
    require_once 'config.php';
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) return ['success' => false, 'message' => 'DB connection failed'];

    $username = trim($username);
    $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
    if (!$email || empty($username) || strlen($password) < 6) {
        return ['success' => false, 'message' => 'Invalid input.'];
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param('ss', $email, $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close(); $conn->close();
        return ['success' => false, 'message' => 'Username or email already exists.'];
    }
    $stmt->close();

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $username, $email, $hash);
    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        session_regenerate_id(true);
        $stmt->close(); $conn->close();
        return ['success' => true, 'message' => 'Account created.'];
    }
    $stmt->close(); $conn->close();
    return ['success' => false, 'message' => 'Registration failed.'];
}

function loginUser(string $email, string $password): array {
    require_once 'config.php';
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) return ['success' => false, 'message' => 'DB connection failed'];

    $email = trim($email);
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            session_regenerate_id(true);
            $stmt->close(); $conn->close();
            return ['success' => true, 'message' => 'Login successful.'];
        }
    }
    $stmt->close(); $conn->close();
    return ['success' => false, 'message' => 'Invalid credentials.'];
}

function logoutUser(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]);
    }
    session_destroy();
}

// Route auth actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auth_action'])) {
    header('Content-Type: application/json');
    $action = $_POST['auth_action'];
    $response = ['success' => false, 'message' => 'Unknown action'];
    switch ($action) {
        case 'signup':
            $response = registerUser($_POST['username'] ?? '', $_POST['email'] ?? '', $_POST['password'] ?? '');
            break;
        case 'login':
            $response = loginUser($_POST['email'] ?? '', $_POST['password'] ?? '');
            break;
        case 'logout':
            logoutUser();
            $response = ['success' => true, 'message' => 'Logged out.'];
            break;
    }
    echo json_encode($response);
    exit;
}

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