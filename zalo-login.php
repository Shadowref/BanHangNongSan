<?php
session_start();

// Thông tin app Zalo
$app_id = 'YOUR_APP_ID';
$redirect_uri = urlencode('YOUR_REDIRECT_URI'); // ví dụ: https://yourwebsite.com/zalo_callback.php
$state = bin2hex(random_bytes(8)); // chống CSRF
$_SESSION['oauth_state'] = $state;

// URL đăng nhập Zalo
$login_url = "https://oauth.zaloapp.com/v4/permission?app_id=$app_id&redirect_uri=$redirect_uri&state=$state&scope=basic";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Đăng nhập Zalo</title>
</head>
<body>
    <h2>Đăng nhập bằng Zalo</h2>
    <a href="<?php echo $login_url; ?>"><button>Đăng nhập Zalo</button></a>
</body>
</html>
