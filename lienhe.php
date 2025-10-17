<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$success = null;
$error = null;

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    die("<p style='text-align:center; color:red; font-weight:bold;'>Bạn phải đăng nhập mới có thể liên hệ. <a href='index.php'>Đăng nhập tại đây</a></p>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        $error = "Vui lòng điền đầy đủ thông tin.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ.";
    } else {
        $conn = new mysqli("localhost", "root", "", "banhangonline");
        $conn->set_charset("utf8");
        if ($conn->connect_error) {
            $error = "Không thể kết nối CSDL: " . $conn->connect_error;
        } else {
            $stmt = $conn->prepare("INSERT INTO lienhe(hoten, email, noidung) VALUES (?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sss", $name, $email, $message);
                if ($stmt->execute()) {
                    $stmt->close();
                    $conn->close();

                    try {
                        $mail = new PHPMailer(true);
                        $mail->CharSet = 'UTF-8';
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'suongnie2k5@gmail.com';
                        $mail->Password   = 'heqwfsjnixruxjtw';
                        $mail->SMTPSecure = 'tls';
                        $mail->Port       = 587;

                        $mail->setFrom('suongnie2k5@gmail.com', 'TheGioiNongSan');
                        $mail->AddEmbeddedImage('img/logochinh.jpg', 'logo_cid');

                        // Gửi tới Admin
                        $mail->addAddress('suongnie2k5@gmail.com', 'Admin');
                        $mail->isHTML(true);
                        $mail->Subject = "Liên hệ mới từ $name";
                        $mail->Body = "
                        <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto; border:1px solid #e0e0e0; border-radius:15px; overflow:hidden;'>
                            <div style='background: linear-gradient(135deg, #28a745, #85e085); padding:20px; text-align:center; color:white;'>
                                <img src='cid:logo_cid' alt='Logo' style='width:80px; margin-bottom:10px;'>
                                <h2>Liên hệ mới từ website</h2>
                            </div>
                            <div style='padding:20px; background:#f9f9f9; color:#333;'>
                                <p><strong>Họ tên:</strong> $name</p>
                                <p><strong>Email:</strong> $email</p>
                                <p><strong>Nội dung:</strong></p>
                                <div style='padding:15px; background:#eafaf1; border-radius:10px;'>$message</div>
                                <hr style='margin:20px 0; border:none; border-top:1px solid #ccc;'>
                                <p style='font-size:12px; color:#666;'>Đây là email tự động từ TheGioiNongSan. Vui lòng không trả lời trực tiếp.</p>
                            </div>
                        </div>
                        ";
                        $mail->send();

                        // Gửi xác nhận tới khách
                        $mail->clearAddresses();
                        $mail->addAddress($email, $name);
                        $mail->Subject = "Xác nhận liên hệ từ TheGioiNongSan";
                        $mail->Body = "
                        <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto; border:1px solid #e0e0e0; border-radius:15px; overflow:hidden;'>
                            <div style='background: linear-gradient(135deg, #28a745, #a8e063); padding:20px; text-align:center; color:white;'>
                                <img src='cid:logo_cid' alt='Logo' style='width:80px; margin-bottom:10px;'>
                                <h2>Cảm ơn bạn, $name!</h2>
                            </div>
                            <div style='padding:20px; background:#f9f9f9; color:#333;'>
                                <p>Chúng tôi đã nhận được nội dung liên hệ của bạn và sẽ phản hồi sớm nhất.</p>
                                <p><strong>Nội dung bạn gửi:</strong></p>
                                <div style='padding:15px; background:#eafaf1; border-radius:10px;'>$message</div>
                                <hr style='margin:20px 0; border:none; border-top:1px solid #ccc;'>
                                <p style='font-size:12px; color:#666;'>TheGioiNongSan</p>
                            </div>
                        </div>
                        ";
                        $mail->send();
                        $success = "Cảm ơn bạn! Email đã gửi tới admin và email xác nhận đã gửi tới bạn.";

                    } catch (Exception $e) {
                        $error = "Liên hệ đã lưu nhưng không thể gửi email. Lỗi PHPMailer: " . $e->getMessage();
                    }

                } else {
                    $error = "Không thể lưu liên hệ (có thể chưa tạo bảng 'lienhe').";
                }
            } else {
                $error = "Có lỗi xảy ra khi chuẩn bị truy vấn: " . $conn->error;
            }
        }
    }
}
?>


