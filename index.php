<?php
require_once 'db.php';
requireLogin();

$catStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll();

$searchKeyword = sanitize($_GET['search'] ?? '');
$selectedCat   = (int)($_GET['category'] ?? 0);

$sql = "SELECT r.*, c.name AS category_name, CONCAT(u.first_name, ' ', u.last_name) AS author_name,
        (SELECT COUNT(*) FROM favorites f WHERE f.recipe_id = r.id) AS fav_count
        FROM recipes r
        JOIN categories c ON r.category_id = c.id
        JOIN users u ON r.user_id = u.id
        WHERE 1=1";

$params = [];

if (!empty($searchKeyword)) {
    $sql .= " AND (r.title LIKE ? OR r.description LIKE ?)";
    $params[] = "%$searchKeyword%";
    $params[] = "%$searchKeyword%";
}

if ($selectedCat > 0) {
    $sql .= " AND r.category_id = ?";
    $params[] = $selectedCat;
}

$sql .= " ORDER BY r.created_at DESC";

$recipeStmt = $pdo->prepare($sql);
$recipeStmt->execute($params);
$recipes = $recipeStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Bakehouse - Home</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php require_once 'nav.php'; ?>

<main class="main-wrapper">
    <div class="hero-banner">
        <h1>Community Baking Recipe Feed</h1>
        <p>Explore home-baked recipes posted by local neighborhood bakers.</p>
    </div>

    <section class="filter-section">
        <form method="GET" action="index.php" class="search-form">
            <input type="text" name="search" value="<?= $searchKeyword ?>" placeholder="Search recipe title or description...">
            <select name="category">
                <option value="0">All Baked Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $selectedCat == $cat['id'] ? 'selected' : '' ?>>
                        <?= sanitize($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($searchKeyword || $selectedCat): ?>
                <a href="index.php" class="btn btn-secondary">Reset Filters</a>
            <?php endif; ?>
        </form>
    </section>

    <section class="recipe-grid">
        <?php if (empty($recipes)): ?>
            <div class="empty-state">
                <p>No baking recipes found. Try another search or <a href="create_recipe.php">post a recipe</a>.</p>
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
                        <?php if ($r['updated_at'] > $r['created_at']): ?>
                            <span class="edited-tag">(edited)</span>
                        <?php endif; ?>
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