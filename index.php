<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Movie Tracker</title>
    <link rel="stylesheet" href="StyleSheet.css">
</head>
<body>

    <h1>🎬 Movie Tracker</h1>

    <!-- Search Section -->
    <div class="search-box">
        <input type="text" id="title" placeholder="Enter movie title">
        <button onclick="searchMovie()">Search</button>
    </div>

    <!-- Results -->
    <div id="searchResults" class="movies-container"></div>

    <!-- Movie Details -->
    <div id="movieDetails"></div>

    <script src="API_Ops.js"></script>

    <script>
        function searchMovie()
        {
            const title = document.getElementById("title").value;

            getMoviesAPI({
                title: title
            });
        }
    </script>

</body>
</html>
