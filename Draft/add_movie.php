<?php include 'header.php'; ?>

<main class="page-wrapper">
    <h1 class="page-title">Add / Edit <span>Movie</span></h1>

    <div class="form-box">

        <!-- Hidden ID (for edit) -->
        <input type="hidden" id="movieId">

        <label>Movie Title</label>
        <input type="text" id="title" placeholder="Enter movie name">

        <label>Image URL</label>
        <input type="text" id="image" placeholder="Enter image URL">

        <label>Rating</label>
        <input type="number" id="rating" placeholder="0 - 10">

        <div style="margin-top:20px;">
            <button onclick="saveMovie()">💾 Save</button>
            <button onclick="resetForm()">➕ New</button>
        </div>

    </div>

    <hr>

    <h2>Your Movies</h2>
    <div id="moviesDB" class="movies-container"></div>

</main>

<script src="API_Ops.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    getMoviesFromDB();
});

// SAVE (ADD or EDIT)
function saveMovie() {
    const id = document.getElementById("movieId").value;
    const title = document.getElementById("title").value.trim();
    const image = document.getElementById("image").value.trim();
    const rating = document.getElementById("rating").value.trim();

    if (!title || !image) {
        alert("Please fill all fields!");
        return;
    }

    const action = id ? "update" : "add";

    fetch("DB_Ops.php", {
        method: "POST",
        body: new URLSearchParams({
            action: action,
            id: id,
            title: title,
            image: image,
            rating: rating || 0
        })
    })
    .then(res => res.json())
    .then(data => {
        alert(action === "add" ? "Added!" : "Updated!");
        resetForm();
        getMoviesFromDB();
    });
}

// EDIT
function fillForm(id, title, image, rating) {
    document.getElementById("movieId").value = id;
    document.getElementById("title").value = title;
    document.getElementById("image").value = image;
    document.getElementById("rating").value = rating;
}

// RESET
function resetForm() {
    document.getElementById("movieId").value = "";
    document.getElementById("title").value = "";
    document.getElementById("image").value = "";
    document.getElementById("rating").value = "";
}
</script>

<?php include 'footer.php'; ?>