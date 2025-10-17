<?php

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "banhangonline");
    if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        // ✅ Lấy thêm role
        $stmt = $conn->prepare("SELECT id, username, password, role FROM taikhoan WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // ✅ Lưu thông tin user vào session
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['role']      = $user['role']; // lưu role (admin/user)

                header("Location: index.php"); // chuyển về trang chủ
                exit;
            } else {
                $message = "❌ Mật khẩu không đúng";
            }
        } else {
            $message = "❌ Tên đăng nhập không tồn tại";
        }
        $stmt->close();
    } else {
        $message = "⚠️ Vui lòng nhập đầy đủ thông tin";
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đăng nhập</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Roboto', sans-serif; }
    body {
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        /* background: linear-gradient(135deg, #74ebd5, #ACB6E5); */
        color: #333;
    }
    .login-container {
        display: none; /* Ẩn ban đầu, chỉ hiện khi bấm icon */
        background: #fff;
        padding: 40px 30px;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.25);
        width: 360px;
        text-align: center;
        animation: fadeIn 0.5s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px);}
        to   { opacity: 1; transform: translateY(0);}
    }
    h2 { margin-bottom: 25px; color: #333; }
    .message { color: red; margin-bottom: 15px; font-size:14px; }
    .input-field {
        position: relative;
        margin: 15px 0;
    }
    .input-field input {
        width: 100%;
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
        transition: 0.3s;
    }
    .input-field input:focus {
        border-color: #28a745;
        box-shadow: 0 0 8px rgba(40,167,69,0.3);
        outline: none;
    }
    input[type="submit"] {
        width: 100%;
        padding: 12px;
        margin-top: 10px;
        background: linear-gradient(135deg, #28a745, #20c997);
        color: #fff;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    input[type="submit"]:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.2);
    }
    .social-login {
        margin-top: 20px;
        display: flex;
        justify-content: center;
        gap: 15px;
    }
    .social-login button {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s;
    }
    .social-login button img { width: 50px; }
    .social-login button:hover { transform: scale(1.15); }
    .google { background: #ffffffff; }
    .facebook { background: #4267B2; }
    .login-image {
        cursor: pointer;
        width: 120px;
        transition: transform 0.3s;
    }
    .login-image:hover { transform: scale(1.1); }
    .extra-links {
        margin-top: 15px;
        font-size: 14px;
    }
    .extra-links a {
        color: #007bff;
        text-decoration: none;
    }
    .extra-links a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>

<!-- Icon bấm để hiện form -->
<img src="./img/login.png" alt="Đăng nhập" class="login-image" onclick="showLoginForm()">

<!-- Form đăng nhập -->
<div class="login-container" id="loginForm">
    <h2>Đăng nhập</h2>
    <?php if($message) echo "<div class='message'>$message</div>"; ?>
    <form method="POST" action="">
        <div class="input-field">
            <input type="text" name="username" placeholder="Tên đăng nhập" required>
        </div>
        <div class="input-field">
            <input type="password" name="password" placeholder="Mật khẩu" required>
        </div>
        <input type="submit" value="Đăng nhập">
    </form>

    <div class="social-login">
        <button class="google" onclick="window.location.href='google-login.php'">
            <img src="./img/google.png" alt="">
        </button>
        <button class="facebook" onclick="window.location.href='zalo_login.php'">
            <img src="./img/facebook-icon.png" alt="">
        </button>
    </div>

    <div class="extra-links">
        <p><a href="quenmk.php">Quên mật khẩu?</a></p>
        <p>Chưa có tài khoản? <a href="index.php?content=dangky">Đăng ký ngay</a></p>
    </div>
</div>

<script>
function showLoginForm() {
    document.getElementById('loginForm').style.display = 'block';
    window.scrollTo({ top: document.getElementById('loginForm').offsetTop, behavior: 'smooth' });
}
</script>

</body>
</html>
