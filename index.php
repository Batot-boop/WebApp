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
  <?php include 'header.php';?>

  <!-- ===== MAIN CONTENT ===== -->
  <main>
    <!-- HOME PAGE (default visible) -->
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

      <section class="db-panel">
        <h3><i class="fas fa-database"></i> My Movie Database</h3>
        <div class="add-form">
          <input type="text" id="newTitle" placeholder="Movie title">
          <input type="text" id="newImage" placeholder="Image URL (optional)">
          <button type="button" id="addToDbBtn"><i class="fas fa-plus"></i> Add to DB</button>
        </div>
        <div id="moviesDB" class="movies-grid"></div>
      </section>
    </div>

    <!-- WATCHLIST PAGE (hidden by default) -->
    <div id="watchlist-page" class="page-section">
      <div class="watchlist-container">
        <h2><i class="fas fa-bookmark"></i> My Watchlist</h2>
        <div id="watchlistMovies" class="movies-grid">
          <p>Your watchlist is empty. Add movies from the Home page!</p>
        </div>
      </div>
    </div>

    <!-- SIGN IN PAGE (hidden) -->
    <div id="signin-page" class="page-section">
      <div class="auth-form">
        <h2><i class="fas fa-sign-in-alt"></i> Sign In</h2>
        <form id="signinForm">
          <div class="form-group">
            <label for="signinEmail">Email</label>
            <input type="email" id="signinEmail" placeholder="your@email.com" required>
          </div>
          <div class="form-group">
            <label for="signinPassword">Password</label>
            <input type="password" id="signinPassword" placeholder="••••••••" required>
          </div>
          <button type="submit">Sign In</button>
        </form>
        <p>Don't have an account? <a data-page="signup" class="switch-page">Sign Up</a></p>
      </div>
    </div>

    <!-- SIGN UP PAGE (hidden) -->
    <div id="signup-page" class="page-section">
      <div class="auth-form">
        <h2><i class="fas fa-user-plus"></i> Create Account</h2>
        <form id="signupForm">
          <div class="form-group">
            <label for="signupName">Full Name</label>
            <input type="text" id="signupName" placeholder="John Doe" required>
          </div>
          <div class="form-group">
            <label for="signupEmail">Email</label>
            <input type="email" id="signupEmail" placeholder="your@email.com" required>
          </div>
          <div class="form-group">
            <label for="signupPassword">Password</label>
            <input type="password" id="signupPassword" placeholder="••••••••" required>
          </div>
          <button type="submit">Sign Up</button>
        </form>
        <p>Already have an account? <a data-page="signin" class="switch-page">Sign In</a></p>
      </div>
    </div>
  </main>

  <?php include 'footer.php'; ?>

  <!-- ===== OVERLAY ===== -->
  <div class="overlay" id="detailsOverlay">
    <div class="overlay-content" id="overlayContent">
      <button class="close-overlay" id="closeOverlayBtn"><i class="fas fa-times"></i></button>
    </div>
  </div>

  <script src="API_Ops.js"></script>

  <!-- ========== JAVASCRIPT ========== -->
  <script>
    (function() {
      // ---------- LOGIN STATE ----------
      // Simulate a logged‑in user flag (persist to localStorage)
      let loggedIn = false;
      try {
        const stored = localStorage.getItem('movieTrackerLoggedIn');
        loggedIn = (stored === 'true');
      } catch(e) {}

      // Update UI according to login state
      function updateLoginUI() {
        const watchlistLink = document.querySelector('.nav-link[data-page="watchlist"]');
        if (watchlistLink) {
          watchlistLink.style.display = loggedIn ? '' : 'none';
        }
        // If not logged in and user tries to access watchlist page directly, redirect to signin
        if (!loggedIn && document.getElementById('watchlist-page').classList.contains('active')) {
          navigateTo('signin');
        }
      }

      // Expose login functions globally if needed
      window.login = function() {
        loggedIn = true;
        localStorage.setItem('movieTrackerLoggedIn', 'true');
        updateLoginUI();
      };
      window.logout = function() {
        loggedIn = false;
        localStorage.setItem('movieTrackerLoggedIn', 'false');
        updateLoginUI();
        // If currently on watchlist, go to home
        if (document.getElementById('watchlist-page').classList.contains('active')) {
          navigateTo('home');
        }
      };

      // ---------- SPA NAVIGATION ----------
      const pageSections = document.querySelectorAll('.page-section');
      const navLinks = document.querySelectorAll('.nav-link');
      const switchLinks = document.querySelectorAll('.switch-page');

      function navigateTo(pageId) {
        // Check authorization for watchlist
        if (pageId === 'watchlist' && !loggedIn) {
          alert('Please sign in to view your watchlist.');
          navigateTo('signin');
          return;
        }

        // Hide all sections
        pageSections.forEach(sec => sec.classList.remove('active'));
        // Show the target section
        const target = document.getElementById(pageId + '-page');
        if (target) target.classList.add('active');

        // Update active class on nav links
        navLinks.forEach(link => {
          const linkPage = link.getAttribute('data-page');
          if (linkPage === pageId) {
            link.classList.add('active');
          } else {
            link.classList.remove('active');
          }
        });

        // If switching to watchlist, refresh its content
        if (pageId === 'watchlist') {
          renderWatchlist();
        }
      }

      // Attach click event to all navigation links
      function attachNavHandlers() {
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
      }
      attachNavHandlers();

      // ---------- MOVIE DATABASE (shared between home DB panel & watchlist) ----------
      let movieDB = [];
      try {
        const stored = localStorage.getItem('movieTrackerDB');
        if (stored) movieDB = JSON.parse(stored);
      } catch(e) {}

      function saveDB() {
        localStorage.setItem('movieTrackerDB', JSON.stringify(movieDB));
      }

      function renderDB() {
        const container = document.getElementById('moviesDB');
        if (!container) return;
        if (movieDB.length === 0) {
          container.innerHTML = '<p style="color:#b0b8cc;">No movies added yet. Use the form above.</p>';
          return;
        }
        container.innerHTML = movieDB.map((movie, index) => `
          <div class="movie-card">
            <img src="${movie.image || 'https://via.placeholder.com/300x200?text=No+Image'}" alt="${movie.title}">
            <h3>${movie.title}</h3>
            <button data-index="${index}" class="remove-db-btn"><i class="fas fa-trash"></i> Remove</button>
          </div>
        `).join('');

        document.querySelectorAll('.remove-db-btn').forEach(btn => {
          btn.addEventListener('click', (e) => {
            const idx = parseInt(btn.getAttribute('data-index'), 10);
            movieDB.splice(idx, 1);
            saveDB();
            renderDB();
            if (document.getElementById('watchlist-page').classList.contains('active')) {
              renderWatchlist();
            }
          });
        });
      }

      function renderWatchlist() {
        const container = document.getElementById('watchlistMovies');
        if (!container) return;
        if (movieDB.length === 0) {
          container.innerHTML = '<p>Your watchlist is empty. Add movies from the Home page!</p>';
          return;
        }
        container.innerHTML = movieDB.map((movie, index) => `
          <div class="movie-card">
            <img src="${movie.image || 'https://via.placeholder.com/300x200?text=No+Image'}" alt="${movie.title}">
            <h3>${movie.title}</h3>
            <button data-index="${index}" class="remove-watchlist-btn"><i class="fas fa-trash"></i> Remove</button>
          </div>
        `).join('');

        document.querySelectorAll('.remove-watchlist-btn').forEach(btn => {
          btn.addEventListener('click', (e) => {
            const idx = parseInt(btn.getAttribute('data-index'), 10);
            movieDB.splice(idx, 1);
            saveDB();
            renderWatchlist();
            renderDB();
          });
        });
      }

      // Initial render
      renderDB();
      updateLoginUI(); // Ensure watchlist link visibility is correct on load

      // Add movie from the form
      const addBtn = document.getElementById('addToDbBtn');
      const titleInput = document.getElementById('newTitle');
      const imageInput = document.getElementById('newImage');
      if (addBtn) {
        addBtn.addEventListener('click', () => {
          const title = titleInput.value.trim();
          if (!title) {
            alert('Please enter a movie title.');
            return;
          }
          const image = imageInput.value.trim() || '';
          movieDB.push({ title, image });
          saveDB();
          renderDB();
          if (document.getElementById('watchlist-page').classList.contains('active')) {
            renderWatchlist();
          }
          titleInput.value = '';
          imageInput.value = '';
        });
      }

      // ---------- AUTH FORM HANDLERS (demo – now sets login state) ----------
      // Sign In
      const signinForm = document.getElementById('signinForm');
      if (signinForm) {
        signinForm.addEventListener('submit', (e) => {
          e.preventDefault();
          const email = document.getElementById('signinEmail').value;
          const password = document.getElementById('signinPassword').value;
          alert(`Sign In demo:\nEmail: ${email}\nPassword: ${password}\n(Simulated login)`);
          // Simulate successful login
          window.login();
          // Go to home page after login
          navigateTo('home');
        });
      }

      // Sign Up
      const signupForm = document.getElementById('signupForm');
      if (signupForm) {
        signupForm.addEventListener('submit', (e) => {
          e.preventDefault();
          const name = document.getElementById('signupName').value;
          const email = document.getElementById('signupEmail').value;
          const password = document.getElementById('signupPassword').value;
          alert(`Sign Up demo:\nName: ${name}\nEmail: ${email}\nPassword: ${password}\n(Simulated registration)`);
          // Automatically log the user in after sign-up
          window.login();
          // Redirect to home (or maybe to watchlist?)
          navigateTo('home');
        });
      }

      // ---------- FALLBACK POPULAR MOVIES PLACEHOLDER ----------
      const movieContainer = document.getElementById('movieContainer');
      if (movieContainer && movieContainer.innerHTML.includes('Loading popular movies')) {
        movieContainer.innerHTML = '<p style="text-align:center; color:#666;">🎬 Popular movies will appear here. Connect your API script!</p>';
      }

      // If the page loads and user is not logged in but tries to directly access watchlist via hash, we handle it.
      // For demonstration, we just call updateLoginUI again.
      window.addEventListener('load', updateLoginUI);
    })();
  </script>
</body>
</html>