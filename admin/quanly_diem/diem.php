<?php 
$conn = new mysqli("localhost", "root", "", "banhangonline");
$conn->set_charset("utf8");
if($conn->connect_error) die("Kết nối thất bại: ".$conn->connect_error);

// Lấy danh sách user
$users = $conn->query("SELECT id, username FROM taikhoan");

// Lấy danh sách đơn hàng
$donhangs = $conn->query("SELECT id, tensanpham FROM donhang");

// ====== Thêm/Sửa điểm ======
if(isset($_POST['add']) || isset($_POST['edit'])){
    $taikhoan_id = !empty($_POST['taikhoan_id']) ? intval($_POST['taikhoan_id']) : "NULL";
    $diem = !empty($_POST['diem']) ? intval($_POST['diem']) : "NULL";
    $loai = $_POST['loai'];
    $id_donhang = !empty($_POST['id_donhang']) ? intval($_POST['id_donhang']) : "NULL";
    $mota = $conn->real_escape_string($_POST['mota']);
    
    if(isset($_POST['add'])){
        $sql = "INSERT INTO lichsu_diem (taikhoan_id, diem, loai, id_donhang, mota)
                VALUES (".($taikhoan_id=="NULL"?"NULL":$taikhoan_id).", $diem, '$loai', ".($id_donhang=="NULL"?"NULL":$id_donhang).", ".($mota? "'$mota'":"NULL").")";
    } else { // edit
        $stt = intval($_POST['stt']);
        $sql = "UPDATE lichsu_diem SET 
                    taikhoan_id=".($taikhoan_id=="NULL"?"NULL":$taikhoan_id).",
                    diem=$diem,
                    loai='$loai',
                    id_donhang=".($id_donhang=="NULL"?"NULL":$id_donhang).",
                    mota=".($mota? "'$mota'":"NULL")."
                WHERE id=$stt";
    }
    $conn->query($sql) or die($conn->error);
    header("Location: http://localhost/banhangonline/admin/index.php?admin=diem");
    exit;
}

if(isset($_GET['delete'])){
    $stt = intval($_GET['delete']);
    $conn->query("DELETE FROM lichsu_diem WHERE id=$stt") or die($conn->error);
    header("Location: index.php?admin=diem");
    exit;
}

// ====== Xử lý tìm kiếm ======
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";

// ====== Phân trang ======
$limit = 10; 
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Đếm tổng số bản ghi (có search)
$countSql = "
    SELECT COUNT(*) as total
    FROM lichsu_diem 
    LEFT JOIN taikhoan ON lichsu_diem.taikhoan_id=taikhoan.id
    LEFT JOIN donhang ON lichsu_diem.id_donhang=donhang.id
    WHERE taikhoan.username LIKE '%$search%' OR lichsu_diem.mota LIKE '%$search%'
