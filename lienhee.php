<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // nếu dùng Composer

$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        $error = "Vui lòng điền đầy đủ thông tin.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ.";
    } else {
        // Lưu vào CSDL
        $conn = new mysqli("localhost", "root", "", "banhangonline");
        $conn->set_charset("utf8");
        if ($conn->connect_error) {
            $error = "Không thể kết nối CSDL.";
        } else {
            $stmt = $conn->prepare("INSERT INTO lienhe(hoten, email, noidung) VALUES (?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sss", $name, $email, $message);
                $stmt->execute();
                $stmt->close();
                $conn->close();

                // Gửi email
                try {
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'suongnie2k5@gmail.com';  // email gửi
                    $mail->Password   = 'heqwfsjnixruxjtw';      // App Password Gmail
                    $mail->SMTPSecure = 'tls';
                    $mail->Port       = 587;

                    $mail->setFrom('suongnie2k5@gmail.com', 'TheGioiNongSan');

                    // Gửi email tới admin
                    $mail->addAddress('admin@yourdomain.com', 'Admin');
                    $mail->isHTML(true);
                    $mail->Subject = "Liên hệ từ $name";
                    $mail->Body    = "<b>Họ tên:</b> $name<br>
                                      <b>Email:</b> $email<br>
                                      <b>Nội dung:</b><br>$message";
                    $mail->send();

                    // Gửi email xác nhận tới khách hàng
                    $mail->clearAddresses();
                    $mail->addAddress($email, $name);
                    $mail->Subject = "Xác nhận liên hệ từ TheGioiNongSan";
                    $mail->Body    = "Chào $name,<br><br>
                                      Cảm ơn bạn đã liên hệ với chúng tôi.<br>
                                      Chúng tôi đã nhận được nội dung của bạn và sẽ trả lời sớm nhất.<br><br>
                                      <b>Nội dung bạn gửi:</b><br>$message<br><br>
                                      Trân trọng,<br>TheGioiNongSan";

                    $mail->send();

                    $success = "Cảm ơn bạn! Chúng tôi đã nhận được liên hệ, email đã gửi tới admin và email xác nhận đã gửi tới bạn.";
                } catch (Exception $e) {
                    $error = "Liên hệ đã lưu nhưng không thể gửi email. Lỗi: {$mail->ErrorInfo}";
                }
            } else {
                $error = "Có lỗi xảy ra. Vui lòng thử lại.";
            }
        }
    }
}

?>
