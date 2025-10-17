<?php
require_once 'vendor/autoload.php';

$client = new Google_Client();
$client->setClientId("1026943602008-7qeiocgt704vnup7tbb7mq3sq0hkalpc.apps.googleusercontent.com");
$client->setClientSecret("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");
$client->setRedirectUri("http://localhost/banhangonline/google_callback.php");

// Quyền yêu cầu
$client->addScope("email");
$client->addScope("profile");

// Luôn hiển thị chọn tài khoản Google
$client->setPrompt("select_account");

$login_url = $client->createAuthUrl();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập bằng Google</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        .login-container {
            text-align: center;
        }

        .google-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            border: 2px solid #4285F4;
            color: #444;
            font-size: 16px;
            font-weight: 500;
            padding: 12px 20px;
            border-radius: 50px;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
        }

        .google-btn:hover {
            background-color: #f8f9fa;
            box-shadow: 0 6px 14px rgba(0,0,0,0.25);
            transform: translateY(-2px);
        }

        .google-btn img {
            height: 24px;
            width: 24px;
            margin-right: 10px;
        }

        h2 {
            color: white;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Đăng nhập vào hệ thống</h2>
        <a class="google-btn" href="<?php echo htmlspecialchars($login_url); ?>">
            <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google Logo">
            Đăng nhập với Google
        </a>
    </div>
</body>
</html>
