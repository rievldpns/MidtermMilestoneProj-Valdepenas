<?php
require_once 'db.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$userName    = isset($_SESSION['name']) ? sanitize($_SESSION['name']) : 'Member';
?>
<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="brand-logo">Barangay Bakehouse</a>
        
        <?php if (isLoggedIn()): ?>
            <div class="nav-links">
                <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Browse Recipes</a>
                <a href="create_recipe.php" class="<?= $currentPage === 'create_recipe.php' ? 'active' : '' ?>">+ Share Recipe</a>
                <a href="favorites.php" class="<?= $currentPage === 'favorites.php' ? 'active' : '' ?>">Saved Favorites</a>
                <span class="user-welcome">Welcome, <?= $userName ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        <?php else: ?>
            <div class="nav-links">
                <a href="login.php" class="<?= $currentPage === 'login.php' ? 'active' : '' ?>">Log In</a>
                <a href="register.php" class="<?= $currentPage === 'register.php' ? 'active' : '' ?>">Register</a>
            </div>
        <?php endif; ?>
    </div>
</nav>