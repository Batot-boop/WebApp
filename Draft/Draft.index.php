<?php include 'header.php'; ?>

<main class="page-wrapper">
    <h1 class="page-title">🎬 <span>Movie</span>Tracker</h1>
    <p class="page-subtitle">Search millions of movies and build your personal watchlist.</p>

    <!-- Search Section -->
    <?php $initQuery = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>
    <div class="search-box">
        <input type="text" id="title" placeholder="Enter a movie title…" autocomplete="off"
               value="<?php echo $initQuery; ?>">
        <button onclick="searchMovie()">Search</button>
    </div>

    <!-- Popular Movies -->
    <h2 class="section-heading" id="popular-heading">🔥 Popular Right Now</h2>
    <div id="popularMovies" class="movies-container"></div>

    <!-- Search Results (hidden until user searches) -->
    <div id="searchResultsWrap" style="display:none;">
        <h2 class="section-heading">🔍 Search Results</h2>
        <div id="searchResults" class="movies-container"></div>
    </div>

</main>

<script src="API_Ops.js"></script>
<script>
    function searchMovie() {
        const title = document.getElementById('title').value.trim();
        if (!title) { alert('Please enter a movie title!'); return; }
        document.getElementById('popular-heading').style.display = 'none';
        document.getElementById('popularMovies').style.display = 'none';
        document.getElementById('searchResultsWrap').style.display = 'block';
        getMoviesAPI({ title });
    }

    // Allow Enter key to trigger search
    document.getElementById('title').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') searchMovie();
    });

    // Auto-search if redirected from header search
    <?php if (!empty($initQuery)): ?>
    document.addEventListener('DOMContentLoaded', function() { searchMovie(); });
    <?php endif; ?>
</script>

<?php include 'footer.php'; ?>
