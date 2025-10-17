<?php
$conn = new mysqli("localhost", "root", "", "banhangonline");
$conn->set_charset("utf8");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

// ====== Thêm/Sửa người dùng ======
if (isset($_POST['add']) || isset($_POST['edit'])) {
    $username   = $conn->real_escape_string($_POST['username']);
    $email      = $conn->real_escape_string($_POST['email']);
    $diachi     = $conn->real_escape_string($_POST['diachi']);
    $password   = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : "";
    $phone      = $conn->real_escape_string($_POST['phone']);
    $role       = $_POST['role'];
    $diem       = intval($_POST['diem']);
    $avatar     = !empty($_POST['avatar']) ? $conn->real_escape_string($_POST['avatar']) : "";

    if(isset($_POST['add'])) {
        $sql = "INSERT INTO taikhoan (username,email,diachi,password,phone,role,diem,avatar) 
                VALUES ('$username','$email','$diachi','$password','$phone','$role',$diem,'$avatar')";
    } else { 
        $stt = intval($_POST['stt']);
        $set = "username='$username', email='$email', diachi='$diachi', phone='$phone', role='$role', diem=$diem, avatar='$avatar'";
        if($password) $set .= ", password='$password'";
        $sql = "UPDATE taikhoan SET $set WHERE id=$stt";
    }
    $conn->query($sql) or die($conn->error);
    header("Location: index.php?admin=nguoidung");
    exit;
}

// ====== Xóa người dùng ======
if(isset($_GET['delete'])){
    $stt = intval($_GET['delete']);
    $conn->query("DELETE FROM taikhoan WHERE id=$stt") or die($conn->error);
    header("Location: index.php?admin=nguoidung");
    exit;
}

