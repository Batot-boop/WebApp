function fetchModified(action, params = {}, tag = "")
{
    fetch(`API_Ops.php?action=${action}&${new URLSearchParams(params).toString()}`)
    .then(respone => 
    {
        if(!respone.ok) throw new Error("Network response was not ok");
        return respone.json();   
    })
    .then(data => {
        console.log("Data received: ", data);
        let htmlContent = "";

        if (Array.isArray(data)) 
        {
            if (data.length === 0) {
                htmlContent = `<div class="state-box"><div class="state-icon">🎬</div><p>No movies found. Try a different search.</p></div>`;
            } else {
                data.forEach(movie => {
                    const cardId  = `card-${movie.id}`;
                    const year    = movie.releaseDate ? movie.releaseDate.substring(0, 4) : '';
                    const rating  = movie.rating ? parseFloat(movie.rating).toFixed(1) : '';
                    const ratingHtml = rating ? `<span class="movie-card__rating">⭐ ${rating}</span>` : '';
                    htmlContent += `
                        <div class="movie-card" id="${cardId}">
                            <div class="movie-card__poster">
                                <img src="${movie.image}"
                                     alt="${movie.title}"
                                     onerror="handleBrokenImage('${cardId}')">
                                ${ratingHtml}
                                <div class="movie-card__overlay">
                                 <button onclick="addMovieFromAPI('${movie.title.replace(/'/g, "\\'")}', '${movie.image}')">Add to Watchlist</button>
                                 <button onclick="window.location.href='movie_details.php?id=${movie.id}'">View Details</button>
                                </div>
                            </div>
                            <div class="movie-card__body">
                                <p class="movie-card__title">${movie.title}</p>
                                ${year ? `<p class="movie-card__year">${year}</p>` : ''}
                            </div>
                        </div>`;
                });
            }
        } 
        else htmlContent = `<div class="state-box error"><div class="state-icon">⚠️</div><p>${data?.error || 'Data not found'}</p></div>`;

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
{   // Check if at least one filter is provided
    const hasData = Object.values(filters).some(value => value.trim() !== "");
    if(!hasData)
    {
        alert("Please enter at least one search criteria!");
        return;
    }
    // Clear popular movies when searching
    const popEl = document.getElementById("popularMovies");
    if (popEl) popEl.innerHTML = "";
    fetchModified("search", filters, "searchResults");
}

function getMovieDetails(id)
{
    // Navigate to the dedicated details page
    window.location.href = `movie_details.php?id=${encodeURIComponent(id)}`;
}

function getPopularMovies()
{
    fetchModified("popular", "", "popularMovies");
}

document.addEventListener('DOMContentLoaded', getPopularMovies());

//for ADD
function addMovieFromAPI(title, image) {
    fetch("DB_Ops.php", {
        method: "POST",
        body: new URLSearchParams({
            action: "add",
            title,
            rating: 0,
            image
        })
    })
        .then(res => res.json())
        .then(() => {
            alert("Saved!");
            getMoviesFromDB();
        });
}

// for READ
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

// for DELETE
function deleteMovie(id) {
    fetch("DB_Ops.php", {
        method: "POST",
        body: new URLSearchParams({ action: "delete", id })
    })
        .then(() => getMoviesFromDB());
}

// for UPDATE
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

// EDIT
function editMovie(id, title, rating) {
    let newTitle = prompt("Edit title:", title);
    let newRating = prompt("Edit rating:", rating);

    if (newTitle && newRating)
        updateMovie(id, newTitle, newRating);
}



