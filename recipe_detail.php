<?php
require_once 'db.php';
requireLogin();

$recipeId = (int)($_GET['id'] ?? 0);
$userId   = $_SESSION['user_id'];

// Fetch recipe details with category and author name
$stmt = $pdo->prepare("SELECT r.*, c.name AS category_name, CONCAT(u.first_name, ' ', u.last_name) AS author_name 
                       FROM recipes r 
                       JOIN categories c ON r.category_id = c.id 
                       JOIN users u ON r.user_id = u.id 
                       WHERE r.id = ?");
$stmt->execute([$recipeId]);
$recipe = $stmt->fetch();

if (!$recipe) {
    header("Location: index.php");
    exit;
}

// Fetch ingredients list
$ingStmt = $pdo->prepare("SELECT * FROM ingredients WHERE recipe_id = ? ORDER BY id ASC");
$ingStmt->execute([$recipeId]);
$ingredients = $ingStmt->fetchAll();

// Fetch comments
$comStmt = $pdo->prepare("SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) AS commenter_name 
                          FROM comments c 
                          JOIN users u ON c.user_id = u.id 
                          WHERE c.recipe_id = ? 
                          ORDER BY c.created_at DESC");
$comStmt->execute([$recipeId]);
$comments = $comStmt->fetchAll();

// Check if favorited by current logged-in user
$favCheck = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND recipe_id = ?");
$favCheck->execute([$userId, $recipeId]);
$isFav = (bool)$favCheck->fetch();

$isOwner = ($recipe['user_id'] == $userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($recipe['title']) ?> - Barangay Bakehouse</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php require_once 'nav.php'; ?>

<main class="main-wrapper">
    <div class="recipe-detail-container">
        <div class="recipe-detail-header">
            <div>
                <span class="category-badge"><?= sanitize($recipe['category_name']) ?></span>
                <h1><?= sanitize($recipe['title']) ?></h1>
                <p class="author-meta">
                    Posted by <strong><?= sanitize($recipe['author_name']) ?></strong> on <?= date('M d, Y', strtotime($recipe['created_at'])) ?>
                    <?php if ($recipe['updated_at'] > $recipe['created_at']): ?>
                        <span class="edited-tag">(edited)</span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="action-buttons">
                <button type="button" id="favBtn" data-id="<?= $recipeId ?>" class="btn btn-fav <?= $isFav ? 'btn-fav-active' : '' ?>">
                    <?= $isFav ? 'Saved' : 'Save Favorite' ?>
                </button>
                <?php if ($isOwner): ?>
                    <a href="edit_recipe.php?id=<?= $recipeId ?>" class="btn btn-secondary">Edit Recipe</a>
                    <a href="delete_recipe.php?id=<?= $recipeId ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this recipe?');">Delete</a>
                <?php endif; ?>
            </div>
        </div>

        <p class="recipe-desc"><?= sanitize($recipe['description']) ?></p>

        <div class="meta-info mb-20">
            <span>Prep Time: <?= (int)$recipe['prep_time'] ?> min | Bake Time: <?= (int)$recipe['bake_time'] ?> min</span>
        </div>

        <div class="recipe-content-grid">
            <div class="ingredients-card">
                <h3>Ingredients</h3>
                <ul class="ingredients-list">
                    <?php foreach ($ingredients as $ing): 
                        $ingName = $ing['name'] ?? $ing['ingredient_name'] ?? $ing['ingredient'] ?? '';
                        $ingQty  = $ing['quantity'] ?? $ing['qty'] ?? 0;
                        $ingUnit = $ing['unit'] ?? '';
                    ?>
                        <li>
                            <strong><?= number_format((float)$ingQty, 2) ?> <?= sanitize($ingUnit) ?></strong> <?= sanitize($ingName) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="instructions-card">
                <h3>Baking Instructions</h3>
                <div class="instructions-body">
                    <?= nl2br(sanitize($recipe['instructions'])) ?>
                </div>
            </div>
        </div>

        <section class="comments-section">
            <h3>Feedback & Comments (<?= count($comments) ?>)</h3>

            <form method="POST" action="comment.php" class="mt-20">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="recipe_id" value="<?= $recipeId ?>">
                <div class="form-group">
                    <textarea name="content" rows="3" placeholder="Leave feedback or tips for this baker..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary mt-10">Post Feedback</button>
            </form>

            <div class="comments-list mt-20">
                <?php if (empty($comments)): ?>
                    <p class="subtitle mt-10">No feedback posted yet. Be the first to leave a comment!</p>
                <?php else: ?>
                    <?php foreach ($comments as $c): ?>
                        <div class="comment-card">
                            <div class="comment-meta">
                                <strong><?= sanitize($c['commenter_name']) ?></strong>
                                <span><?= date('M d, Y h:i A', strtotime($c['created_at'])) ?></span>
                            </div>
                            <p class="comment-body"><?= sanitize($c['content']) ?></p>
                            <?php if ($c['user_id'] == $userId): ?>
                                <div class="comment-actions">
                                    <form method="POST" action="comment.php" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="recipe_id" value="<?= $recipeId ?>">
                                        <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn-link" onclick="return confirm('Delete this comment?');">Delete</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<footer class="footer">
    <p>&copy; <?= date('Y') ?> Barangay Bakehouse - Shared by local home bakers.</p>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>