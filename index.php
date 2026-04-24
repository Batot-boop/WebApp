<?php
// Auth functions
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
}  // starts session and defines login functions
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MovieWatchlist</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>

<main>
    <!-- HOME PAGE -->
    <div id="home-page" class="page-section active">
        <section class="search-section">
            <form class="search-form" id="searchForm">
                <div class="input-group">
                    <label for="primaryTitle"><i class="fas fa-film"></i> Title</label>
                    <input type="text" id="primaryTitle" placeholder="e.g. Inception, The Matrix...">
                </div>
                <div class="input-group">
                    <label for="genreSelect"><i class="fas fa-tags"></i> Genre</label>
                    <select id="genreSelect">
                        <option value="">All Genres</option>
                        <option value="Drama">Drama</option>
                        <option value="Comedy">Comedy</option>
                        <option value="Action">Action</option>
                        <option value="Romance">Romance</option>
                        <option value="Horror">Horror</option>
                        <option value="Adventure">Adventure</option>
                        <option value="Family">Family</option>
                        <option value="Animation">Animation</option>
                    </select>
                </div>
                <button type="submit"><i class="fas fa-search"></i> Search</button>
            </form>

            <h3 class="section-title"><i class="fas fa-clapperboard"></i> <span id="movieSectionTitle">Popular Movies</span></h3>
            <div id="movieContainer" class="movies-grid">
                <p>Loading popular movies...</p>
            </div>
        </section>

        <!-- My Watchlist (directly from DB) -->
        <section class="db-panel">
            <h3><i class="fas fa-database"></i> My Watchlist</h3>
            <div id="moviesDB" class="movies-grid">
                <p>Loading your watchlist...</p>
            </div>
        </section>
    </div>

    <!-- WATCHLIST PAGE (same content) -->
    <div id="watchlist-page" class="page-section">
        <div class="watchlist-container">
            <h2><i class="fas fa-bookmark"></i> My Watchlist</h2>
            <div id="watchlistMovies" class="movies-grid"></div>
        </div>
    </div>

    <!-- SIGN IN PAGE -->
    <div id="signin-page" class="page-section">
        <div class="auth-form">
            <h2><i class="fas fa-sign-in-alt"></i> Sign In</h2>
            <form id="signinForm" method="post">
                <input type="hidden" name="auth_action" value="login">
                <div class="form-group">
                    <label for="signinEmail">Email</label>
                    <input type="email" id="signinEmail" name="email" placeholder="your@email.com" required>
                </div>
                <div class="form-group">
                    <label for="signinPassword">Password</label>
                    <input type="password" id="signinPassword" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit">Sign In</button>
            </form>
            <p>Don't have an account? <a data-page="signup" class="switch-page">Sign Up</a></p>
        </div>
    </div>

    <!-- SIGN UP PAGE -->
    <div id="signup-page" class="page-section">
        <div class="auth-form">
            <h2><i class="fas fa-user-plus"></i> Create Account</h2>
            <form id="signupForm" method="post">
                <input type="hidden" name="auth_action" value="signup">
                <div class="form-group">
                    <label for="signupName">Username</label>
                    <input type="text" id="signupName" name="username" placeholder="Mohamed" required>
                </div>
                <div class="form-group">
                    <label for="signupEmail">Email</label>
                    <input type="email" id="signupEmail" name="email" placeholder="your@email.com" required>
                </div>
                <div class="form-group">
                    <label for="signupPassword">Password</label>
                    <input type="password" id="signupPassword" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit">Sign Up</button>
            </form>
            <p>Already have an account? <a data-page="signin" class="switch-page">Sign In</a></p>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>

<div class="overlay" id="detailsOverlay">
    <div class="overlay-content" id="overlayContent">
        <button class="close-overlay" id="closeOverlayBtn"><i class="fas fa-times"></i></button>
    </div>
</div>

<script src="API_Ops.js"></script>
<script>
(function() {
    const pageSections = document.querySelectorAll('.page-section');
    const navLinks = document.querySelectorAll('.nav-link');
    const switchLinks = document.querySelectorAll('.switch-page');
    const loggedIn = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;

    function navigateTo(pageId) {
        if (pageId === 'watchlist' && !loggedIn) {
            alert('Please sign in to view your watchlist.');
            navigateTo('signin');
            return;
        }
        pageSections.forEach(sec => sec.classList.remove('active'));
        const target = document.getElementById(pageId + '-page');
        if (target) target.classList.add('active');

        navLinks.forEach(link => {
            const linkPage = link.getAttribute('data-page');
            link.classList.toggle('active', linkPage === pageId);
        });

        if (pageId === 'watchlist' || pageId === 'home') {
            if (typeof getMoviesFromDB === 'function') getMoviesFromDB();
        }
    }

    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const page = link.getAttribute('data-page');
            if (page) navigateTo(page);
        });
    });
    switchLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const page = link.getAttribute('data-page');
            if (page) navigateTo(page);
        });
    });

    async function handleAuth(formId, actionType) {
        const form = document.getElementById(formId);
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            try {
                const res = await fetch('auth.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await res.json();
                if (result.success) {
                    alert(result.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (err) {
                alert('Network error');
            }
        });
    }

    handleAuth('signinForm', 'login');
    handleAuth('signupForm', 'signup');

    const logoutLink = document.getElementById('logoutLink');
    if (logoutLink) {
        logoutLink.addEventListener('click', async (e) => {
            e.preventDefault();
            await fetch('auth.php', {
                method: 'POST',
                body: new URLSearchParams({ auth_action: 'logout' })
            });
            window.location.reload();
        });
    }
})();
</script>
</body>
</html>