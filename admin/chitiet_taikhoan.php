<?php

if (!isset($_SESSION['username'])) {
    die("<p style='text-align:center;'>Bạn cần đăng nhập trước!</p>");
}

$conn = new mysqli("localhost", "root", "", "banhangonline");
$conn->set_charset("utf8");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

$user = $_SESSION['username'];

// --- Lấy thông tin người dùng ---
$stmt = $conn->prepare("SELECT * FROM taikhoan WHERE username=?");
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Lấy avatar từ DB, nếu rỗng thì dùng default
    $avatar_file = !empty($row['avatar']) ? $row['avatar'] : "uploads/default.png";

    // Nếu avatar không có hoặc file không tồn tại trên server thì dùng default
    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/banhangonline/' . $avatar_file)) {
        $avatar_file = 'uploads/default.png';
    }

    // Đường dẫn web tới avatar
    $avatar_url = '/banhangonline/' . ltrim($avatar_file, '/');
?>
<div style="width:100%; max-width:600px; margin:50px auto; padding:30px; background:#f8f9fa; border-radius:15px; box-shadow:0 8px 20px rgba(0,0,0,0.1); font-family:Arial, sans-serif; ">
    <h2 style="text-align:center; color:#27ae60; margin-bottom:25px;">Thông tin cá nhân</h2>
    <div style="text-align:center; margin-bottom:20px;">
        <img src="<?php echo htmlspecialchars($avatar_url); ?>" 
             alt="Avatar" 
             style="width:40%; max-width:120px; height:auto; border-radius:50%; object-fit:cover; border:2px solid #27ae60;">
    </div>
    <table style="width:100%; border-collapse: collapse;">
        <tr>
            <th style="text-align:left; padding:8px; width:30%;">Họ tên</th>
            <td style="padding:8px;"><?php echo htmlspecialchars($row['username']); ?></td>
        </tr>
        <tr>
            <th style="text-align:left; padding:8px;">Email</th>
            <td style="padding:8px;"><?php echo htmlspecialchars($row['email'] ?? ''); ?></td>
        </tr>
        <tr>
            <th style="text-align:left; padding:8px;">Số điện thoại</th>
            <td style="padding:8px;"><?php echo htmlspecialchars($row['phone'] ?? ''); ?></td>
        </tr>
        <tr>
            <th style="text-align:left; padding:8px;">Địa chỉ</th>
            <td style="padding:8px;"><?php echo htmlspecialchars($row['diachi'] ?? ''); ?></td>
        </tr>
        <tr>
            <th style="text-align:left; padding:8px;">Ngày tạo</th>
            <td style="padding:8px;"><?php echo $row['created_at']; ?></td>
        </tr>
    </table>
    <div style="text-align:center; margin-top:20px;">
        <a href="?admin=trangchu" style="padding:8px 15px; background:#3498db; color:#fff; border-radius:6px; text-decoration:none;">Quay lại</a>
    </div>
</div>

<?php
} else {
    die("<p style='text-align:center;color:red;'>Không tìm thấy thông tin!</p>");
}
$conn->close();
?>
