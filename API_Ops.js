// ---------- Overlay Controller ----------
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
                    : `<span style="color: gray;">No Trailer Available</span>`}
            </div>
            <div style="clear:both;"></div>
        </div>
    `;
    const overlay = document.getElementById('detailsOverlay');
    const content = document.getElementById('overlayContent');
    // Keep the close button and insert the details after it
    const closeBtn = content.querySelector('.close-overlay');
    content.innerHTML = ''; 
    content.appendChild(closeBtn);
    content.insertAdjacentHTML('beforeend', html);
    overlay.classList.add('active');
}

function closeOverlay() {
    document.getElementById('detailsOverlay').classList.remove('active');
}

// ---------- Retrieve Movies From API ----------
// Bind the close event (only needs to be executed once after the DOM has loaded)
document.addEventListener('DOMContentLoaded', function() {
    const closeBtn = document.getElementById('closeOverlayBtn');
    const overlay = document.getElementById('detailsOverlay');
    if (closeBtn) closeBtn.addEventListener('click', closeOverlay);
    if (overlay) overlay.addEventListener('click', (e) => { if (e.target === overlay) closeOverlay(); });
});

function fetchModified(action, params = {}, tag = "")
{
    fetch(`API_Ops.php?action=${action}&${new URLSearchParams(params).toString()}`)
    .then(response => 
    {
        if(!response.ok) throw new Error("Network response was not ok");
        return response.json();   
    })
    .then(data => {
        console.log("Data received: ", data);
        let htmlContent = "";

        if (Array.isArray(data)) 
        {
            data.forEach(movie => {
                const cardId = `card-${movie.id}`;
                htmlContent += `
                    <div class="movie-card" id="${cardId}">
                        <img src="${movie.image}" 
                        alt="${movie.title}" 
                        onerror="handleBrokenImage('${cardId}')"><h3>${movie.title}</h3>
                        <button onclick="getMovieDetails('${movie.id}')">View Full Details</button>
                    </div>`;
            });
        } 
        else if (data && !data.error) 
        {
            if(!data.trailer) data.trailer = "No trailer available"; // Ensure trailer is defined to avoid undefined errors
            showOverlayDetails(data);
            return; // Exit early since we're using the overlay to display details
        } 
        else htmlContent = `<p style="color:red;">${data.error || "Data not found"}</p>`;

        document.getElementById(tag).innerHTML = htmlContent;
    })
    .catch(error => 
    {
        console.error("Error:", error);
        document.getElementById(tag).innerHTML = "<p>Error fetching data!</p>";
    });
}

function handleBrokenImage(cardId)
{
    const cardElement = document.getElementById(cardId);
    if (cardElement) 
    {
        cardElement.remove();
        console.warn(`Removed film card ${cardId} due to broken image URL.`);
    }
}

function getMoviesAPI(filters = {}) 
{
    // Retrieve from the page input (as the call may be triggered externally, we are reading it proactively here)
    const titleInput = document.getElementById('primaryTitle');
    const genreSelect = document.getElementById('genreSelect');
    const title = titleInput ? titleInput.value.trim() : '';
    const genre = genreSelect ? genreSelect.value : '';
    
    // Construct the parameters actually sent
    const params = {};
    if (title) params.primaryTitle = title;   // Use `primaryTitle` as the key name
    if (genre) params.genre = genre;
    
    const hasData = Object.values(params).some(value => value && value.trim() !== "");
    if(!hasData) {
        alert("Please enter a title or select a genre.");
        return;
    }
    
    // Update the title to "Search Results"
    const titleSpan = document.getElementById('movieSectionTitle');
    if (titleSpan) titleSpan.textContent = 'Search Results';
    
    // Clear the container and display the loading status
    const container = document.getElementById('movieContainer');
    if (container) container.innerHTML = '<p>Searching...</p>';
    
    // When `fetchModified` is called, the result will be rendered to `movieContainer`
    fetchModified("search", params, "movieContainer");
}

function getMovieDetails(id)
{
    fetchModified("details", {id : id}, "movieDetails");
}

function getPopularMovies()
{
    const titleSpan = document.getElementById('movieSectionTitle');
    if (titleSpan) titleSpan.textContent = 'Popular Movies';
    fetchModified("popular", {}, "movieContainer");
}

document.addEventListener('DOMContentLoaded', function() {
    getPopularMovies();
    getMoviesFromDB();   // Loading database list
    
    // Bind search form
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            getMoviesAPI();   // The function reads the form values internally
        });
    }
    
    // Bind database add button
    const addBtn = document.getElementById('addToDbBtn');
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            const title = document.getElementById('newTitle').value.trim();
            const image = document.getElementById('newImage').value.trim();
            if (!title || !image) {
                alert("Please enter both title and image URL.");
                return;
            }
            addMovieFromAPI(title, image);
        });
    }
});

// ---------- Database Operations ----------
function addMovieFromAPI(tag = "", id) {
    fetch(`API_Ops.php?action=retrieveToDB&id=${encodeURIComponent(id)}`)
    .then(res => res.json())
    .then(data => {
            alert("Saved!");
            getMoviesFromDB();
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Failed to save movie!");
    });
}

function getMoviesFromDB() {
    fetch("DB_Ops.php", {
        method: "POST",
        body: new URLSearchParams({ action: "read" })
    })
        .then(res => res.json())
        .then(data => {
            let html = "";

            data.forEach(movie => {
                html += `
                <div class="movie-card">
                    <img src="${movie.image}" width="120">
                    <h3>${movie.title}</h3>
                    <p> ${movie.rating}</p>

                    <button onclick="deleteMovie(${movie.id})">Delete</button>
                    <button onclick="editMovie(${movie.id}, '${movie.title}', ${movie.rating})">Edit</button>
                </div>`;
            });

            document.getElementById("moviesDB").innerHTML = html;
        });
}

function deleteMovie(id) {
    fetch("DB_Ops.php", {
        method: "POST",
        body: new URLSearchParams({ action: "delete", id })
    })
        .then(() => getMoviesFromDB());
}

function updateMovie(id, title, rating) {
    fetch("DB_Ops.php", {
        method: "POST",
        body: new URLSearchParams({
            action: "update",
            id,
            title,
            rating
        })
    })
        .then(() => getMoviesFromDB());
}

function editMovie(id, title, rating) {
    let newTitle = prompt("Edit title:", title);
    let newRating = prompt("Edit rating:", rating);

    if (newTitle && newRating)
        updateMovie(id, newTitle, newRating);
}