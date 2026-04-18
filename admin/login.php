<?php

//  Admin login page with brute force protection

require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in go straight to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: " . SITE_URL . "/admin/index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Brute Force Protection 

    // Set up attempt tracking if not already set
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt']   = time();
    }

    // Reset attempts after 15 minutes
    if (time() - $_SESSION['last_attempt'] > 900) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt']   = time();
    }

    // Block login if 5 or more failed attempts
    if ($_SESSION['login_attempts'] >= 5) {
        $minutes_left = ceil((900 - (time() - $_SESSION['last_attempt'])) / 60);
        $error = "Too many failed attempts. Please wait {$minutes_left} minute(s) and try again.";

    } else {
        // Normal Login Logic 
        $username = sanitize($_POST['username']);
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            $error = "Please enter your username and password.";
        } else {
            $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $admin  = $result->fetch_assoc();
            $stmt->close();

            if ($admin && password_verify($password, $admin['password'])) {
                // Login successful 

                // Reset attempt counter on successful login
                $_SESSION['login_attempts'] = 0;

                // Store admin info in session
                $_SESSION['admin_id']   = $admin['id'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['admin_user'] = $admin['username'];

                header("Location: " . SITE_URL . "/admin/index.php");
                exit();

            } else {
                // Login failed 

                // Increment failed attempt counter
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt'] = time();

                $attempts_left = 5 - $_SESSION['login_attempts'];

                if ($attempts_left > 0) {
                    $error = "Invalid username or password. {$attempts_left} attempt(s) remaining.";
                } else {
                    $error = "Too many failed attempts. Please wait 15 minutes and try again.";
                }
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
    <title>Admin Login | <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #8B6914;
            --primary-dark: #6B5010;
            --dark: #1a1a2e;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--dark) 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
        }
        .login-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 2rem;
            text-align: center;
            color: #fff;
        }
        .login-header h4 {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            margin: 0;
        }
        .login-body { padding: 2rem; }
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(139,105,20,0.15);
        }
        .attempts-warning {
            font-size: 0.8rem;
            color: #856404;
            background: #fff3cd;
            border-radius: 6px;
            padding: 8px 12px;
            margin-top: 8px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <i class="bi bi-shield-lock fs-1 mb-2 d-block"></i>
        <h4>Admin Panel</h4>
        <small class="opacity-75">Guesthouse Management System</small>
    </div>
    <div class="login-body">

        <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <?= $error ?>
        </div>
        <?php endif; ?>

        <?php
        // Show attempts warning when getting close to lockout
        $attempts = $_SESSION['login_attempts'] ?? 0;
        if ($attempts >= 3 && $attempts < 5):
        ?>
        <div class="attempts-warning">
            <i class="bi bi-exclamation-triangle me-1"></i>
            Warning — you will be locked out after 5 failed attempts.
        </div>
        <?php endif; ?>

        <form method="POST" class="mt-3">
            <div class="mb-3">
                <label class="form-label fw-semibold">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control"
                           placeholder="Enter username"
                           value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                           <?= ($attempts >= 5) ? 'disabled' : '' ?>
                           required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control"
                           placeholder="Enter password"
                           <?= ($attempts >= 5) ? 'disabled' : '' ?>
                           required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold"
                    <?= ($attempts >= 5) ? 'disabled' : '' ?>>
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="<?= SITE_URL ?>/public/index.php" class="text-muted small">
                <i class="bi bi-arrow-left me-1"></i>Back to Website
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    const input = document.querySelector('input[name="password"]');
    const icon  = document.getElementById('toggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
});
</script>
</body>
</html>