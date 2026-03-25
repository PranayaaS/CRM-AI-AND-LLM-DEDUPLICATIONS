<?php
// index.php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require 'config/db.php';
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        // Check using MD5 since that's what we seeded the DB with
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ? AND password = MD5(?)");
        $stmt->execute([$email, $password]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Log the login action
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
            $action = 'User Login';
            $details = "User successfully authenticated.";
            try {
                $log_stmt = $pdo->prepare("INSERT INTO AuditLogs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
                $log_stmt->execute([$user['id'], $action, $details, $ip_address]);
            } catch (PDOException $e) {
                // Silently ignore log failure or handle it
            }

            header("Location: dashboard.php");
            exit();
        } else {
            $error = 'Invalid email or password!';
        }
    } else {
        $error = 'Please fill in all fields!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-box { max-width: 400px; width: 100%; padding: 30px; background: #fff; border-radius: 10px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
    </style>
</head>
<body>

<div class="login-box">
    <div class="text-center mb-4">
        <h2 class="fw-bold text-primary">CRM System</h2>
        <p class="text-muted">Database-Driven CRM</p>
    </div>
    
    <?php if($error): ?>
        <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php">
        <div class="mb-3">
            <label class="form-label">Email address</label>
            <input type="email" name="email" class="form-control" required placeholder="admin@crm.local">
        </div>
        <div class="mb-4">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required placeholder="admin123">
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
</div>

</body>
</html>
