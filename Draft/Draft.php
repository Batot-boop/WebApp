<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MovieWatchlist</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* ---------- Global styles (as before) ---------- */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: system-ui, 'Segoe UI', Roboto, sans-serif;
      line-height: 1.5;
      background: #f5f7fc;
      color: #1e1e2f;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }
    main {
      flex: 1;
      padding: 2rem 1.5rem;
      max-width: 1300px;
      margin: 0 auto;
      width: 100%;
    }
    :root {
      --primary-dark: #0b0e14;
      --secondary-dark: #161b26;
      --accent: #7c83fd;
      --accent-light: #a5abff;
      --text-light: #eef2fb;
      --text-muted: #b0b8cc;
      --footer-bg: #0f131c;
      --card-bg: #ffffff;
      --transition: all 0.25s ease;
    }
    /* Header */
    .header {
      background: var(--primary-dark);
      color: var(--text-light);
      padding: 0.75rem 2rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      box-shadow: 0 6px 14px rgba(0,0,0,0.25);
      border-bottom: 1px solid rgba(124,131,253,0.2);
    }
    .logo {
      font-size: 1.8rem;
      font-weight: 700;
      background: linear-gradient(135deg, #fff, #cfd7ff);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }
    .logo i { margin-right: 6px; color: var(--accent-light); }
    .menu-toggle { display: none; }
    .hamburger {
      font-size: 2rem;
      cursor: pointer;
      color: var(--text-light);
      transition: var(--transition);
      padding: 0 6px;
      display: block;
    }
    .hamburger:hover { color: var(--accent-light); transform: scale(1.05); }
    .nav {
      width: 100%;
      display: none;
      background: var(--secondary-dark);
      margin-top: 0.75rem;
      border-radius: 20px;
      padding: 1rem 1.5rem;
      box-shadow: 0 12px 20px -8px rgba(0,0,0,0.4);
      border: 1px solid #2e3545;
    }
    .menu-toggle:checked ~ .nav { display: block; }
    .nav-list {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 1.2rem;
    }
    .nav-list a {
      text-decoration: none;
      color: var(--text-light);
      font-weight: 500;
      font-size: 1.2rem;
      padding: 0.4rem 0.2rem;
      transition: var(--transition);
      display: inline-block;
      border-bottom: 2px solid transparent;
    }
    .nav-list a:hover {
      color: var(--accent-light);
      border-bottom-color: var(--accent);
      transform: translateX(6px);
    }
    @media screen and (min-width: 768px) {
      .header { padding: 0.75rem 3rem; }
      .hamburger { display: none; }
      .nav {
        display: flex;
        width: auto;
        background: transparent;
        margin-top: 0;
        padding: 0;
        box-shadow: none;
        border: none;
      }
      .nav-list { flex-direction: row; gap: 2.2rem; align-items: center; }
      .nav-list a { font-size: 1.05rem; padding: 0.5rem 0; }
      .nav-list a:hover { transform: translateY(-2px); }
      .logo { margin-right: auto; }
    }
    /* Search form */
    .search-section {
      background: var(--card-bg);
      border-radius: 28px;
      padding: 2rem 2rem 1.8rem;
      margin-bottom: 2.5rem;
      box-shadow: 0 12px 30px -8px rgba(0,0,0,0.08);
      border: 1px solid #eef2fb;
    }
    .search-form {
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
      align-items: flex-end;
      margin-bottom: 24px;
    }
    .input-group { flex: 2 1 240px; }
    .input-group label {
      display: block;
      font-weight: 600;
      margin-bottom: 6px;
      color: #2a2a40;
      font-size: 0.9rem;
    }
    .input-group input,
    .input-group select {
      width: 100%;
      padding: 0.9rem 1.2rem;
      border: 2px solid #e0e7f0;
      border-radius: 60px;
      font-size: 1rem;
      background: white;
      transition: var(--transition);
    }
    .input-group input:focus,
    .input-group select:focus {
      border-color: var(--accent);
      outline: none;
      box-shadow: 0 0 0 4px rgba(124,131,253,0.15);
    }
    .search-form button {
      background: var(--primary-dark);
      color: white;
      border: none;
      padding: 0.9rem 2.2rem;
      border-radius: 60px;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      transition: var(--transition);
      display: inline-flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 6px 12px rgba(11,14,20,0.2);
      border: 1px solid #2f3a50;
      height: fit-content;
    }
    .search-form button:hover {
      background: var(--accent);
      color: #0b0e14;
      transform: scale(1.02);
    }
    /* Movie grid */
    .movies-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 1.8rem;
      margin-top: 1.5rem;
    }
    @media (min-width: 560px) { .movies-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (min-width: 880px) { .movies-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (min-width: 1100px) { .movies-grid { grid-template-columns: repeat(4, 1fr); } }
    .movie-card {
      background: white;
      border-radius: 24px;
      padding: 1.2rem;
      box-shadow: 0 10px 20px -6px rgba(0,0,0,0.05);
      border: 1px solid #edeff2;
      transition: var(--transition);
    }
    .movie-card:hover { transform: translateY(-6px); box-shadow: 0 24px 30px -10px rgba(0,0,0,0.12); }
    .movie-card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 16px;
      margin-bottom: 0.8rem;
      background: #ddd;
    }
    .movie-card h3 { font-size: 1.2rem; margin-bottom: 0.5rem; }
    .movie-card button {
      background: var(--accent);
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 30px;
      cursor: pointer;
      font-weight: 500;
      transition: var(--transition);
      margin-top: 0.8rem;
    }
    .movie-card button:hover { background: var(--primary-dark); color: white; }
    /* Overlay */
    .overlay {
      position: fixed;
      top: 0; left: 0; width: 100%; height: 100%;
      background-color: rgba(0,0,0,0.7);
      backdrop-filter: blur(5px);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1000;
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.3s, visibility 0.3s;
      padding: 20px;
    }
    .overlay.active { opacity: 1; visibility: visible; }
    .overlay-content {
      background: white;
      border-radius: 36px;
      max-width: 700px;
      width: 100%;
      max-height: 85vh;
      overflow-y: auto;
      padding: 2rem;
      box-shadow: 0 30px 50px rgba(0,0,0,0.3);
      position: relative;
    }
    .close-overlay {
      position: absolute;
      top: 1.2rem; right: 1.5rem;
      background: none;
      border: none;
      font-size: 2rem;
      cursor: pointer;
      color: #7b7f8e;
      transition: var(--transition);
    }
    .close-overlay:hover { color: var(--primary-dark); transform: scale(1.1); }
    .movie-details-full img {
      max-width: 180px;
      border-radius: 20px;
      margin-right: 1.8rem;
      float: left;
    }
    .details-text { overflow: hidden; }
    .details-text h2 { margin-bottom: 0.5rem; font-size: 2rem; }
    .details-text a {
      display: inline-block;
      margin-top: 1rem;
      background: var(--accent);
      color: white;
      padding: 0.6rem 1.5rem;
      border-radius: 40px;
      text-decoration: none;
      font-weight: 600;
    }
    /* DB Panel */
    .db-panel {
      background: #1e2432;
      border-radius: 32px;
      padding: 2rem;
      margin-top: 3rem;
      color: #eef2fb;
    }
    .db-panel h3 { color: white; margin-bottom: 1.5rem; }
    .add-form {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 2rem;
    }
    .add-form input {
      padding: 0.8rem 1.2rem;
      border-radius: 40px;
      border: none;
      flex: 1 1 200px;
    }
    .add-form button {
      background: var(--accent);
      border: none;
      padding: 0.8rem 2rem;
      border-radius: 40px;
      font-weight: bold;
      cursor: pointer;
    }
    /* Footer */
    .footer {
      background: var(--footer-bg);
      color: var(--text-muted);
      padding: 3rem 2rem 1.2rem;
      margin-top: 2rem;
      border-top: 1px solid #2a3142;
    }
    .footer-container { max-width: 1300px; margin: 0 auto; }
    .footer-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 2.5rem;
      margin-bottom: 2.5rem;
    }
    .footer-col h4 {
      color: white;
      font-size: 1.25rem;
      margin-bottom: 1.4rem;
      border-left: 4px solid var(--accent);
      padding-left: 1rem;
    }
    .footer-col ul { list-style: none; }
    .footer-col li { margin-bottom: 0.8rem; }
    .footer-col a {
      color: var(--text-muted);
      text-decoration: none;
      transition: var(--transition);
    }
    .footer-col a:hover { color: white; transform: translateX(5px); }
    .social-links { display: flex; gap: 1.2rem; margin-top: 0.8rem; }
    .social-links a {
      background: #232a38;
      width: 42px; height: 42px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--text-light);
      font-size: 1.4rem;
      transition: var(--transition);
    }
    .social-links a:hover { background: var(--accent); color: #0b0e14; transform: translateY(-5px); }
    .footer-bottom {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: space-between;
      padding-top: 1.8rem;
      border-top: 1px solid #2f374b;
      font-size: 0.9rem;
      gap: 1rem;
    }
    .copyright { color: #9aa1b3; }
    .footer-extra-links { display: flex; gap: 1.8rem; }
    .footer-extra-links a { color: #b9c2d9; text-decoration: none; }
    .footer-extra-links a:hover { color: white; }
    @media screen and (min-width: 600px) { .footer-grid { grid-template-columns: repeat(2, 1fr); } }
    @media screen and (min-width: 900px) { .footer-grid { grid-template-columns: repeat(4, 1fr); } .footer-bottom { flex-direction: row; } }
    .section-title { margin: 1.5rem 0 0.8rem; font-weight: 600; }
  </style>
</head>

<body>
    <!-- ===== HEADER ===== -->
     <?php include 'header.php'; ?>

  <!-- ===== MAIN ===== -->
  <main>
    <section class="search-section">
      <!-- Search form: Enter title + Select type -->
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

      <!-- Film display container (shared between trending and search results) -->
      <h3 class="section-title"><i class="fas fa-clapperboard"></i> <span id="movieSectionTitle">Popular Movies</span></h3>
      <div id="movieContainer" class="movies-grid">
        <p>Loading popular movies...</p>
      </div>
    </section>

    <!-- Database Management Panel (retained) -->
    <section class="db-panel">
      <h3><i class="fas fa-database"></i> My Movie Database</h3>
      <div class="add-form">
        <input type="text" id="newTitle" placeholder="Movie title">
        <input type="text" id="newImage" placeholder="Image URL">
        <button type="button" id="addToDbBtn"><i class="fas fa-plus"></i> Add to DB</button>
      </div>
      <div id="moviesDB" class="movies-grid"></div>
    </section>
  </main>

    <?php include 'footer.php'; ?>

  <!-- Details mask layer -->
  <div class="overlay" id="detailsOverlay">
    <div class="overlay-content" id="overlayContent">
      <button class="close-overlay" id="closeOverlayBtn"><i class="fas fa-times"></i></button>
      <!-- Dynamic content will be inserted here -->
    </div>
  </div>
    <button id="test" >Test button</button>
    <div id="sub" style="display:none">
        <h2>أهلاً بك في الصفحة الفرعية</h2>
        <p>هذا الكود كان مخفياً.</p>
    </div>
  <!-- Loads only external JavaScript files; does not include any inline scripts -->
  <!-- <script src="API_Ops.js"></script> -->
</body>

</html>