";
$totalRows = $conn->query($countSql)->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Lấy dữ liệu có phân trang + tìm kiếm
$result = $conn->query("
    SELECT lichsu_diem.*, taikhoan.username, donhang.tensanpham AS ten_donhang
    FROM lichsu_diem 
    LEFT JOIN taikhoan ON lichsu_diem.taikhoan_id=taikhoan.id
    LEFT JOIN donhang ON lichsu_diem.id_donhang=donhang.id
    WHERE taikhoan.username LIKE '%$search%' OR lichsu_diem.mota LIKE '%$search%'
    ORDER BY lichsu_diem.id DESC
    LIMIT $limit OFFSET $offset
");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý điểm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .container { margin: 40px auto; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; margin-bottom: 25px; color: #2c3e50; font-weight: 700; }
        form { margin-bottom: 30px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        form select, form input, form textarea { padding: 8px; border-radius: 6px; border: 1px solid #ccc; }
        form button { padding: 8px 15px; background: #27ae60; color: #fff; border: none; border-radius: 6px; cursor: pointer; transition: 0.3s; }
        form button:hover { background: #1abc9c; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { border: 1px solid #ddd; padding: 8px; text-align: center; vertical-align: middle; }
        table th { background: #27ae60; color: #fff; text-transform: uppercase; }
        table tr:nth-child(even) { background: #f9f9f9; }
        table tr:hover { background: #eafaf1; }
        a.btn { text-decoration: none; color: #fff; padding: 5px 10px; border-radius: 5px; }
        a.btn-delete { background: #e74c3c; }
        a.btn-delete:hover { background: #c0392b; }
        a.btn-edit { background: #f39c12; }
        a.btn-edit:hover { background: #d35400; }
    </style>
</head>
<body>
<div class="container">
    <h2>Quản lý Lịch sử điểm</h2>

    <!-- Form tìm kiếm -->
    <form method="get" class="mb-3">
        <input type="hidden" name="admin" value="diem">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm theo username hoặc mô tả" style="flex:1; padding:8px;">
        <button type="submit" class="btn btn-primary">🔍 Tìm kiếm</button>
    </form>

    <!-- Form thêm/sửa -->
    <form method="post">
        <input type="hidden" name="stt" id="stt">
        <select name="taikhoan_id" id="taikhoan_id">
            <option value="">-- Chọn người dùng (tùy chọn) --</option>
            <?php mysqli_data_seek($users,0); while($u = $users->fetch_assoc()){ ?>
                <option value="<?= $u['id']; ?>"><?= $u['username']; ?></option>
            <?php } ?>
        </select>
        <input type="number" name="diem" id="diem" placeholder="Điểm" required>
        <select name="loai" id="loai" required>
            <option value="cong">Cộng</option>
            <option value="tieu">Trừ</option>
        </select>
        <select name="id_donhang" id="id_donhang">
            <option value="">-- Chọn đơn hàng (tùy chọn) --</option>
            <?php mysqli_data_seek($donhangs,0); while($d = $donhangs->fetch_assoc()){ ?>
                <option value="<?= $d['id']; ?>"><?= $d['tensanpham']; ?></option>
            <?php } ?>
        </select>
        <input type="text" name="mota" id="mota" placeholder="Mô tả (tùy chọn)">
        <button type="submit" name="add" id="submitBtn">➕ Thêm điểm</button>
    </form>

    <!-- Bảng điểm -->
    <table>
        <thead>
            <tr>
                <th>STT</th>
                <th>Người dùng</th>
                <th>Điểm</th>
                <th>Loại</th>
                <th>Đơn hàng</th>
                <th>Mô tả</th>
                <th>Ngày</th>
                <th>Hành động</th>
            </tr>
        </thead>
<tbody>
<?php
if($result->num_rows > 0){
    $stt = $offset + 1;
    while($row = $result->fetch_assoc()){
        echo "<tr>
                <td>".$stt++."</td>
                <td>".htmlspecialchars($row['username'])."</td>
                <td>".$row['diem']."</td>
                <td>".$row['loai']."</td>
                <td>".($row['ten_donhang'] ?? "")."</td>
                <td>".htmlspecialchars($row['mota'])."</td>
                <td>".$row['ngay']."</td>
                <td>
                    <a href='#' class='btn btn-edit btn-sm' onclick=\"editRow(
                        '".$row['id']."',
                        '".$row['taikhoan_id']."',
                        '".$row['diem']."',
                        '".$row['loai']."',
                        '".$row['id_donhang']."',
                        '".htmlspecialchars($row['mota'], ENT_QUOTES)."'
                    )\">Sửa</a>
                    
                    <a href='index.php?admin=diem&delete=".$row['id']."' 
                       class='btn btn-delete btn-sm' 
                       onclick=\"return confirm('Xóa lịch sử điểm này?')\">Xóa</a>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='8'>Không tìm thấy dữ liệu</td></tr>";
}
?>
</tbody>
</table>

<!-- Phân trang -->
<div class="mt-3">
    <nav>
        <ul class="pagination justify-content-center">
            <?php if($page > 1): ?>
                <li class="page-item"><a class="page-link" href="?admin=diem&search=<?= urlencode($search) ?>&page=<?= $page-1 ?>">« Trước</a></li>
            <?php endif; ?>
            <?php for($i=1;$i<=$totalPages;$i++): ?>
                <li class="page-item <?= ($i==$page)?'active':'' ?>">
                    <a class="page-link" href="?admin=diem&search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <?php if($page < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="?admin=diem&search=<?= urlencode($search) ?>&page=<?= $page+1 ?>">Sau »</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

</div>

<script>
function editRow(id, taikhoan_id, diem, loai, id_donhang, mota){
    document.getElementById('stt').value = id;
    document.getElementById('taikhoan_id').value = taikhoan_id;
    document.getElementById('diem').value = diem;
    document.getElementById('loai').value = loai;
    document.getElementById('id_donhang').value = id_donhang;
    document.getElementById('mota').value = mota;

    let btn = document.getElementById('submitBtn');
    btn.innerText = "💾 Cập nhật";
    btn.name = "edit";
    btn.style.backgroundColor = "#3498db";
}
</script>
</body>
</html>
