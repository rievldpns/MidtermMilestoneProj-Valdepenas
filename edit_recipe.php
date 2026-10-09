<?php
require_once 'db.php';
requireLogin();

$userId   = $_SESSION['user_id'];
$recipeId = (int)($_GET['id'] ?? 0);
$error    = '';

// Fetch existing recipe owned by current user
$stmt = $pdo->prepare("SELECT * FROM recipes WHERE id = ? AND user_id = ?");
$stmt->execute([$recipeId, $userId]);
$recipe = $stmt->fetch();

if (!$recipe) {
    header("Location: index.php");
    exit;
}

// Fetch existing ingredients for this recipe
$ingStmt = $pdo->prepare("SELECT * FROM ingredients WHERE recipe_id = ? ORDER BY id ASC");
$ingStmt->execute([$recipeId]);
$existingIngredients = $ingStmt->fetchAll();

$catStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = sanitize($_POST['title']);
    $description  = sanitize($_POST['description']);
    $categoryId   = (int)$_POST['category_id'];
    $prepTime     = (int)($_POST['prep_time'] ?? 0);
    $bakeTime     = (int)($_POST['bake_time'] ?? 0);
    $instructions = sanitize($_POST['instructions']);

    $ingNames = $_POST['ing_name'] ?? [];
    $ingQtys  = $_POST['ing_qty'] ?? [];
    $ingUnits = $_POST['ing_unit'] ?? [];

    if (empty($title) || empty($description) || $categoryId <= 0 || empty($instructions)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            $pdo->beginTransaction();

            $updateStmt = $pdo->prepare("UPDATE recipes SET title = ?, description = ?, category_id = ?, prep_time = ?, bake_time = ?, instructions = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
            $updateStmt->execute([$title, $description, $categoryId, $prepTime, $bakeTime, $instructions, $recipeId, $userId]);

            $delIng = $pdo->prepare("DELETE FROM ingredients WHERE recipe_id = ?");
            $delIng->execute([$recipeId]);

            $insIng = $pdo->prepare("INSERT INTO ingredients (recipe_id, item_name, quantity, unit) VALUES (?, ?, ?, ?)");

            for ($i = 0; $i < count($ingNames); $i++) {
                $name = sanitize($ingNames[$i]);
                $qty  = (float)($ingQtys[$i] ?? 0);
                $unit = sanitize($ingUnits[$i] ?? '');
                if (!empty($name)) {
                    $insIng->execute([$recipeId, $name, $qty, $unit]);
                }
            }

            $pdo->commit();
            header("Location: recipe_detail.php?id=" . $recipeId);
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "Failed to update recipe: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recipe - Barangay Bakehouse</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php require_once 'nav.php'; ?>

<main class="main-wrapper">
    <div class="form-container">
        <h2>Edit Recipe</h2>
        <p class="subtitle">Update your recipe details and ingredients list.</p>

        <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

        <form method="POST" action="edit_recipe.php?id=<?= $recipeId ?>" class="form-grid">
            <div class="form-group full-width">
                <label>Recipe Title *</label>
                <input type="text" name="title" value="<?= sanitize($recipe['title']) ?>" required>
            </div>

            <div class="form-group">
                <label>Category *</label>
                <select name="category_id" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $recipe['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                            <?= sanitize($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Prep Time (minutes)</label>
                <input type="number" name="prep_time" value="<?= (int)$recipe['prep_time'] ?>" min="0">
            </div>

            <div class="form-group">
                <label>Bake Time (minutes)</label>
                <input type="number" name="bake_time" value="<?= (int)$recipe['bake_time'] ?>" min="0">
            </div>

            <div class="form-group full-width">
                <label>Short Description *</label>
                <textarea name="description" rows="3" required><?= sanitize($recipe['description']) ?></textarea>
            </div>

            <div class="form-group full-width">
                <label>Ingredients *</label>
                <div id="ingContainer">
                    <?php foreach ($existingIngredients as $ing): 
                        $valName = $ing['item_name'] ?? $ing['name'] ?? '';
                        $valQty  = $ing['quantity']  ?? $ing['qty']  ?? 0;
                        $valUnit = $ing['unit']      ?? '';
                    ?>
                        <div class="ing-row">
                            <input type="text" name="ing_name[]" value="<?= sanitize($valName) ?>" placeholder="Ingredient name" required>
                            <input type="number" step="0.01" name="ing_qty[]" value="<?= (float)$valQty ?>" placeholder="Qty" required>
                            <input type="text" name="ing_unit[]" value="<?= sanitize($valUnit) ?>" placeholder="Unit">
                            <button type="button" class="btn btn-danger remove-ing-btn">&times;</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="addIngBtn" class="btn btn-secondary mt-10">+ Add Ingredient</button>
            </div>

            <div class="form-group full-width">
                <label>Baking Instructions & Steps *</label>
                <textarea name="instructions" rows="6" required><?= sanitize($recipe['instructions']) ?></textarea>
            </div>

            <div class="form-group full-width" style="display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="recipe_detail.php?id=<?= $recipeId ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</main>

<footer class="footer">
    <p>&copy; <?= date('Y') ?> Barangay Bakehouse - Shared by local home bakers.</p>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>