<?php 
$conn = new mysqli("localhost", "root", "", "banhangonline");
$conn->set_charset("utf8");
if($conn->connect_error) die("Kết nối thất bại: ".$conn->connect_error);

// ====== Thêm/Sửa liên hệ ======
if(isset($_POST['add']) || isset($_POST['edit'])){
    $hoten = $conn->real_escape_string($_POST['hoten']);
    $email = $conn->real_escape_string($_POST['email']);
    $noidung = $conn->real_escape_string($_POST['noidung']);

    if(isset($_POST['add'])){
        $sql = "INSERT INTO lienhe (hoten, email, noidung) 
                VALUES ('$hoten','$email','$noidung')";
    } else { // edit
        $stt = intval($_POST['stt']);
        $sql = "UPDATE lienhe SET hoten='$hoten', email='$email', noidung='$noidung' WHERE id=$stt";
    }
    $conn->query($sql) or die($conn->error);
    header("Location: http://localhost/banhangonline/admin/index.php?admin=lienhe");
    exit;
}

// ====== Xóa liên hệ ======
if(isset($_GET['delete'])){
    $stt = intval($_GET['delete']);
    $conn->query("DELETE FROM lienhe WHERE id=$stt") or die($conn->error);
    header("Location: http://localhost/banhangonline/admin/index.php?admin=lienhe");
    exit;
}

// ====== Tìm kiếm ======
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = "";
if($search != ""){
    $safe = $conn->real_escape_string($search);
    $where = "WHERE hoten LIKE '%$safe%' OR email LIKE '%$safe%' OR noidung LIKE '%$safe%'";
}

// ====== Phân trang ======
$limit = 5; // số bản ghi mỗi trang
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Tổng số bản ghi
$total_res = $conn->query("SELECT COUNT(*) as cnt FROM lienhe $where");
$total_row = $total_res->fetch_assoc();
$total_records = $total_row['cnt'];
$total_pages = ceil($total_records / $limit);

// Lấy danh sách liên hệ theo trang
$result = $conn->query("SELECT * FROM lienhe $where ORDER BY id DESC LIMIT $limit OFFSET $offset");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Liên hệ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .container { margin: 40px auto; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; margin-bottom: 25px; color: #2c3e50; font-weight: 700; }
        form { margin-bottom: 30px; display: flex; flex-wrap: wrap; gap: 10px; }
        form input, form textarea { padding: 8px; border-radius: 6px; border: 1px solid #ccc; }
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
        .search-box { text-align:center; margin-bottom:20px; }
        .pagination { justify-content: center; margin-top: 20px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Quản lý Liên hệ</h2>

    <!-- Form tìm kiếm -->
    <div class="search-box">
        <form method="get">
            <input type="hidden" name="admin" value="lienhe">
            <input type="text" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES) ?>" placeholder="🔍 Tìm kiếm liên hệ..." style="padding:6px;width:300px;">
            <button type="submit" style="padding:6px 12px;">Tìm</button>
        </form>
    </div>

    <!-- Form thêm/sửa liên hệ -->
    <form method="post">
        <input type="hidden" name="stt" id="stt">
        <input type="text" name="hoten" id="hoten" placeholder="Họ tên" required>
        <input type="email" name="email" id="email" placeholder="Email" required>
        <textarea name="noidung" id="noidung" placeholder="Nội dung" rows="2" required></textarea>
        <button type="submit" name="add" id="submitBtn">➕ Thêm</button>
    </form>

    <!-- Bảng liên hệ -->
    <table>
        <thead>
            <tr>
                <th>STT</th>
                <th>Họ tên</th>
                <th>Email</th>
                <th>Nội dung</th>
                <th>Ngày tạo</th>
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
                            <td>".htmlspecialchars($row['hoten'])."</td>
                            <td>".htmlspecialchars($row['email'])."</td>
                            <td>".htmlspecialchars($row['noidung'])."</td>
                            <td>".$row['created_at']."</td>
                            <td>
                                <a href='#' class='btn btn-edit btn-sm' onclick=\"editRow(
                                    '".$row['id']."',
                                    '".htmlspecialchars($row['hoten'], ENT_QUOTES)."',
                                    '".htmlspecialchars($row['email'], ENT_QUOTES)."',
                                    '".htmlspecialchars($row['noidung'], ENT_QUOTES)."'
                                )\">Sửa</a>
                                <a href='http://localhost/banhangonline/admin/index.php?admin=lienhe&delete=".$row['id']."' 
                                   class='btn btn-delete btn-sm' 
                                   onclick=\"return confirm('Xóa liên hệ này?')\">Xóa</a>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>Chưa có dữ liệu</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Phân trang -->
    <nav>
        <ul class="pagination">
            <?php if($page > 1): ?>
                <li class="page-item"><a class="page-link" href="?admin=lienhe&search=<?= urlencode($search) ?>&page=<?= $page-1 ?>">«</a></li>
            <?php endif; ?>
            <?php for($i=1;$i<=$total_pages;$i++): ?>
                <li class="page-item <?= $i==$page?'active':'' ?>">
                    <a class="page-link" href="?admin=lienhe&search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <?php if($page < $total_pages): ?>
                <li class="page-item"><a class="page-link" href="?admin=lienhe&search=<?= urlencode($search) ?>&page=<?= $page+1 ?>">»</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

<script>
function editRow(id, hoten, email, noidung){
    document.getElementById('stt').value = id;
    document.getElementById('hoten').value = hoten;
    document.getElementById('email').value = email;
    document.getElementById('noidung').value = noidung;

    let btn = document.getElementById('submitBtn');
    btn.innerText = "💾 Cập nhật";
    btn.name = "edit";
    btn.style.backgroundColor = "#3498db";
}
</script>
</body>
</html>
