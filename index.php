<?php
require_once 'auth.php';
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
<body class="<?php echo isLoggedIn() ? 'logged-in' : ''; ?>">
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

    <!-- UPLOAD PAGE -->
    <div id="upload-page" class="page-section">
        <div class="auth-form">
            <h2><i class="fas fa-upload"></i> Add New Movie</h2>
            <form action="upload.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="form-group">
                    <label for="movie_title">Movie Title</label>
                    <input type="text" name="movie_title" id="movie_title" placeholder="Enter movie name" required>
                </div>

                <div class="form-group">
                    <label for="movie_image">Poster Image</label>
                    <input type="file" name="movie_image" id="movie_image" accept="image/*" required style="padding: 0.7rem 1.2rem; background: white; cursor: pointer;">
                </div>

                <button type="submit">Save Movie</button>
            </form>
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
        if ((pageId === 'watchlist' || pageId === 'upload') && !loggedIn) {
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