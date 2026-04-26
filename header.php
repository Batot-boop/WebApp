<?php
$loggedIn = isLoggedIn();
?>
<header class="header">
    <div class="logo"><i class="fas fa-film"></i> Movie<span style="color: #a5abff;">Tracker</span></div>
    <input type="checkbox" id="menu-toggle" class="menu-toggle">
    <label for="menu-toggle" class="hamburger"><i class="fas fa-bars"></i></label>
    <nav class="nav">
        <ul class="nav-list">
            <li><a data-page="home" class="nav-link active">Home</a></li>
            <?php if ($loggedIn): ?>
                <li><a data-page="watchlist" class="nav-link">My Watchlist</a></li>
                <li><a id="logoutLink" class="nav-link" href="#">Logout</a></li>
            <?php else: ?>
                <li><a data-page="signin" class="nav-link">Sign In</a></li>
                <li><a data-page="signup" class="nav-link">Sign Up</a></li>
            <?php endif; ?>
            <li><a href="#" class="nav-link" data-page="upload"><i class="fas fa-upload"></i> Upload</a></li>
        </ul>
    </nav>
</header>