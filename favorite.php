<?php
require_once 'db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$recipeId = (int)($data['recipe_id'] ?? 0);
$userId = $_SESSION['user_id'];

if ($recipeId > 0) {
    $check = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND recipe_id = ?");
    $check->execute([$userId, $recipeId]);
    
    if ($check->fetch()) {
        $del = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?");
        $del->execute([$userId, $recipeId]);
        $status = 'removed';
    } else {
        $ins = $pdo->prepare("INSERT INTO favorites (user_id, recipe_id) VALUES (?, ?)");
        $ins->execute([$userId, $recipeId]);
        $status = 'added';
    }

    echo json_encode(['status' => $status]);
} else {
    echo json_encode(['error' => 'Invalid recipe ID']);
}