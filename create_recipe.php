<?php
require_once 'db.php';
requireLogin();

$catStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = sanitize($_POST['title']);
    $categoryId   = (int)$_POST['category_id'];
    $description  = sanitize($_POST['description']);
    $servings     = (int)$_POST['servings'];
    $prepTime     = (int)$_POST['prep_time'];
    $bakeTime     = (int)$_POST['bake_time'];
    $instructions = sanitize($_POST['instructions']);

    $ingNames = $_POST['ing_name'] ?? [];
    $ingQtys  = $_POST['ing_qty'] ?? [];
    $ingUnits = $_POST['ing_unit'] ?? [];

    if (empty($title) || empty($description) || empty($instructions) || empty($ingNames[0])) {
        $error = "Please fill in all required fields and include at least one ingredient.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO recipes (user_id, category_id, title, description, servings, prep_time, bake_time, instructions) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $categoryId, $title, $description, $servings, $prepTime, $bakeTime, $instructions]);
            $recipeId = $pdo->lastInsertId();

            $ingStmt = $pdo->prepare("INSERT INTO ingredients (recipe_id, item_name, quantity, unit) VALUES (?, ?, ?, ?)");
            for ($i = 0; $i < count($ingNames); $i++) {
                if (!empty(trim($ingNames[$i]))) {
                    $ingStmt->execute([$recipeId, sanitize($ingNames[$i]), (float)$ingQtys[$i], sanitize($ingUnits[$i] ?? '')]);
                }
            }

            $pdo->commit();
            header("Location: recipe_detail.php?id=" . $recipeId);
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = "Failed to post recipe. Please check your inputs.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Recipe - Barangay Bakehouse</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php require_once 'nav.php'; ?>

<main class="main-wrapper">
    <div class="form-container">
        <h2>Share a New Baking Recipe</h2>
        <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

        <form method="POST" action="create_recipe.php" class="form-grid">
            <div class="form-group">
                <label>Recipe Title *</label>
                <input type="text" name="title" required placeholder="e.g. Classic Pan de Sal">
            </div>

            <div class="form-group">
                <label>Category *</label>
                <select name="category_id" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= sanitize($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group full-width">
                <label>Short Description *</label>
                <textarea name="description" rows="2" required placeholder="Brief intro..."></textarea>
            </div>

            <div class="form-group">
                <label>Base Servings *</label>
                <input type="number" name="servings" value="12" min="1" required>
            </div>

            <div class="form-group">
                <label>Prep Time (mins) *</label>
                <input type="number" name="prep_time" value="20" min="1" required>
            </div>

            <div class="form-group">
                <label>Bake Time (mins) *</label>
                <input type="number" name="bake_time" value="25" min="0" required>
            </div>

            <!-- Dynamic Ingredients -->
            <div class="form-group full-width">
                <label>Ingredients List *</label>
                <div id="ingContainer">
                    <div class="ing-row">
                        <input type="text" name="ing_name[]" placeholder="Ingredient name" required>
                        <input type="number" step="0.01" name="ing_qty[]" placeholder="Qty" required>
                        <input type="text" name="ing_unit[]" placeholder="Unit (g, cups, tbsp)">
                    </div>
                </div>
                <button type="button" id="addIngBtn" class="btn btn-secondary mt-10">+ Add Another Ingredient</button>
            </div>

            <div class="form-group full-width">
                <label>Baking Steps & Instructions *</label>
                <textarea name="instructions" rows="6" required placeholder="Step 1... Step 2..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary full-width">Publish Recipe</button>
        </form>
    </div>
</main>

<script src="assets/js/main.js"></script>
</body>
</html>