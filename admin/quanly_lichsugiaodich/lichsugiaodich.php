<?php
$conn = new mysqli("localhost", "root", "", "banhangonline");
$conn->set_charset("utf8");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

// ====== Sửa giao dịch ======
if (isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $trangthai = $conn->real_escape_string($_POST['trangthai']);
    $conn->query("UPDATE lichsugiaodich SET trangthai='$trangthai' WHERE id=$id") or die($conn->error);
    header("Location: index.php?admin=lichsugiaodich");
    exit;
}

// ====== Xóa giao dịch ======
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM lichsugiaodich WHERE id=$id") or die($conn->error);
    header("Location: index.php?admin=lichsugiaodich");
    exit;
}

// ====== Tìm kiếm ======
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$where = "";
if ($search != "") {
    $safeSearch = $conn->real_escape_string($search);
    $where = "WHERE sp.tensp LIKE '%$safeSearch%' 
              OR tk.username LIKE '%$safeSearch%' 
              OR ls.phuongthucthanhtoan LIKE '%$safeSearch%'";
}

// ====== Phân trang ======
$limit = 10; 
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page-1) * $limit;

$countSql = "SELECT COUNT(*) as total 
             FROM lichsugiaodich ls
             LEFT JOIN taikhoan tk ON ls.id_nguoidung=tk.id
             LEFT JOIN sanpham sp ON ls.id_sanpham=sp.id
             $where";
$totalRow = $conn->query($countSql)->fetch_assoc()['total'];
$totalPage = ceil($totalRow/$limit);

$sql = "SELECT ls.*, tk.username, sp.tensp as ten_sp 
        FROM lichsugiaodich ls
        LEFT JOIN taikhoan tk ON ls.id_nguoidung=tk.id
        LEFT JOIN sanpham sp ON ls.id_sanpham=sp.id
        $where
        ORDER BY ls.id DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý Lịch sử giao dịch</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#f4f6f9; font-family:'Segoe UI'; }
.container { margin:40px auto; background:#fff; padding:25px; border-radius:12px; box-shadow:0 3px 10px rgba(0,0,0,0.1);}
h2 { text-align:center; margin-bottom:25px; color:#2c3e50; font-weight:700; }
table { width:100%; border-collapse:collapse;}
table th, table td { border:1px solid #ddd; padding:8px; text-align:center; vertical-align:middle;}
table th { background:#3498db; color:#fff;}
table tr:nth-child(even){background:#f9f9f9;}
a.btn { padding:5px 10px; border-radius:5px; text-decoration:none; color:#fff;}
a.btn-delete { background:#e74c3c;}
a.btn-delete:hover { background:#c0392b;}
a.btn-edit { background:#f39c12;}
a.btn-edit:hover { background:#d35400;}
.search-box { text-align:center; margin-bottom:20px;}
.pagination { margin-top:20px; justify-content:center;}
</style>
</head>
<body>
<div class="container">
    <h2>Quản lý Lịch sử giao dịch</h2>

    <!-- Thanh tìm kiếm -->
    <div class="search-box">
        <form method="get">
            <input type="hidden" name="admin" value="lichsugiaodich">
            <input type="text" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES) ?>" placeholder="🔍 Tìm theo tên SP, Username, PT thanh toán" style="padding:6px;width:350px;">
            <button type="submit" class="btn btn-primary">Tìm kiếm</button>
        </form>
    </div>

    <!-- Bảng giao dịch -->
    <table>
        <thead>
            <tr>
                <th>STT</th>
                <th>Người dùng</th>
                <th>Sản phẩm</th>
                <th>Số lượng</th>
                <th>Giá bán</th>
                <th>Tổng tiền</th>
                <th>Ngày đặt</th>
                <th>Ngày giao dịch</th>
                <th>Trạng thái</th>
                <th>Điểm sử dụng</th>
                <th>Ảnh nhận hàng</th>
                <th>Phương thức TT</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if($result->num_rows > 0){
            $stt = $offset+1;
            while($row = $result->fetch_assoc()){
                $anhNhanHang = $row['anh_nhanhang'] ? "<img src='../uploads/".$row['anh_nhanhang']."' width='60'>" : "Chưa có";
                echo "<tr>
                    <td>".$stt++."</td>
                    <td>".htmlspecialchars($row['username'])."</td>
                    <td>".htmlspecialchars($row['ten_sp'])."</td>
                    <td>".$row['soluong']."</td>
                    <td>".number_format($row['giaban'])." đ</td>
                    <td>".number_format($row['tongtien'])." đ</td>
                    <td>".$row['ngaydat']."</td>
                    <td>".$row['ngaygiaodich']."</td>
                    <td>".$row['trangthai']."</td>
                    <td>".$row['diem_sudung']."</td>
                    <td>".$anhNhanHang."</td>
                    <td>".$row['phuongthucthanhtoan']."</td>
                    <td>
                        <a href='#' class='btn btn-edit btn-sm' onclick=\"editRow(
                            '".$row['id']."',
                            '".$row['trangthai']."'
                        )\">Sửa</a>
                        <a href='index.php?admin=lichsugiaodich&delete=".$row['id']."' class='btn btn-delete btn-sm' onclick=\"return confirm('Xóa giao dịch này?')\">Xóa</a>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='13'>Không có dữ liệu</td></tr>";
        }
        ?>
        </tbody>
    </table>

    <!-- Phân trang -->
    <nav>
        <ul class="pagination">
            <?php for($i=1; $i<=$totalPage; $i++): ?>
                <li class="page-item <?= ($i==$page)?'active':'' ?>">
                    <a class="page-link" href="index.php?admin=lichsugiaodich&page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<!-- Form sửa trạng thái (ẩn) -->
<div id="editForm" style="display:none; position:fixed; top:30%; left:50%; transform:translate(-50%,-50%); background:#fff; padding:20px; border-radius:8px; box-shadow:0 3px 8px rgba(0,0,0,0.3); z-index:1000;">
    <h4>Cập nhật trạng thái</h4>
    <form method="post">
        <input type="hidden" name="id" id="edit_id">
        <select name="trangthai" id="edit_trangthai" class="form-select">
            <option value="dang_cho">Đang chờ</option>
            <option value="dang_giao">Đang giao</option>
            <option value="hoan_tat">Hoàn tất</option>
        </select>
        <div style="margin-top:15px;">
            <button type="submit" name="edit" class="btn btn-primary">💾 Lưu</button>
            <button type="button" class="btn btn-secondary" onclick="closeForm()">❌ Hủy</button>
        </div>
    </form>
</div>

<script>
function editRow(id, trangthai){
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_trangthai').value = trangthai;
    document.getElementById('editForm').style.display = 'block';
}
function closeForm(){
    document.getElementById('editForm').style.display = 'none';
}
</script>
</body>
</html>
