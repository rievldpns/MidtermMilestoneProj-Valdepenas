<?php
require_once 'db.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['first_name'] . ' ' . $user['last_name'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid email address or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Barangay Bakehouse</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-card">
    <h2>Member Login</h2>
    <p class="subtitle">Log in to view recipes, save favorites, and leave comments.</p>

    <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

    <form method="POST" action="login.php" class="form-grid">
        <div class="form-group full-width">
            <label>Email Address</label>
            <input type="email" name="email" required autofocus>
        </div>
        <div class="form-group full-width">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary full-width">Log In</button>
    </form>
    <p class="auth-footer">Don't have an account? <a href="register.php">Register here</a></p>
</div>
</body>
</html>