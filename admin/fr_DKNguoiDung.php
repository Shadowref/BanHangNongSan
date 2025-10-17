<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

$message = '';
$conn = new mysqli("localhost", "root", "", "banhangonline");
if ($conn->connect_error) die("Kết nối DB thất bại: " . $conn->connect_error);

// Reset session khi mở lại trang
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    unset($_SESSION['reg_data'], $_SESSION['otp'], $_SESSION['otp_expire'], $_SESSION['otp_verified']);
}

// ===== Bước 1: Nhập thông tin đăng ký & gửi OTP =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');

    if (!$username || !$email || !$password || !$confirm_password) {
        $message = "Vui lòng điền đầy đủ thông tin.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email không hợp lệ.";
    } elseif ($password !== $confirm_password) {
        $message = "Mật khẩu xác nhận không khớp.";
    } else {
        // Kiểm tra trùng username/email
        $stmt = $conn->prepare("SELECT id FROM taikhoan WHERE username=? OR email=?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Tên đăng nhập hoặc email đã tồn tại.";
        } else {
            // Sinh OTP
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_expire'] = time() + 300; // 5 phút
            $_SESSION['reg_data'] = [
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'phone' => $phone
            ];

            // Gửi email OTP
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = "smtp.gmail.com";
                $mail->SMTPAuth = true;
                $mail->Username = "suongnie2k5@gmail.com"; // Gmail của bạn
                $mail->Password = "heqwfsjnixruxjtw";        // App password Gmail
                $mail->SMTPSecure = "tls";
                $mail->Port = 587;

                $mail->CharSet = "UTF-8";
                $mail->setFrom("suongnie2k5@gmail.com.com", "Đăng ký");
                $mail->addAddress($email);
                $mail->Subject = "Mã OTP xác thực đăng ký";
                $mail->Body = "Mã OTP của bạn là: $otp (hết hạn sau 5 phút)";

                $mail->send();
                $message = "<span style='color:green;'>OTP đã gửi đến email $email</span>";
            } catch (Exception $e) {
                $message = "Không gửi được OTP: " . $mail->ErrorInfo;
            }
        }
        $stmt->close();
    }
}

// ===== Bước 2: Xác thực OTP =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    $otp = $_POST['otp'] ?? '';

    if ($otp == ($_SESSION['otp'] ?? '') && time() <= ($_SESSION['otp_expire'] ?? 0)) {
        // Insert user vào DB
        $data = $_SESSION['reg_data'];
        $insert = $conn->prepare("INSERT INTO taikhoan (username, email, password, phone) VALUES (?, ?, ?, ?)");
        $insert->bind_param("ssss", $data['username'], $data['email'], $data['password'], $data['phone']);

        if ($insert->execute()) {
            $message = "<span style='color:green;'>Đăng ký thành công! <a href='index.php'>Đăng nhập</a></span>";
            unset($_SESSION['otp'], $_SESSION['otp_expire'], $_SESSION['reg_data']);
        } else {
            $message = "Lỗi khi lưu dữ liệu: " . $conn->error;
        }
        $insert->close();
    } else {
        $message = "OTP không hợp lệ hoặc đã hết hạn!";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đăng ký tài khoản</title>
<style>
    body { font-family: Arial, sans-serif; background:#f2f2f2; display:flex; justify-content:center; align-items:center; height:100vh; }
    .box { background:#fff; padding:30px; border-radius:12px; box-shadow:0 5px 20px rgba(0,0,0,0.2); width:380px; }
    h2 { color:#05602b; text-align:center; margin-bottom:20px; }
    .message { margin-bottom:15px; font-size:14px; text-align:center; }
    .input-field { margin:10px 0; }
    .input-field input { width:100%; padding:12px; border:1px solid #ccc; border-radius:8px; }
    .btn { width:100%; padding:12px; background:#27ae60; border:none; border-radius:8px; color:#fff; font-weight:bold; cursor:pointer; margin-top:10px; }
    .btn:hover { background:#219150; }
</style>
</head>
<body>
<div class="box">
    <h2>Đăng ký người dùng</h2>
    <?php if($message) echo "<div class='message'>$message</div>"; ?>

    <?php if(empty($_SESSION['otp'])): ?>
        <!-- Form đăng ký -->
        <form method="POST">
            <div class="input-field"><input type="text" name="username" placeholder="Tên đăng nhập" required></div>
            <div class="input-field"><input type="email" name="email" placeholder="Email" required></div>
            <div class="input-field"><input type="password" name="password" placeholder="Mật khẩu" required></div>
            <div class="input-field"><input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu" required></div>
            <div class="input-field"><input type="tel" name="phone" placeholder="Số điện thoại"></div>
            <button type="submit" name="register" class="btn">Đăng ký</button>
        </form>

    <?php else: ?>
        <!-- Form nhập OTP -->
        <form method="POST">
            <div class="input-field"><input type="text" name="otp" placeholder="Nhập OTP đã gửi qua email" required></div>
            <button type="submit" name="verify_otp" class="btn">Xác nhận OTP</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
