<?php
require_once 'config.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: index.php');
        exit;
    } else {
        $error_message = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin Panel Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .login-container {
            background: white;
            padding: 40px 36px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        .login-header { text-align: center; margin-bottom: 30px; }
        .login-header h1 { font-size: 26px; color: #333; margin-bottom: 8px; }
        .login-header p { color: #888; font-size: 14px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 600; font-size: 14px; }
        .form-group input {
            width: 100%;
            padding: 14px;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .form-group input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
        .error-message {
            background: #ffebee; color: #c62828; padding: 12px; border-radius: 8px;
            margin-bottom: 20px; font-size: 14px; border-left: 4px solid #f44336;
        }
        .login-btn {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; border: none; border-radius: 8px;
            font-size: 16px; font-weight: 600; cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            min-height: 50px;
        }
        .login-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(102,126,234,0.35); }
        .login-footer { text-align: center; margin-top: 20px; color: #999; font-size: 13px; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #667eea; font-size: 14px; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🏨 Admin Panel</h1>
            <p>Luxury Hotel Booking System</p>
        </div>

        <?php if ($error_message): ?>
            <div class="error-message"><i class="ri-alert-line"></i> <?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Masukkan username" required autofocus autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password" required autocomplete="current-password">
            </div>
            <button type="submit" class="login-btn">🔐 Login</button>
        </form>

        <a href="../index.html" class="back-link"><i class="ri-arrow-left-line"></i> Kembali ke Website</a>
    </div>
</body>
</html>