// ====== Tìm kiếm người dùng ======
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
if ($search != "") {
    $safeSearch = $conn->real_escape_string($search);
    $sql = "SELECT * FROM taikhoan 
            WHERE username LIKE '%$safeSearch%' OR email LIKE '%$safeSearch%' 
            ORDER BY id DESC";
} else {
    $sql = "SELECT * FROM taikhoan ORDER BY id DESC";
}
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý Người dùng</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.container { margin: 40px auto; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
h2 { text-align: center; margin-bottom: 25px; color: #2c3e50; font-weight: 700; }

form { margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
form input, form select { padding: 8px; border-radius: 6px; border: 1px solid #ccc; }
form button { padding: 8px 15px; border: none; border-radius: 6px; cursor: pointer; color:#fff; transition: 0.3s; }
form button:hover { opacity:0.9; }

table { width: 100%; border-collapse: collapse; }
table th, table td { border: 1px solid #ddd; padding: 8px; text-align: center; vertical-align: middle; }
table th { background: #27ae60; color: #fff; text-transform: uppercase; }
table tr:nth-child(even) { background: #f9f9f9; }
table tr:hover { background: #eafaf1; }

a.btn { text-decoration: none; color: #fff; padding: 5px 10px; border-radius: 5px; }
a.btn-edit { background: #f39c12; }
a.btn-edit:hover { background: #d35400; }
a.btn-delete { background: #e74c3c; }
a.btn-delete:hover { background: #c0392b; }

img.avatar-img { width:50px; border-radius:50%; }
.search-box { text-align:center; margin-bottom:20px; }
</style>
</head>
<body>
<div class="container">
<h2>Quản lý Người dùng</h2>

<!-- Form Thêm/Sửa -->
<form method="post">
    <input type="hidden" name="stt" id="stt">
    <input type="text" name="username" id="username" placeholder="Username" required>
    <input type="email" name="email" id="email" placeholder="Email" required>
    <input type="text" name="diachi" id="diachi" placeholder="Địa chỉ">
    <input type="password" name="password" id="password" placeholder="Mật khẩu">
    <input type="text" name="phone" id="phone" placeholder="Số điện thoại">
    <select name="role" id="role">
        <option value="user">User</option>
        <option value="admin">Admin</option>
    </select>
    <input type="number" name="diem" id="diem" value="0">
    <input type="text" name="avatar" id="avatar" placeholder="Link avatar (tùy chọn)">
    <button type="submit" name="add" id="submitBtn" class="btn btn-success">➕ Thêm người dùng</button>
</form>

<!-- Thanh tìm kiếm -->
<div class="search-box">
    <form method="get">
        <input type="hidden" name="admin" value="nguoidung">
        <input type="text" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES) ?>" placeholder="🔍 Tìm theo Username hoặc Email" style="padding:6px;width:300px;">
        <button type="submit" class="btn btn-primary">Tìm kiếm</button>
    </form>
</div>

<!-- Bảng người dùng -->
<table>
<thead>
<tr>
    <th>STT</th>
    <th>Username</th>
    <th>Email</th>
    <th>Địa chỉ</th>
    <th>Phone</th>
    <th>Role</th>
    <th>Điểm</th>
    <th>Avatar</th>
    <th>Ngày tạo</th>
    <th>Hành động</th>
</tr>
</thead>
<tbody>
<?php
if($result->num_rows > 0){
    $stt = 1;
   while($row = $result->fetch_assoc()){
    $avatar = $row['avatar'] ? $row['avatar'] : "";
    
    if ($avatar) {
        if (preg_match('/^https?:\/\//', $avatar)) {
            $avatarPath = $avatar;
        } else {
            $avatarPath = "../" . ltrim($avatar, "/");
            if (!file_exists($_SERVER['DOCUMENT_ROOT']."/banhangonline/".ltrim($avatar, "/"))) {
                $avatarPath = "img/avatar.png";
            }
        }
    } else {
        $avatarPath = "img/avatar.png";
    }

    echo "<tr>
        <td>".$stt++."</td>
        <td>".htmlspecialchars($row['username'])."</td>
        <td>".htmlspecialchars($row['email'])."</td>
        <td>".htmlspecialchars($row['diachi'])."</td>
        <td>".htmlspecialchars($row['phone'])."</td>
        <td>".htmlspecialchars($row['role'])."</td>
        <td>".$row['diem']."</td>
        <td><img src='".$avatarPath."' class='avatar-img'></td>
        <td>".$row['created_at']."</td>
        <td>
            <a href='#' class='btn btn-edit btn-sm' onclick=\"editRow(
                '".$row['id']."',
                '".htmlspecialchars($row['username'], ENT_QUOTES)."',
                '".htmlspecialchars($row['email'], ENT_QUOTES)."',
                '".htmlspecialchars($row['diachi'], ENT_QUOTES)."',
                '',
                '".htmlspecialchars($row['phone'], ENT_QUOTES)."',
                '".$row['role']."',
                '".$row['diem']."',
                '".htmlspecialchars($row['avatar'], ENT_QUOTES)."'
            )\">Sửa</a>
            <a href='index.php?admin=nguoidung&delete=".$row['id']."' class='btn btn-delete btn-sm' onclick=\"return confirm('Xóa người dùng này?')\">Xóa</a>
        </td>
    </tr>";
}

}else{
    echo "<tr><td colspan='10'>Không tìm thấy người dùng</td></tr>";
}
?>
</tbody>
</table>
</div>

<script>
function editRow(id, username, email, diachi, password, phone, role, diem, avatar){
    document.getElementById('stt').value = id;
    document.getElementById('username').value = username;
    document.getElementById('email').value = email;
    document.getElementById('diachi').value = diachi;
    document.getElementById('password').value = '';
    document.getElementById('phone').value = phone;
    document.getElementById('role').value = role;
    document.getElementById('diem').value = diem;
    document.getElementById('avatar').value = avatar;

    let btn = document.getElementById('submitBtn');
    btn.innerText = "💾 Cập nhật";
    btn.name = "edit";
    btn.classList.remove('btn-success');
    btn.classList.add('btn-primary');
}
</script>
</body>
</html>
