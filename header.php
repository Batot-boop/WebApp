 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add / Edit Movie</title>
    <link rel="stylesheet" href="StyleSheet.css">
</head>
<body>

    <h1>Add Movie to Watchlist</h1>

    <div class="search-box">
        <input type="text" id="title" placeholder="Search movie...">
        <button onclick="searchMovie()">Search</button>
    </div>

    <div id="searchResults" class="movies-container"></div>

    <div id="addMovieForm" style="display:none;">
        <h3>Add Movie</h3>
        <input type="hidden" id="hidden_tmdb_id">
        <input type="text" id="form_title" readonly>
        
        <select id="status">
            <option value="want_to_watch">Want to Watch</option>
            <option value="watching">Watching</option>
            <option value="watched">Watched</option>
        </select>
        
        <button onclick="saveMovie()">Add to My List</button>
    </div>

    <script src="API_Ops.js"></script>
    <script>
        // الكود البرمجي الخاص بك لم يتغير
        function searchMovie() {
            getMoviesAPI({ title: document.getElementById("title").value });
        }

        function prepareAdd(id, title) {
            document.getElementById("addMovieForm").style.display = "block";
            document.getElementById("hidden_tmdb_id").value = id;
            document.getElementById("form_title").value = title;
        }

        function saveMovie() {
            const movieData = {
                action: 'add',
                user_id: 1,
                tmdb_id: document.getElementById("hidden_tmdb_id").value,
                title: document.getElementById("form_title").value,
                status: document.getElementById("status").value
            };

            fetch('DB_Ops.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(movieData)
            })
            .then(res => res.json())
            .then(data => alert("Movie added successfully!"))
            .catch(err => console.error("Error adding movie:", err));
        }
    </script>
</body>
</html> 