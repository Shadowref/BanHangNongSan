<?php
// session_start();
if (!isset($_SESSION['username'])) {
    die("<p style='text-align:center;'>Bạn cần đăng nhập trước!</p>");
}

$conn = new mysqli("localhost", "root", "", "banhangonline");
$conn->set_charset("utf8");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

$user = $_SESSION['username'];

// --- Upload avatar ---
$avatar_err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
    $hoten = $_POST['hoten'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $diachi = $_POST['diachi'] ?? '';

    // Xử lý ảnh nếu có
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $avatar_err = "Chỉ cho phép file ảnh JPG, PNG, GIF.";
        } else {
            $newName = 'uploads/'.time().'_'.basename($_FILES['avatar']['name']);
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $newName)) {
                $stmt = $conn->prepare("UPDATE taikhoan SET avatar=? WHERE username=?");
                $stmt->bind_param("ss", $newName, $user);
                $stmt->execute();
            } else {
                $avatar_err = "Upload ảnh thất bại.";
            }
        }
    }

    $stmt = $conn->prepare("UPDATE taikhoan SET username=?, email=?, phone=?, diachi=? WHERE username=?");
    $stmt->bind_param("sssss", $hoten, $email, $phone, $diachi, $user);
    if($stmt->execute()){
        echo "<p style='color:green;text-align:center;'>Cập nhật thông tin thành công!</p> <br></br>";
        $_SESSION['username'] = $hoten; // cập nhật session nếu username thay đổi
    } else {
        echo "<p style='color:red;text-align:center;'>Cập nhật thất bại!</p>";
    }

    if($avatar_err) echo "<p style='color:red;text-align:center;'>$avatar_err</p>";
}

// --- Lấy thông tin người dùng ---
$stmt = $conn->prepare("SELECT * FROM taikhoan WHERE username=?");
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $avatar = $row['avatar'] ?? 'uploads/default.png'; // avatar mặc định
?>
<div style="max-width:1000px;margin:50px auto;padding:30px;background:#f8f9fa;border-radius:15px;box-shadow:0 8px 20px rgba(0,0,0,0.1);font-family:Arial, sans-serif;width: 500px;">
    <h2 style="text-align:center;color:#27ae60;margin-bottom:25px;">Thông tin cá nhân</h2>
    <form method="post" enctype="multipart/form-data">
        <div style="text-align:center;margin-bottom:20px;">
            <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:2px solid #27ae60;">
        </div>
        <p><b>Thay ảnh đại diện:</b> <input type="file" name="avatar" accept="image/*"></p>
        <p><b>Họ tên:</b> <input type="text" name="hoten" value="<?php echo htmlspecialchars($row['username']); ?>" required style="width:100%;padding:10px;margin:5px 0;border-radius:8px;border:1px solid #ccc;"></p>
        <p><b>Email:</b> <input type="email" name="email" value="<?php echo htmlspecialchars($row['email'] ?? ''); ?>" required style="width:100%;padding:10px;margin:5px 0;border-radius:8px;border:1px solid #ccc;"></p>
        <p><b>Số điện thoại:</b> <input type="text" name="phone" value="<?php echo htmlspecialchars($row['phone'] ?? ''); ?>" style="width:100%;padding:10px;margin:5px 0;border-radius:8px;border:1px solid #ccc;"></p>
        <p><b>Địa chỉ:</b> <input type="text" name="diachi" value="<?php echo htmlspecialchars($row['diachi'] ?? ''); ?>" style="width:100%;padding:10px;margin:5px 0;border-radius:8px;border:1px solid #ccc;"></p>
        <p style="text-align:center;">
            <button type="submit" name="update_info" style="padding:12px 25px;background:#27ae60;color:#fff;border:none;border-radius:10px;cursor:pointer;font-weight:600;transition:all 0.3s;">Cập nhật</button>
        </p>
    </form>
</div>
<?php
} else {
    die("<p style='text-align:center;color:red;'>Không tìm thấy thông tin!</p>");
}
?>
