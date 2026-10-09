<?php
require_once 'db.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitize($_POST['first_name']);
    $lastName  = sanitize($_POST['last_name']);
    $email     = sanitize($_POST['email']);
    $password  = $_POST['password'];

    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        
        if ($check->fetch()) {
            $error = "An account with this email address already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
            
            if ($stmt->execute([$firstName, $lastName, $email, $hashedPassword])) {
                $success = "Account created successfully. You can now <a href='login.php'>log in here</a>.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Barangay Bakehouse</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-card">
    <h2>Join Barangay Bakehouse</h2>
    <p class="subtitle">Create an account to browse and share baking recipes.</p>

    <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php else: ?>

    <form method="POST" action="register.php" class="form-grid">
        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="first_name" required>
        </div>
        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="last_name" required>
        </div>
        <div class="form-group full-width">
            <label>Email Address</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group full-width">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary full-width">Create Account</button>
    </form>
    <p class="auth-footer">Already a member? <a href="login.php">Log in here</a></p>
    <?php endif; ?>
</div>
</body>
</html>