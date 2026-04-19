
function addMovieFromAPI(title, image)
{
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
    .then(() => getMoviesFromDB());
}

// READ
function getMoviesFromDB()
{
    fetch("DB_Ops.php", {
        method: "POST",
        body: new URLSearchParams({ action: "read" })
    })
    .then(res => res.json())
    .then(data => {
        let html = "";

        data.forEach(movie => {
            html += `
                <div>
                    <h3>${movie.title}</h3>
                    <button onclick="deleteMovie(${movie.id})">Delete</button>
                </div>`;
        });

        document.getElementById("moviesDB").innerHTML = html;
    });
}

// DELETE
function deleteMovie(id)
{
    fetch("DB_Ops.php", {
        method: "POST",
        body: new URLSearchParams({ action: "delete", id })
    })
    .then(() => getMoviesFromDB());
}

// UPDATE
function updateMovie(id, title)
{
    fetch("DB_Ops.php", {
        method: "POST",
        body: new URLSearchParams({
            action: "update",
            id,
            title
        })
    })
    .then(() => getMoviesFromDB());
}