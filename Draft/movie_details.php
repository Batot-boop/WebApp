<?php include 'header.php'; ?>

<main class="details-page" id="detailsApp">
    <!-- JS will render the content here -->
    <div class="spinner-wrap" id="loadingSpinner">
        <div class="spinner"></div>
    </div>
</main>

<script src="API_Ops.js"></script>
<script>
    // Read the movie ID from the URL: movie_details.php?id=123
    const urlParams = new URLSearchParams(window.location.search);
    const movieId   = urlParams.get('id');

    if (!movieId) {
        window.location.href = 'index.php';
    } else {
        loadMovieDetails(movieId);
    }

    function loadMovieDetails(id) {
        fetch(`API_Ops.php?action=details&id=${encodeURIComponent(id)}`)
            .then(res => {
                if (!res.ok) throw new Error('Network error');
                return res.json();
            })
            .then(data => renderDetails(data))
            .catch(err => {
                console.error(err);
                renderError('Failed to load movie details. Please try again.');
            });
    }

    function renderDetails(data) {
        const app = document.getElementById('detailsApp');

        if (!data || data.error) {
            renderError(data?.error || 'Movie not found.');
            return;
        }

        const backdropStyle = data.backdrop
            ? `style="background-image: url('${data.backdrop}')"`
            : '';

        const genres = (data.genres && data.genres.length > 0)
            ? data.genres.map(g => `<span class="genre-chip">${g}</span>`).join('')
            : '';

        const trailerBtn = (data.trailer && data.trailer.startsWith('http'))
            ? `<a href="${data.trailer}" target="_blank" class="btn-trailer">▶ Watch Trailer</a>`
            : `<span class="details-badge">No Trailer Available</span>`;

        const ratingBadge = data.rating
            ? `<span class="details-badge accent">⭐ ${parseFloat(data.rating).toFixed(1)}</span>`
            : '';

        const releaseBadge = data.releaseDate
            ? `<span class="details-badge">📅 ${data.releaseDate}</span>`
            : '';

        app.innerHTML = `
            <!-- Hero Backdrop -->
            <div class="details-hero" ${backdropStyle}></div>

            <!-- Content -->
            <div class="details-content">

                <!-- Poster -->
                <div class="details-poster">
                    <img src="${data.image || 'https://via.placeholder.com/220x330?text=No+Image'}"
                         alt="${data.title}">
                </div>

                <!-- Info -->
                <div class="details-info">
                    <h1>${data.title}</h1>

                    <div class="details-meta">
                        ${ratingBadge}
                        ${releaseBadge}
                    </div>

                    ${data.description
                        ? `<p class="details-overview">${data.description}</p>`
                        : ''}

                    <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom: 20px;">
                        ${trailerBtn}
                        <a href="index.php" class="btn-back">← Back to Home</a>
                    </div>

                    ${genres ? `<div class="genres-row">${genres}</div>` : ''}
                </div>

            </div>
        `;
    }

    function renderError(message) {
        document.getElementById('detailsApp').innerHTML = `
            <div class="page-wrapper">
                <div class="state-box error">
                    <div class="state-icon">⚠️</div>
                    <p>${message}</p>
                    <a href="index.php" class="btn-back" style="margin-top:20px; display:inline-block;">← Back to Home</a>
                </div>
            </div>`;
    }
</script>

<?php include 'footer.php'; ?>
