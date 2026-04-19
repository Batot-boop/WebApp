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
                htmlContent += `
                    <div class="movie-card">
                        <img src="${movie.image}" alt="${movie.title}">
                        <h3>${movie.title}</h3>
                        <button onclick="getMovieDetails('${movie.id}')">View Full Details</button>
                    </div>`;
            });
        } 
        else 
        {
            htmlContent = `
                <div class="movie-details-full">
                    <h2>${data.title}</h2>
                    <img src="${data.image}" style="width:200px">
                    <p><strong>Release:</strong> ${data.releaseDate}</p>
                    <p><strong>Genres:</strong> ${data.genres.join(', ')}</p>
                    <a href="${data.trailer}" target="_blank">Watch Trailer</a>
                    <button onclick="document.getElementById('${tag}').innerHTML=''">Close</button>
                </div>`;
        }
        
        document.getElementById(tag).innerHTML = htmlContent;
    })
    .catch(error => 
    {
        console.error("Error:", error);
        document.getElementById(tag).innerHTML = "<p>Error fetching data!</p>";
    });
}

function getMoviesAPI(filters = {}) 
{   // Check if at least one filter is provided
    const hasData = Object.values(filters).some(value => value.trim() !== "");
    if(!hasData)
    {
        alert("Please enter at least one search criteria!");
        return;
    }
    fetchModified("search", filters, "searchResults");
}

function getMovieDetails(id)
{
    fetchModified("details", {id : id}, "movieDetails");
}