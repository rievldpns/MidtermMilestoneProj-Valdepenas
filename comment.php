<?php
require_once 'db.php';
requireLogin();

$action   = $_POST['action'] ?? '';
$recipeId = (int)($_POST['recipe_id'] ?? 0);
$userId   = $_SESSION['user_id'];

if ($action === 'add' && !empty($_POST['content'])) {
    $content = sanitize($_POST['content']);
    $stmt = $pdo->prepare("INSERT INTO comments (recipe_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$recipeId, $userId, $content]);

} elseif ($action === 'edit' && !empty($_POST['content'])) {
    $commentId = (int)($_POST['comment_id'] ?? 0);
    $content   = sanitize($_POST['content']);
    $stmt = $pdo->prepare("UPDATE comments SET content = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$content, $commentId, $userId]);

} elseif ($action === 'delete') {
    $commentId = (int)($_POST['comment_id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
    $stmt->execute([$commentId, $userId]);
}

header("Location: recipe_detail.php?id=" . $recipeId);
exit;