<!-- HTML Form -->
<li style="list-style:none; width:100%; display:flex; justify-content:center; padding:20px;">
  <div class="contact-card">
    <!-- Logo -->
    <div style="text-align:center; margin-bottom:20px;">
      <img src="img/logochinh.jpg" alt="Logo TheGioiNongSan" style="width:100px; height:auto;">
    </div>

    <h2>Liên hệ với chúng tôi</h2>

    <?php if ($success): ?>
      <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="">
      <label>Họ tên</label>
      <input type="text" name="name" required placeholder="Nhập họ tên của bạn">

      <label>Email</label>
      <input type="email" name="email" required placeholder="Nhập email của bạn">

      <label>Nội dung</label>
      <textarea name="message" rows="6" required placeholder="Viết nội dung liên hệ"></textarea>

      <button type="submit">Gửi liên hệ</button>
    </form>

    <!-- Social links -->
    <div class="social-links">
      <a href="https://web.facebook.com/nie.suong.24/" target="_blank" class="social-icon fb"></a>
      <a href="https://www.tiktok.com/@suong4705" target="_blank" class="social-icon tt"></a>
      <a href="https://www.youtube.com/@syn4705" target="_blank" class="social-icon yt"></a>
    </div>
  </div>
</li>

<style>
.contact-card {
  max-width: 700px;
  width: 100%;
  background: linear-gradient(135deg, #56ab2f, #a8e063);
  padding: 40px 35px;
  border-radius: 25px;
  box-shadow: 0 20px 50px rgba(0,0,0,0.25);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #fff;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.contact-card:hover {
  transform: translateY(-7px);
  box-shadow: 0 25px 60px rgba(0,0,0,0.3);
}

.contact-card h2 {
  text-align: center;
  margin-bottom: 25px;
  font-size: 32px;
  text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
}

.alert {
  padding: 15px;
  border-radius: 12px;
  margin-bottom: 20px;
  font-weight: 600;
  text-align: center;
  color: #fff;
  font-size: 16px;
}

.alert.success {
  background: linear-gradient(90deg, #28a745, #85e085);
}

.alert.error {
  background: linear-gradient(90deg, #dc3545, #ff7b7b);
}

.contact-card label {
  display: block;
  margin: 12px 0 6px;
  font-weight: 600;
  color: #fff;
}

.contact-card input,
.contact-card textarea {
  width: 100%;
  padding: 16px;
  border: none;
  border-radius: 15px;
  font-size: 16px;
  margin-bottom: 15px;
  box-shadow: inset 0 4px 8px rgba(0,0,0,0.1);
  transition: all 0.3s ease;
}

.contact-card input:focus,
.contact-card textarea:focus {
  outline: none;
  box-shadow: 0 0 20px rgba(144,238,144,0.6);
}

.contact-card button {
  width: 100%;
  padding: 16px;
  background: linear-gradient(45deg, #28a745, #a8e063);
  border: none;
  border-radius: 20px;
  font-size: 18px;
  color: #fff;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
}

.contact-card button:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(0,128,0,0.5);
}

/* Social links */
.social-links {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-top: 25px;
}

.social-icon {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  display: inline-block;
  transition: all 0.3s ease;
  background-size: cover;
}

.social-icon.fb { background: url('img/facebook.png') no-repeat center/contain; }
.social-icon.tt { background: url('img/tiktok.png') no-repeat center/contain; }
.social-icon.yt { background: url('img/youtube.png') no-repeat center/contain; }

.social-icon:hover {
  transform: scale(1.3) rotate(-10deg);
  box-shadow: 0 5px 20px rgba(0,128,0,0.5);
}
</style>
