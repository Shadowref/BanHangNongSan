<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

$message = '';

$conn = new mysqli("localhost", "root", "", "banhangonline");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

// ===== Reset session khi vừa mở trang (không submit gì) =====
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    unset($_SESSION['show_otp_form'], $_SESSION['otp_verified'], $_SESSION['otp'], $_SESSION['otp_expire']);
}

// ===== GỬI OTP =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_otp'])) {
    $input = trim($_POST['username'] ?? '');
    if ($input) {
        $stmt = $conn->prepare("SELECT id, email FROM taikhoan WHERE username=? OR email=? OR phone=?");
        $stmt->bind_param("sss", $input, $input, $input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $otp = rand(100000, 999999);

            $_SESSION['otp'] = $otp;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['otp_expire'] = time() + 300; // 5 phút
            $_SESSION['show_otp_form'] = true;

            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = "smtp.gmail.com";
                $mail->SMTPAuth = true;
                $mail->Username = "suongnie2k5@gmail.com"; // Gmail của bạn
                $mail->Password = "heqwfsjnixruxjtw";      // App password Gmail
                $mail->SMTPSecure = "tls";
                $mail->Port = 587;

                $mail->CharSet = "UTF-8";
                $mail->Encoding = "base64";

                $mail->setFrom("suongnie2k5@gmail.com", "Support");
                $mail->addAddress($user['email']);
                $mail->Subject = "Mã OTP khôi phục mật khẩu";
                $mail->Body = "Mã OTP của bạn là: $otp (hết hạn sau 5 phút)";

                $mail->send();
                $message = "<span style='color:green;'>OTP đã gửi đến email của bạn.</span>";
            } catch (Exception $e) {
                $message = "Không gửi được OTP: " . $mail->ErrorInfo;
            }
        } else {
            $message = "Không tìm thấy tài khoản!";
        }
        $stmt->close();
    }
}

// ===== XÁC NHẬN OTP =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    $otp = $_POST['otp'] ?? '';

    if ($otp == ($_SESSION['otp'] ?? '') && time() <= ($_SESSION['otp_expire'] ?? 0)) {
        $_SESSION['otp_verified'] = true;
        $message = "<span style='color:green;'>Xác thực OTP thành công! Vui lòng nhập mật khẩu mới.</span>";
    } else {
        $message = "OTP không hợp lệ hoặc đã hết hạn!";
    }
}

// ===== ĐỔI MẬT KHẨU =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_pass'])) {
    if (!empty($_SESSION['otp_verified']) && $_SESSION['otp_verified'] === true) {
        $new_pass = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($new_pass && $new_pass === $confirm) {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $user_id = $_SESSION['user_id'];

            $update = $conn->prepare("UPDATE taikhoan SET password=? WHERE id=?");
            $update->bind_param("si", $hashed, $user_id);
            if ($update->execute()) {
                $message = "<span style='color:green;'>Đặt lại mật khẩu thành công! <a href='index.php'>Đăng nhập</a></span>";
                unset($_SESSION['otp'], $_SESSION['user_id'], $_SESSION['otp_expire'], $_SESSION['show_otp_form'], $_SESSION['otp_verified']);
            } else {
                $message = "Lỗi hệ thống, thử lại!";
            }
            $update->close();
        } else {
            $message = "Mật khẩu không khớp!";
        }
    } else {
        $message = "Bạn chưa xác thực OTP!";
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quên mật khẩu</title>
<style>
    body { font-family: Arial, sans-serif; background: #f2f2f2; display:flex; justify-content:center; align-items:center; height:100vh; }
    .box { background:#fff; padding:30px; border-radius:12px; box-shadow:0 5px 20px rgba(0,0,0,0.2); width:380px; }
    h2 { color:#ff416c; margin-bottom:20px; text-align:center; }
    .message { margin-bottom:15px; font-size:14px; text-align:center; }
    .input-field { margin:10px 0; }
    .input-field input { width:100%; padding:12px; border:1px solid #ccc; border-radius:8px; }
    .btn { width:100%; padding:12px; background:#ff416c; border:none; border-radius:8px; color:#fff; font-weight:bold; cursor:pointer; margin-top:10px; }
    .btn:hover { background:#ff1e5f; }
    a { color:#ff416c; text-decoration:none; }
</style>
</head>
<body>
<div class="box">
    <h2>Quên mật khẩu</h2>
    <?php if($message) echo "<div class='message'>$message</div>"; ?>

    <?php if(empty($_SESSION['show_otp_form'])): ?>
        <!-- Form gửi OTP -->
        <form method="POST">
            <div class="input-field">
                <input type="text" name="username" placeholder="Tên đăng nhập / Email / SĐT" required>
            </div>
            <button type="submit" name="send_otp" class="btn">Gửi OTP</button>
        </form>

    <?php elseif(empty($_SESSION['otp_verified'])): ?>
        <!-- Form nhập OTP -->
        <form method="POST">
            <div class="input-field">
                <input type="text" name="otp" placeholder="Nhập OTP" required>
            </div>
            <button type="submit" name="verify_otp" class="btn">Xác nhận OTP</button>
        </form>

    <?php else: ?>
        <!-- Form đổi mật khẩu -->
        <form method="POST">
            <div class="input-field">
                <input type="password" name="new_password" placeholder="Mật khẩu mới" required>
            </div>
            <div class="input-field">
                <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu" required>
            </div>
            <button type="submit" name="change_pass" class="btn">Đổi mật khẩu</button>
        </form>
    <?php endif; ?>

    <p style="margin-top:15px; text-align:center;"><a href="index.php">Quay lại đăng nhập</a></p>
</div>
</body>
</html>
