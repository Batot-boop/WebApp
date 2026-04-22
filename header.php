<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Movie Tracker – Search, explore, and track your favourite movies.">
    <title>🎬 Movie Tracker</title>
    <link rel="stylesheet" href="StyleSheet.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
</head>
<body>

<!-- ===== SITE HEADER / NAVBAR ===== -->
<header class="site-header">
    <div class="header-inner">

        <!-- Logo -->
        <a href="index.php" class="logo">
            <span class="logo-icon">🎬</span>
            <span class="logo-text">Movie<span class="logo-accent">Tracker</span></span>
        </a>

        <!-- Navigation -->
        <nav class="nav-links">
            <a href="index.php" class="nav-link active">Home</a>
            <a href="add_movie.php" class="nav-link active">Add Movie</a>
        </nav>

        <!-- Search bar -->
        <div class="header-search">
            <input type="text" id="header-search-input" placeholder="Search movies…" autocomplete="off">
            <button onclick="headerSearch()" aria-label="Search">&#128269;</button>
        </div>

    </div>
</header>
<!-- ============================= -->

<script>
    function headerSearch() {
        const val = document.getElementById('header-search-input').value.trim();
        if (!val) return;
        window.location.href = `index.php?q=${encodeURIComponent(val)}`;
    }
    document.addEventListener('DOMContentLoaded', function () {
        const inp = document.getElementById('header-search-input');
        if (inp) inp.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') headerSearch();
        });
    });
</script>
