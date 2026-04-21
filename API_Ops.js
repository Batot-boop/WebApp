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
            htmlContent = `
                <div class="movie-details-full">
                    <h2>${data.title}</h2>
                    <img src="${data.image}" style="width:150px">
                    <div style="margin-left: 170px;">
                        ${data.releaseDate ? `<p><strong>Release:</strong> ${data.releaseDate}</p>` : ''}
                        ${data.genres && data.genres.length > 0 ? `<p><strong>Genres:</strong> ${data.genres.join(', ')}</p>` : ''}
                        ${data.description ? `<p><strong>Description:</strong> ${data.description}</p>` : ''}
                        ${(data.trailer && data.trailer.startsWith('http')) 
    ? `<a href="${data.trailer}" target="_blank">▶ Watch Trailer</a>` 
    : `<span style="color: gray;">No Trailer Available</span>`}
                    </div>
                    <button onclick="document.getElementById('${tag}').innerHTML=''" style="clear:both; margin-top:20px;">Close</button>
                </div>`;
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
{   // Check if at least one filter is provided
    const hasData = Object.values(filters).some(value => value.trim() !== "");
    if(!hasData)
    {
        alert("Please enter at least one search criteria!");
        return;
    }
    // Clear popular movies when searching
    document.getElementById("popularMovies").innerHTML = ""; 
    fetchModified("search", filters, "searchResults");
}

function getMovieDetails(id)
{
    fetchModified("details", {id : id}, "movieDetails");
}

function getPopularMovies()
{
    fetchModified("popular", "", "popularMovies");
}

document.addEventListener('DOMContentLoaded', getPopularMovies());