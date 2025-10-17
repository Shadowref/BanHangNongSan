<?php
$conn = mysqli_connect("localhost", "root", "", "banhangonline");
mysqli_set_charset($conn, "utf8");
if (!$conn) die("Kết nối thất bại: " . mysqli_connect_error());

// Lấy danh sách sản phẩm
$products = mysqli_query($conn, "SELECT id, tensp FROM sanpham");
// Lấy danh sách user
$users = mysqli_query($conn, "SELECT id, username FROM taikhoan");

// ====== Thêm/Sửa đánh giá ======
if (isset($_POST['add']) || isset($_POST['edit'])) {
    $id_sanpham   = intval($_POST['id_sanpham']);
    $id_nguoidung = !empty($_POST['id_nguoidung']) ? intval($_POST['id_nguoidung']) : "NULL";
    $rating       = intval($_POST['rating']);
    $comment      = mysqli_real_escape_string($conn, $_POST['comment']);
    $user_name    = mysqli_real_escape_string($conn, $_POST['user_name']);
    $trangthai    = isset($_POST['trangthai']) ? intval($_POST['trangthai']) : 1;

    if(isset($_POST['add'])) {
        $sql = "INSERT INTO danhgia (id_sanpham, id_nguoidung, user_name, rating, comment, trangthai)
                VALUES ($id_sanpham, ".($id_nguoidung=="NULL"?"NULL":$id_nguoidung).", '$user_name', $rating, '$comment', $trangthai)";
    } else { // edit
        $id = intval($_POST['id']);
        $sql = "UPDATE danhgia 
                SET id_sanpham=$id_sanpham, 
                    id_nguoidung=".($id_nguoidung=="NULL"?"NULL":$id_nguoidung).", 
                    user_name='$user_name', 
                    rating=$rating, 
                    comment='$comment',
                    trangthai=$trangthai
                WHERE id=$id";
    }

    mysqli_query($conn, $sql) or die(mysqli_error($conn));
    header("Location: index.php?admin=danhgia");
    exit;
}

// ====== Xóa đánh giá ======
if(isset($_GET['delete']) && $_GET['admin'] == 'danhgia'){
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM danhgia WHERE id=$id") or die(mysqli_error($conn));
    header("Location: index.php?admin=danhgia");
    exit;
}

// ====== Đổi trạng thái (ẩn/hiện) ======
if(isset($_GET['toggle']) && $_GET['admin'] == 'danhgia'){
    $id = intval($_GET['toggle']);
    mysqli_query($conn, "UPDATE danhgia SET trangthai = IF(trangthai=1,0,1) WHERE id=$id") or die(mysqli_error($conn));
    header("Location: index.php?admin=danhgia");
    exit;
}

// ====== Thanh tìm kiếm ======
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = "";
if ($search !== '') {
    $safe = mysqli_real_escape_string($conn, $search);
    $where = "WHERE sanpham.tensp LIKE '%$safe%' OR danhgia.user_name LIKE '%$safe%'";
}

