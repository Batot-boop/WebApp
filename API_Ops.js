// API_Ops.js

/* ---------- Overlay ---------- */
function showOverlayDetails(movieData) {
    if (!movieData.trailer) movieData.trailer = "No trailer available";
    const html = `
        <div class="movie-details-full">
            <img src="${movieData.image}" alt="${movieData.title}">
            <div class="details-text">
                <h2>${movieData.title}</h2>
                ${movieData.releaseDate ? `<p><strong>Release:</strong> ${movieData.releaseDate}</p>` : ''}
                ${movieData.genres && movieData.genres.length ? `<p><strong>Genres:</strong> ${movieData.genres.join(', ')}</p>` : ''}
                ${movieData.description ? `<p><strong>Description:</strong> ${movieData.description}</p>` : ''}
                ${movieData.trailer.startsWith('http')
                    ? `<a href="${movieData.trailer}" target="_blank"><i class="fas fa-play"></i> Watch Trailer</a>`
                    : `<span style="color:gray;">No Trailer Available</span>`}
            </div>
            <div style="clear:both;"></div>
        </div>`;
    const overlay = document.getElementById('detailsOverlay');
    const content = document.getElementById('overlayContent');
    const closeBtn = content.querySelector('.close-overlay');
    content.innerHTML = '';
    content.appendChild(closeBtn);
    content.insertAdjacentHTML('beforeend', html);
    overlay.classList.add('active');
}

function closeOverlay() {
    document.getElementById('detailsOverlay').classList.remove('active');
}

document.addEventListener('DOMContentLoaded', function() {
    const closeBtn = document.getElementById('closeOverlayBtn');
    const overlay = document.getElementById('detailsOverlay');
    if (closeBtn) closeBtn.addEventListener('click', closeOverlay);
    if (overlay) overlay.addEventListener('click', (e) => { if (e.target === overlay) closeOverlay(); });
});

/* ---------- Fetch helper ---------- */
function fetchModified(action, params = {}, targetId = "movieContainer") {
    const url = `API_Ops.php?action=${action}&${new URLSearchParams(params).toString()}`;
    fetch(url)
    .then(response => {
        if (!response.ok) throw new Error("Network response not ok");
        return response.json();
    })
    .then(data => {
        let htmlContent = "";
        const container = document.getElementById(targetId);

        if (Array.isArray(data)) {
            data.forEach(movie => {
                const cardId = `card-${movie.id}`;
                htmlContent += `
                    <div class="movie-card" id="${cardId}">
                        <img src="${movie.image}" alt="${movie.title}" onerror="handleBrokenImage('${cardId}')">
                        <h3>${movie.title}</h3>
                        <button onclick="getMovieDetails('${movie.id}')">View Details</button>
                        <button onclick="addToWatchlist('${movie.id}')">+ Add to Watchlist</button>
                    </div>`;
            });
        } else if (data && !data.error) {
            showOverlayDetails(data);
            return;
        } else {
            htmlContent = `<p style="color:red;">${data.error || "No data found"}</p>`;
        }

        if (container) container.innerHTML = htmlContent;
    })
    .catch(error => {
        console.error("Error:", error);
        const container = document.getElementById(targetId);
        if (container) container.innerHTML = "<p>Error fetching data!</p>";
    });
}

function handleBrokenImage(cardId) {
    const card = document.getElementById(cardId);
    if (card) card.remove();
}

/* ---------- API calls ---------- */
function getMoviesAPI() {
    const title = document.getElementById('primaryTitle').value.trim();
    const genre = document.getElementById('genreSelect').value;
    if (!title && !genre) {
        alert("Please enter a title or select a genre.");
        return;
    }
    document.getElementById('movieSectionTitle').textContent = 'Search Results';
    const container = document.getElementById('movieContainer');
    if (container) container.innerHTML = '<p>Searching...</p>';
    const params = {};
    if (title) params.primaryTitle = title;
    if (genre) params.genre = genre;
    fetchModified("search", params, "movieContainer");
}

function getPopularMovies() {
    document.getElementById('movieSectionTitle').textContent = 'Popular Movies';
    fetchModified("popular", {}, "movieContainer");
}

function getMovieDetails(id) {
    fetchModified("details", { id: id }, "movieDetails");
}

/* ---------- Watchlist / DB operations (title + image only) ---------- */
function getMoviesFromDB() {
    fetch("DB_Ops.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: "read" })
    })
    .then(res => res.json())
    .then(response => {
        const data = response.data || [];
        let html = "";
        data.forEach(movie => {
            html += `
                <div class="movie-card">
                    <img src="${movie.image}" alt="${escapeHtml(movie.title)}">
                    <h3>${escapeHtml(movie.title)}</h3>
                    <button onclick="deleteMovie(${movie.ID})">Delete</button>
                    <button onclick="editMovie(${movie.ID}, '${escapeHtml(movie.title)}', '${escapeHtml(movie.image)}')">Edit</button>
                </div>`;
        });
        document.getElementById("moviesDB").innerHTML = html || "<p>No movies in your watchlist.</p>";
        // Also update watchlist page if visible
        const watchlistContainer = document.getElementById("watchlistMovies");
        if (watchlistContainer && document.getElementById('watchlist-page').classList.contains('active')) {
            watchlistContainer.innerHTML = html || "<p>Your watchlist is empty.</p>";
        }
    })
    .catch(err => console.error("DB read error:", err));
}

function deleteMovie(id) {
    fetch("DB_Ops.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: "delete", id: id })
    })
    .then(() => getMoviesFromDB());
}

// Updated edit: prompts for title and image URL
function editMovie(id, currentTitle, currentImage) {
    const newTitle = prompt("Edit title:", currentTitle);
    if (newTitle === null) return; // cancelled
    const newImage = prompt("Edit image URL:", currentImage);
    if (newImage === null) return;

    fetch("DB_Ops.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: "update",
            id: id,
            title: newTitle,
            image: newImage
        })
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            getMoviesFromDB();
        } else {
            alert("Update failed: " + result.message);
        }
    });
}

// Add movie from API: fetches details, then sends title+image to DB
function addToWatchlist(movieId) {
    fetch(`API_Ops.php?action=retrieveToDB&id=${encodeURIComponent(movieId)}`)
    .then(res => res.json())
    .then(movie => {
        if (movie.error) {
            alert("Failed to fetch movie details: " + movie.error);
            return;
        }
        return fetch("DB_Ops.php", {
            method: "POST",
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: "add",
                title: movie.title,
                image: movie.image
            })
        });
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            alert("Movie added to your watchlist!");
            getMoviesFromDB();
        } else {
            alert("Could not add: " + result.message);
        }
    })
    .catch(err => {
        console.error("Add error:", err);
        alert("Error adding movie.");
    });
}

// Escape helper for safe HTML insertion
function escapeHtml(text) {
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    getPopularMovies();
    getMoviesFromDB();

    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            getMoviesAPI();
        });
    }
});