<?php
require_once 'db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipeId = (int)($_POST['recipe_id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM recipes WHERE id = ? AND user_id = ?");
    $stmt->execute([$recipeId, $_SESSION['user_id']]);
}

header("Location: index.php");
exit;