// ====== Lấy danh sách đánh giá ======
$sql = "SELECT danhgia.*, sanpham.tensp 
        FROM danhgia 
        LEFT JOIN sanpham ON danhgia.id_sanpham = sanpham.id
        $where
        ORDER BY danhgia.id DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Đánh giá</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .container { margin: 40px auto; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; margin-bottom: 25px; color: #2c3e50; font-weight: 700; }
        form.add-form { margin-bottom: 30px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        form.add-form select, form.add-form input, form.add-form textarea { padding: 8px; border-radius: 6px; border: 1px solid #ccc; }
        form.add-form button { padding: 8px 15px; background: #27ae60; color: #fff; border: none; border-radius: 6px; cursor: pointer; transition: 0.3s; }
        form.add-form button:hover { background: #1abc9c; }
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
        a.btn-toggle { background: #3498db; }
        a.btn-toggle:hover { background: #2980b9; }
        .search-box { text-align: center; margin-bottom: 20px; }
        .search-box input { padding: 8px; width: 300px; border: 1px solid #ccc; border-radius: 6px; }
        .search-box button { padding: 8px 14px; border: none; border-radius: 6px; background: #3498db; color: #fff; cursor: pointer; }
        .search-box button:hover { background: #2980b9; }
    </style>
</head>
<body>
<div class="container">
    <h2>Quản lý Đánh giá</h2>

    <!-- Thanh tìm kiếm -->
    <div class="search-box">
        <form method="get">
            <input type="hidden" name="admin" value="danhgia">
            <input type="text" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES); ?>" placeholder="🔍 Tìm theo sản phẩm hoặc khách hàng">
            <button type="submit">Tìm kiếm</button>
        </form>
    </div>

    <!-- Form thêm/sửa đánh giá -->
    <form method="post" class="add-form">
        <input type="hidden" name="id" id="id">
        <select name="id_sanpham" id="id_sanpham" required>
            <option value="">-- Chọn sản phẩm --</option>
            <?php mysqli_data_seek($products,0); while($p = mysqli_fetch_assoc($products)) { ?>
                <option value="<?= $p['id']; ?>"><?= $p['tensp']; ?></option>
            <?php } ?>
        </select>

        <select name="id_nguoidung" id="id_nguoidung">
            <option value="">-- Chọn khách hàng (tùy chọn) --</option>
            <?php mysqli_data_seek($users,0); while($u = mysqli_fetch_assoc($users)) { ?>
                <option value="<?= $u['id']; ?>"><?= $u['username']; ?></option>
            <?php } ?>
        </select>

        <input type="text" name="user_name" id="user_name" placeholder="Tên hiển thị" required>
        <input type="number" name="rating" id="rating" min="1" max="5" placeholder="Số sao (1-5)" required>
        <textarea name="comment" id="comment" placeholder="Nhận xét"></textarea>

        <select name="trangthai" id="trangthai" required>
            <option value="1">Hiện</option>
            <option value="0">Ẩn</option>
        </select>

        <button type="submit" name="add" id="submitBtn">➕ Thêm/Cập nhật đánh giá</button>
    </form>

    <!-- Bảng đánh giá -->
    <table>
        <thead>
            <tr>
                <th>STT</th>
                <th>Sản phẩm</th>
                <th>Khách hàng</th>
                <th>Số sao</th>
                <th>Nhận xét</th>
                <th>Trạng thái</th>
                <th>Ngày đăng</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
<?php
if(mysqli_num_rows($result) > 0){
    $stt = 1;
    while($row = mysqli_fetch_assoc($result)){
        echo "<tr>
                <td>".$stt++."</td>
                <td>".htmlspecialchars($row['tensp'])."</td>
                <td>".htmlspecialchars($row['user_name'])."</td>
                <td>".$row['rating']."</td>
                <td>".htmlspecialchars($row['comment'])."</td>
                <td>".($row['trangthai']==1 ? "✅ Hiện" : "🚫 Ẩn")."</td>
                <td>".$row['ngaydat']."</td>
                <td>
                    <a href='#' class='btn btn-edit' onclick=\"editRow(
                        '".$row['id']."',
                        '".$row['id_sanpham']."',
                        '".$row['id_nguoidung']."',
                        '".htmlspecialchars($row['user_name'], ENT_QUOTES)."',
                        '".$row['rating']."',
                        '".htmlspecialchars($row['comment'], ENT_QUOTES)."',
                        '".$row['trangthai']."'
                    )\">Sửa</a>
                    <a href='index.php?admin=danhgia&toggle=".$row['id']."' class='btn btn-toggle'>".($row['trangthai']==1?"Ẩn":"Hiện")."</a>
                    <a href='index.php?admin=danhgia&delete=".$row['id']."' class='btn btn-delete' onclick=\"return confirm('Xóa đánh giá này?')\">Xóa</a>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='8'>Không tìm thấy đánh giá nào</td></tr>";
}
?>
        </tbody>
    </table>
</div>

<script>
function editRow(id, id_sanpham, id_nguoidung, user_name, rating, comment, trangthai) {
    document.getElementById('id').value = id;
    document.getElementById('id_sanpham').value = id_sanpham;
    document.getElementById('id_nguoidung').value = id_nguoidung;
    document.getElementById('user_name').value = user_name;
    document.getElementById('rating').value = rating;
    document.getElementById('comment').value = comment;
    document.getElementById('trangthai').value = trangthai;

    let btn = document.getElementById('submitBtn');
    btn.innerText = "💾 Cập nhật";
    btn.name = "edit";
    btn.style.backgroundColor = "#3498db";
}
</script>
</body>
</html>
