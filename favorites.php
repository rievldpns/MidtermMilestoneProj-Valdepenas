<?php
require_once 'db.php';
requireLogin();

$userId = $_SESSION['user_id'];

$sql = "SELECT r.*, c.name AS category_name, CONCAT(u.first_name, ' ', u.last_name) AS author_name,
        (SELECT COUNT(*) FROM favorites f2 WHERE f2.recipe_id = r.id) AS fav_count
        FROM favorites f
        JOIN recipes r ON f.recipe_id = r.id
        JOIN categories c ON r.category_id = c.id
        JOIN users u ON r.user_id = u.id
        WHERE f.user_id = ?
        ORDER BY f.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$recipes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Favorites - Barangay Bakehouse</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php require_once 'nav.php'; ?>

<main class="main-wrapper">
    <div class="hero-banner">
        <h1>Your Saved Favorites</h1>
        <p>Quick access to all recipes you saved for future baking.</p>
    </div>

    <section class="recipe-grid">
        <?php if (empty($recipes)): ?>
            <div class="empty-state">
                <p>You have not saved any recipes yet. <a href="index.php">Browse recipes</a> to add your favorites.</p>
            </div>
        <?php else: ?>
            <?php foreach ($recipes as $r): ?>
                <div class="recipe-card">
                    <div class="card-header">
                        <span class="category-badge"><?= sanitize($r['category_name']) ?></span>
                        <span class="fav-count">Saved: <?= $r['fav_count'] ?></span>
                    </div>
                    <h3><a href="recipe_detail.php?id=<?= $r['id'] ?>"><?= sanitize($r['title']) ?></a></h3>
                    <p class="description"><?= sanitize(substr($r['description'], 0, 110)) ?>...</p>
                    <div class="meta-info">
                        <span>Prep: <?= $r['prep_time'] ?> min | Bake: <?= $r['bake_time'] ?> min</span>
                        <span>Baker: <?= sanitize($r['author_name']) ?></span>
                    </div>
                    <a href="recipe_detail.php?id=<?= $r['id'] ?>" class="btn btn-outline">View Recipe & Steps &rarr;</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<footer class="footer">
    <p>&copy; <?= date('Y') ?> Barangay Bakehouse - Shared by local home bakers.</p>
</footer>

</body>
</html>