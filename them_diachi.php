<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "banhangonline";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);
$conn->set_charset("utf8");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diachi = trim($_POST['diachi'] ?? '');
    $tennguoinhan = trim($_POST['tennguoinhan'] ?? '');
    $sdt = trim($_POST['sdt'] ?? '');
    $macdinh = isset($_POST['macdinh']) ? 1 : 0;
    $username = $_SESSION['username'];

    if ($diachi === '' || $tennguoinhan === '' || $sdt === '') {
        $error = "Vui lòng điền đầy đủ thông tin.";
    } else {
        // Nếu chọn là mặc định, reset các địa chỉ khác
        if ($macdinh) {
            $conn->query("UPDATE diachi SET macdinh=0 WHERE username='$username'");
        }

        $stmt = $conn->prepare("INSERT INTO diachi (username, diachi, tennguoinhan, sdt, macdinh) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $username, $diachi, $tennguoinhan, $sdt, $macdinh);
        if ($stmt->execute()) {
            $success = "Thêm địa chỉ thành công!";
        } else {
            $error = "Có lỗi xảy ra, thử lại sau.";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Thêm địa chỉ giao hàng</title>
<style>
body { font-family: Arial, sans-serif; background:#f9f9f9; padding:20px; }
.container { max-width:500px; margin:50px auto; background:#fff; padding:20px; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.1); }
h2 { text-align:center; margin-bottom:20px; }
label { display:block; margin-top:10px; font-weight:bold; }
input[type=text], input[type=tel] { width:100%; padding:8px; margin-top:5px; border-radius:4px; border:1px solid #ccc; }
input[type=checkbox] { margin-top:10px; }
button { margin-top:15px; padding:10px 20px; border:none; border-radius:5px; background:#28a745; color:white; cursor:pointer; }
button:hover { opacity:0.9; }
.message { margin-top:15px; padding:10px; border-radius:5px; }
.error { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
.success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
</style>
</head>
<body>

<div class="container">
    <h2>Thêm địa chỉ giao hàng</h2>

    <?php if ($error): ?>
        <div class="message error"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="message success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="tennguoinhan">Tên người nhận:</label>
        <input type="text" name="tennguoinhan" id="tennguoinhan" required>

        <label for="sdt">Số điện thoại:</label>
        <input type="tel" name="sdt" id="sdt" required pattern="[0-9]{9,12}" title="Nhập số điện thoại hợp lệ">

        <label for="diachi">Địa chỉ:</label>
        <input type="text" name="diachi" id="diachi" required>

        <label><input type="checkbox" name="macdinh" value="1"> Chọn làm địa chỉ mặc định</label>

        <button type="submit">Thêm địa chỉ</button>
    </form>
</div>

</body>
</html>
