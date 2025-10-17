<?php
$conn = new mysqli("localhost", "root", "", "banhangonline");
$conn->set_charset("utf8");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

// Lấy danh sách sản phẩm để chọn trong khuyến mãi
$products = $conn->query("SELECT id, tensp FROM sanpham ORDER BY tensp ASC");

// ===== Thêm/Sửa khuyến mại =====
if(isset($_POST['add']) || isset($_POST['edit'])){
    $sanpham_id = intval($_POST['sanpham_id']);
    $giakhuyenmai = $_POST['giakhuyenmai'] ? floatval($_POST['giakhuyenmai']) : NULL;
    $giamgia = $_POST['giamgia'] ? intval($_POST['giamgia']) : NULL;
    $ngay_bat_dau = $_POST['ngay_bat_dau'] ?: NULL;
    $ngay_ket_thuc = $_POST['ngay_ket_thuc'] ?: NULL;

    // Lấy tên sản phẩm
    $sp = $conn->query("SELECT tensp FROM sanpham WHERE id=$sanpham_id")->fetch_assoc();
    $tensp = $sp ? $sp['tensp'] : "Sản phẩm #$sanpham_id";

    if(isset($_POST['add'])){
        $sql = "INSERT INTO khuyenmai (sanpham_id, giakhuyenmai, giamgia, ngay_bat_dau, ngay_ket_thuc) 
                VALUES ($sanpham_id, ".($giakhuyenmai!==NULL?$giakhuyenmai:"NULL").", ".($giamgia!==NULL?$giamgia:"NULL").", ".
                ($ngay_bat_dau?"'".$conn->real_escape_string($ngay_bat_dau)."'":"NULL").", ".
                ($ngay_ket_thuc?"'".$conn->real_escape_string($ngay_ket_thuc)."'":"NULL").")";
        $conn->query($sql) or die($conn->error);

        // Ghi thông báo
        $msg = "Thêm khuyến mại cho sản phẩm '$tensp' | Giá KM: ".($giakhuyenmai?number_format($giakhuyenmai)."đ":"N/A")." | Giảm: ".($giamgia?$giamgia."%":"0%")." | Từ ".($ngay_bat_dau?:'?')." đến ".($ngay_ket_thuc?:'?');
        $conn->query("INSERT INTO thongbao (user_id, message) VALUES (1, '".$conn->real_escape_string($msg)."')");

    } else {
        $stt = intval($_POST['stt']);
        $sql = "UPDATE khuyenmai SET sanpham_id=$sanpham_id, 
                giakhuyenmai=".($giakhuyenmai!==NULL?$giakhuyenmai:"NULL").", 
                giamgia=".($giamgia!==NULL?$giamgia:"NULL").", 
                ngay_bat_dau=".($ngay_bat_dau?"'".$conn->real_escape_string($ngay_bat_dau)."'":"NULL").", 
                ngay_ket_thuc=".($ngay_ket_thuc?"'".$conn->real_escape_string($ngay_ket_thuc)."'":"NULL")." 
                WHERE id=$stt";
        $conn->query($sql) or die($conn->error);

        // Ghi thông báo
        $msg = "Cập nhật khuyến mại sản phẩm '$tensp' | Giá KM: ".($giakhuyenmai?number_format($giakhuyenmai)."đ":"N/A")." | Giảm: ".($giamgia?$giamgia."%":"0%")." | Từ ".($ngay_bat_dau?:'?')." đến ".($ngay_ket_thuc?:'?');
        $conn->query("INSERT INTO thongbao (user_id, message) VALUES (1, '".$conn->real_escape_string($msg)."')");
    }

    header("Location: http://localhost/banhangonline/admin/index.php?admin=khuyenmai");
    exit;
}

// ===== Xóa khuyến mại =====
if(isset($_GET['delete'])){
    $stt = intval($_GET['delete']);

    // Lấy thông tin trước khi xóa
    $km = $conn->query("SELECT k.*, s.tensp FROM khuyenmai k LEFT JOIN sanpham s ON k.sanpham_id=s.id WHERE k.id=$stt")->fetch_assoc();

    $conn->query("DELETE FROM khuyenmai WHERE id=$stt") or die($conn->error);

    if ($km) {
        $msg = "Xóa khuyến mại sản phẩm '".$km['tensp']."' | Giá KM: ".($km['giakhuyenmai']?number_format($km['giakhuyenmai'])."đ":"N/A")." | Giảm: ".($km['giamgia']?$km['giamgia']."%":"0%");
        $conn->query("INSERT INTO thongbao (user_id, message) VALUES (1, '".$conn->real_escape_string($msg)."')");
    }

    header("Location: index.php?admin=khuyenmai");
    exit;
}


// ===== Lọc tìm kiếm =====
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = "";
if ($search !== "") {
    $safe = $conn->real_escape_string($search);
    $where = "WHERE sanpham.tensp LIKE '%$safe%'";
}

// ===== Lấy danh sách khuyến mại =====
$sql = "SELECT khuyenmai.*, sanpham.tensp 
        FROM khuyenmai 
        LEFT JOIN sanpham ON khuyenmai.sanpham_id = sanpham.id
        $where
        ORDER BY khuyenmai.id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý Khuyến mại</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.container { margin: 40px auto; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
h2 { text-align: center; margin-bottom: 25px; color: #2c3e50; font-weight: 700; }

form { margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
form select, form input { padding: 8px; border-radius: 6px; border: 1px solid #ccc; }
form button { padding: 8px 15px; border: none; border-radius: 6px; cursor: pointer; color:#fff; transition: 0.3s; }
form button:hover { opacity:0.9; }

.search-box { text-align:center; margin:20px 0; }
.search-box input { width:300px; padding:6px; border-radius:6px; border:1px solid #ccc; }

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
</style>
</head>
<body>
<div class="container">
<h2>Quản lý Khuyến mại</h2>

<!-- Form Thêm/Sửa -->
<form method="post">
    <input type="hidden" name="stt" id="stt">
    <select name="sanpham_id" id="sanpham_id" required>
        <option value="">-- Chọn sản phẩm --</option>
        <?php while($p = $products->fetch_assoc()){ ?>
            <option value="<?= $p['id']; ?>"><?= htmlspecialchars($p['tensp']); ?></option>
        <?php } ?>
    </select>
    <input type="number" step="0.01" name="giakhuyenmai" id="giakhuyenmai" placeholder="Giá khuyến mại">
    <input type="number" name="giamgia" id="giamgia" placeholder="Giảm giá (%)">
    <input type="date" name="ngay_bat_dau" id="ngay_bat_dau" placeholder="Ngày bắt đầu">
    <input type="date" name="ngay_ket_thuc" id="ngay_ket_thuc" placeholder="Ngày kết thúc">
    <button type="submit" name="add" id="submitBtn" class="btn btn-success">➕ Thêm khuyến mại</button>
</form>

<!-- Thanh tìm kiếm -->
<div class="search-box">
    <form method="get" action="">
        <input type="hidden" name="admin" value="khuyenmai">
        <input type="text" name="search" placeholder="🔍 Tìm theo tên sản phẩm..." value="<?= htmlspecialchars($search,ENT_QUOTES) ?>">
        <button type="submit" class="btn btn-primary">Tìm kiếm</button>
    </form>
</div>

<!-- Bảng khuyến mại -->
<table>
<thead>
<tr>
    <th>STT</th>
    <th>Sản phẩm</th>
    <th>Giá KM</th>
    <th>Giảm giá (%)</th>
    <th>Ngày bắt đầu</th>
    <th>Ngày kết thúc</th>
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
            <td>".htmlspecialchars($row['tensp'])."</td>
            <td>".($row['giakhuyenmai']!==NULL ? number_format($row['giakhuyenmai'],2) : "")."</td>
            <td>".($row['giamgia']!==NULL ? $row['giamgia'] : "")."</td>
            <td>".$row['ngay_bat_dau']."</td>
            <td>".$row['ngay_ket_thuc']."</td>
            <td>
                <a href='#' class='btn btn-edit btn-sm' onclick=\"editRow(
                    '".$row['id']."',
                    '".$row['sanpham_id']."',
                    '".$row['giakhuyenmai']."',
                    '".$row['giamgia']."',
                    '".$row['ngay_bat_dau']."',
                    '".$row['ngay_ket_thuc']."'
                )\">Sửa</a>
                <a href='index.php?admin=khuyenmai&delete=".$row['id']."' class='btn btn-delete btn-sm' onclick=\"return confirm('Xóa khuyến mại này?')\">Xóa</a>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='7'>Không tìm thấy khuyến mại nào</td></tr>";
}
?>
</tbody>
</table>
</div>

<script>
function editRow(id, sanpham_id, giakhuyenmai, giamgia, ngay_bat_dau, ngay_ket_thuc){
    document.getElementById('stt').value = id;
    document.getElementById('sanpham_id').value = sanpham_id;
    document.getElementById('giakhuyenmai').value = giakhuyenmai;
    document.getElementById('giamgia').value = giamgia;
    document.getElementById('ngay_bat_dau').value = ngay_bat_dau;
    document.getElementById('ngay_ket_thuc').value = ngay_ket_thuc;

    let btn = document.getElementById('submitBtn');
    btn.innerText = "💾 Cập nhật";
    btn.name = "edit";
    btn.classList.remove('btn-success');
    btn.classList.add('btn-primary');
}
</script>
</body>
</html>
