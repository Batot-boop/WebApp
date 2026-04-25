<?php
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
?>