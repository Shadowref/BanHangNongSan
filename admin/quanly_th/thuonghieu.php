<?php
$conn = new mysqli("localhost", "root", "", "banhangonline");
$conn->set_charset("utf8");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

// ===== Thêm/Sửa thương hiệu =====
if(isset($_POST['add']) || isset($_POST['edit'])){
    $tenthuonghieu = $conn->real_escape_string($_POST['tenthuonghieu']);
    
    if(isset($_POST['add'])){
        $sql = "INSERT INTO thuonghieu (tenthuonghieu) VALUES ('$tenthuonghieu')";
        $conn->query($sql) or die($conn->error);

        // Thông báo
        $msg = "Thêm thương hiệu mới: '$tenthuonghieu'";
        $conn->query("INSERT INTO thongbao (user_id, message) VALUES (1, '".$conn->real_escape_string($msg)."')");

    } else { // edit
        $stt = intval($_POST['stt']);
        // Lấy tên cũ
        $old = $conn->query("SELECT tenthuonghieu FROM thuonghieu WHERE id=$stt")->fetch_assoc();
        $oldName = $old ? $old['tenthuonghieu'] : '';

        $sql = "UPDATE thuonghieu SET tenthuonghieu='$tenthuonghieu' WHERE id=$stt";
        $conn->query($sql) or die($conn->error);

        // Thông báo
        if ($oldName && $oldName != $tenthuonghieu) {
            $msg = "Đã đổi tên thương hiệu từ '$oldName' thành '$tenthuonghieu'";
        } else {
            $msg = "Cập nhật thương hiệu: '$tenthuonghieu'";
        }
        $conn->query("INSERT INTO thongbao (user_id, message) VALUES (1, '".$conn->real_escape_string($msg)."')");
    }

    header("Location: http://localhost/banhangonline/admin/index.php?admin=thuonghieu");
    exit;
}

// ===== Xóa thương hiệu =====
if(isset($_GET['delete']) && isset($_GET['admin']) && $_GET['admin']=='thuonghieu'){
    $stt = intval($_GET['delete']);

    // Lấy tên cũ
    $old = $conn->query("SELECT tenthuonghieu FROM thuonghieu WHERE id=$stt")->fetch_assoc();
    $oldName = $old ? $old['tenthuonghieu'] : '';

    $conn->query("DELETE FROM thuonghieu WHERE id=$stt") or die($conn->error);

    // Thông báo
    if ($oldName) {
        $msg = "Đã xóa thương hiệu: '$oldName'";
        $conn->query("INSERT INTO thongbao (user_id, message) VALUES (1, '".$conn->real_escape_string($msg)."')");
    }

    header("Location: index.php?admin=thuonghieu");
    exit;
}

// ===== Lọc tìm kiếm =====
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = "";
if ($search !== "") {
    $safe = $conn->real_escape_string($search);
    $where = "WHERE tenthuonghieu LIKE '%$safe%'";
}

// ===== Lấy danh sách thương hiệu =====
$sql = "SELECT * FROM thuonghieu $where ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý Thương hiệu</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.container { margin: 40px auto; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
h2 { text-align: center; margin-bottom: 25px; color: #2c3e50; font-weight: 700; }

form { margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
form input { padding: 8px; border-radius: 6px; border: 1px solid #ccc; flex: 1; }
form button { padding: 8px 15px; border: none; border-radius: 6px; cursor: pointer; color:#fff; transition: 0.3s; }
form button:hover { opacity:0.9; }

table { width: 100%; border-collapse: collapse; margin-top: 20px; }
table th, table td { border: 1px solid #ddd; padding: 8px; text-align: center; vertical-align: middle; }
table th { background: #27ae60; color: #fff; text-transform: uppercase; }
table tr:nth-child(even) { background: #f9f9f9; }
table tr:hover { background: #eafaf1; }

a.btn { text-decoration: none; color: #fff; padding: 5px 10px; border-radius: 5px; }
a.btn-edit { background: #f39c12; }
a.btn-edit:hover { background: #d35400; }
a.btn-delete { background: #e74c3c; }
a.btn-delete:hover { background: #c0392b; }
.search-box { margin-bottom: 20px; text-align: center; }
.search-box input { width: 300px; display:inline-block; }
</style>
</head>
<body>
<div class="container">
<h2>Quản lý Thương hiệu</h2>

<!-- Form Thêm/Sửa -->
<form method="post">
    <input type="hidden" name="stt" id="stt">
    <input type="text" name="tenthuonghieu" id="tenthuonghieu" placeholder="Tên thương hiệu" required>
    <button type="submit" name="add" id="submitBtn" class="btn btn-success">➕ Thêm thương hiệu</button>
</form>

<!-- Thanh tìm kiếm -->
<div class="search-box">
    <form method="get" action="">
        <input type="hidden" name="admin" value="thuonghieu">
        <input type="text" name="search" placeholder="🔍 Tìm kiếm thương hiệu..." value="<?= htmlspecialchars($search,ENT_QUOTES) ?>">
        <button type="submit" class="btn btn-primary">Tìm</button>
    </form>
</div>

<!-- Bảng thương hiệu -->
<table>
<thead>
<tr>
    <th>STT</th>
    <th>Tên thương hiệu</th>
    <th>Hành động</th>
</tr>
</thead>
<tbody>
<?php
if($result->num_rows > 0){
    $stt = 1;
    while($row = $result->fetch_assoc()){
        echo "<tr>
            <td>".$stt++."</td>
            <td>".htmlspecialchars($row['tenthuonghieu'])."</td>
            <td>
                <a href='#' class='btn btn-edit btn-sm' onclick=\"editRow('".$row['id']."','".htmlspecialchars($row['tenthuonghieu'],ENT_QUOTES)."')\">Sửa</a>
                <a href='index.php?admin=thuonghieu&delete=".$row['id']."' class='btn btn-delete btn-sm' onclick=\"return confirm('Xóa thương hiệu này?')\">Xóa</a>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='3'>Không tìm thấy thương hiệu</td></tr>";
}
?>
</tbody>
</table>
</div>

<script>
function editRow(id, tenthuonghieu){
    document.getElementById('stt').value = id;
    document.getElementById('tenthuonghieu').value = tenthuonghieu;

    let btn = document.getElementById('submitBtn');
    btn.innerText = "💾 Cập nhật";
    btn.name = "edit";
    btn.classList.remove('btn-success');
    btn.classList.add('btn-primary');
}
</script>
</body>
</html>
