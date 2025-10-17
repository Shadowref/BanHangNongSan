<?php
// Kết nối database
$conn = new mysqli("localhost", "root", "", "banhangonline");
$conn->set_charset("utf8");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

// Lấy danh sách loại và thương hiệu
$loaiList = $conn->query("SELECT * FROM loaisanpham");
$thuonghieuList = $conn->query("SELECT * FROM thuonghieu");


// ================== XỬ LÝ ĐỔI TRẠNG THÁI ==================
if (isset($_GET['toggle']) && isset($_GET['status'])) {
    $id = (int)$_GET['toggle'];
    $status = $_GET['status'] === 'an' ? 'an' : 'hien';

    // Cập nhật trạng thái sản phẩm
    $conn->query("UPDATE sanpham SET trangthai='$status' WHERE id=$id");

    // Lấy tên sản phẩm để thông báo cho rõ
    $sp = $conn->query("SELECT tensp FROM sanpham WHERE id=$id")->fetch_assoc();
    $tensp = $sp ? $sp['tensp'] : "Sản phẩm không xác định";

    // Nội dung thông báo tùy trạng thái
    if ($status == 'an') {
        $msg = "❌ Sản phẩm <b>$tensp</b> đã Ngừng kinh doanh)";
    } else {
        $msg = "✅ Sản phẩm <b>$tensp</b> đã Được kinh doanh)";
    }

    // Ghi log vào bảng thongbao
    $conn->query("INSERT INTO thongbao (user_id, message) VALUES (1, '".$conn->real_escape_string(strip_tags($msg))."')");

    // Hiển thị thông báo
    echo "<div style='padding:10px; background:#d1ecf1; border:1px solid #bee5eb; color:#0c5460; border-radius:6px; margin-bottom:15px;'>$msg</div>";

    // Quay lại danh sách
    header("Location: ?admin=hienthisp");
    exit;
}



// Xử lý thêm hoặc sửa sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $tensp = $_POST['tensp'];
    $gia = $_POST['gia'];
    $mota = $_POST['mota'];
    $soluong = $_POST['soluong'] ?: 0;
    $id_loai = $_POST['id_loai'] ?: NULL;
    $id_thuonghieu = $_POST['id_thuonghieu'] ?: NULL;

    // Upload hình ảnh
    $hinhanh = $_POST['hinhanh_old'] ?? '';
    if (isset($_FILES['hinhanh']) && $_FILES['hinhanh']['error'] == 0) {
        $target_dir = "../img/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $hinhanh = time() . "_" . basename($_FILES['hinhanh']['name']);
        move_uploaded_file($_FILES['hinhanh']['tmp_name'], $target_dir . $hinhanh);
    }

    if ($id) {
        // Sửa sản phẩm
        $stmt = $conn->prepare("UPDATE sanpham 
            SET tensp=?, gia=?, hinhanh=?, mota=?, soluong=?, id_loai=?, id_thuonghieu=? 
            WHERE id=?");
        $stmt->bind_param("sdsssiii", $tensp, $gia, $hinhanh, $mota, $soluong, $id_loai, $id_thuonghieu, $id);
        $stmt->execute();
        $stmt->close();

        $msg = "Cập nhật sản phẩm: $tensp | Giá: " . number_format($gia) . "đ | SL: $soluong";
        $conn->query("INSERT INTO thongbao (user_id, message) VALUES (1, '".$conn->real_escape_string($msg)."')");
        echo "<p style='color:green;'>Sửa sản phẩm thành công!</p>";
    } else {
        // Thêm sản phẩm
        $stmt = $conn->prepare("INSERT INTO sanpham
            (tensp, gia, hinhanh, mota, soluong, id_loai, id_thuonghieu) 
            VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("sdsssii", $tensp, $gia, $hinhanh, $mota, $soluong, $id_loai, $id_thuonghieu);
        $stmt->execute();
        $stmt->close();

        $msg = "Thêm sản phẩm mới: $tensp | Giá: " . number_format($gia) . "đ | SL: $soluong";
        $conn->query("INSERT INTO thongbao (user_id, message) VALUES (1, '".$conn->real_escape_string($msg)."')");
        echo "<p style='color:green;'>Thêm sản phẩm thành công!</p>";
    }
}

// Xử lý xóa sản phẩm
if (isset($_GET['xoasp'])) {
    $id = (int)$_GET['xoasp'];

    // Kiểm tra sản phẩm có trong đơn hàng của khách hàng không
    $check = $conn->query("SELECT COUNT(*) AS total FROM lichsugiaodich WHERE id_sanpham = $id");
    $hasOrder = $check->fetch_assoc()['total'] > 0;

    // Lấy thông tin sản phẩm
    $result = $conn->query("SELECT tensp, hinhanh FROM sanpham WHERE id = $id");
    if ($row = $result->fetch_assoc()) {
        $tensp = $row['tensp'];

        if ($hasOrder) {
            // ❌ Không cho xóa, chỉ hiện cảnh báo
            $msg = "⚠️ Bạn không thể xóa sản phẩm <b>$tensp</b> vì sản phẩm đang có thông tin đơn hàng của khách hàng. 
                    Nếu không muốn hiển thị sản phẩm này, hãy thay đổi <b>Trạng thái</b> để sản phẩm sẽ <b>ngừng kinh doanh</b>.";
            echo "<div style='padding:12px; background:#fff3cd; border:1px solid #ffeeba; color:#856404; 
                    border-radius:6px; margin-bottom:15px; line-height:1.6;'>$msg</div>";

        } else {
            // ✅ Nếu chưa có đơn hàng thì cho phép xóa
            if ($row['hinhanh'] && file_exists("../img/" . $row['hinhanh'])) {
                unlink("../img/" . $row['hinhanh']);
            }
            $conn->query("DELETE FROM sanpham WHERE id = $id");

            $msg = "🗑️ Sản phẩm <b>$tensp</b> đã bị xóa thành công.";
            echo "<div style='padding:12px; background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; 
                    border-radius:6px; margin-bottom:15px; line-height:1.6;'>$msg</div>";

            // Ghi lại thông báo cho admin
            $conn->query("INSERT INTO thongbao (user_id, message) VALUES (1, '".$conn->real_escape_string(strip_tags($msg))."')");
        }
    }
}



// Lấy thông tin sản phẩm nếu sửa
$editProduct = null;
if (isset($_GET['suasp'])) {
    $id = (int)$_GET['suasp'];
    $result = $conn->query("SELECT * FROM sanpham WHERE id=$id");
    $editProduct = $result->fetch_assoc();
}

/* ================== TÌM KIẾM + PHÂN TRANG ================== */
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = "WHERE 1"; // Admin thấy tất cả
if ($search !== '') {
    $searchSafe = $conn->real_escape_string($search);
    $where .= " AND sp.tensp LIKE '%$searchSafe%'";
}

$limit = 10;
$pageNum = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($pageNum - 1) * $limit;

// Tính tổng trang
$resultTotal = $conn->query("SELECT COUNT(*) AS total FROM sanpham sp $where");
$totalRow = $resultTotal->fetch_assoc()['total'];
$totalPages = ceil($totalRow / $limit);

// Lấy danh sách sản phẩm phân trang
$sanpham = $conn->query("
    SELECT sp.*, l.tenloai, t.tenthuonghieu
    FROM sanpham sp
    LEFT JOIN loaisanpham l ON sp.id_loai = l.id
    LEFT JOIN thuonghieu t ON sp.id_thuonghieu = t.id
    $where
    ORDER BY sp.id DESC
    LIMIT $start, $limit
");
?>


<h2>Quản lý sản phẩm</h2>

<!-- Form thêm/sửa sản phẩm -->
<form method="post" enctype="multipart/form-data" style="margin-bottom:20px;">
    <input type="hidden" name="id" value="<?php echo $editProduct['id'] ?? ''; ?>">
    <input type="hidden" name="hinhanh_old" value="<?php echo $editProduct['hinhanh'] ?? ''; ?>">

    <input type="text" name="tensp" placeholder="Tên sản phẩm" value="<?php echo $editProduct['tensp'] ?? ''; ?>" required>
    <input type="number" name="gia" placeholder="Giá" value="<?php echo $editProduct['gia'] ?? ''; ?>" required>
    <input type="file" name="hinhanh" accept="image/*">
    <?php if(isset($editProduct['hinhanh']) && $editProduct['hinhanh']): ?>
        <img src="../img/<?php echo $editProduct['hinhanh']; ?>" width="50" alt="Hình hiện tại">
    <?php endif; ?>
    <textarea name="mota" placeholder="Mô tả"><?php echo $editProduct['mota'] ?? ''; ?></textarea>
    <input type="number" name="soluong" placeholder="Số lượng" value="<?php echo $editProduct['soluong'] ?? 0; ?>">

    <!-- Select loại -->
    <select name="id_loai">
        <option value="">Chọn loại</option>
        <?php
        $loaiList->data_seek(0);
        while($l = $loaiList->fetch_assoc()):
        ?>
            <option value="<?php echo $l['id']; ?>" <?php if(($editProduct['id_loai'] ?? '') == $l['id']) echo 'selected'; ?>>
                <?php echo $l['tenloai']; ?>
            </option>
        <?php endwhile; ?>
    </select>

    <!-- Select thương hiệu -->
    <select name="id_thuonghieu">
        <option value="">Chọn thương hiệu</option>
        <?php
        $thuonghieuList->data_seek(0);
        while($t = $thuonghieuList->fetch_assoc()):
        ?>
            <option value="<?php echo $t['id']; ?>" <?php if(($editProduct['id_thuonghieu'] ?? '') == $t['id']) echo 'selected'; ?>>
                <?php echo $t['tenthuonghieu']; ?>
            </option>
        <?php endwhile; ?>
    </select>

    <button type="submit"><?php echo $editProduct ? "Cập nhật" : "Thêm sản phẩm"; ?></button>
</form>

<!-- Thanh tìm kiếm -->
<form method="get" style="text-align:center; margin:20px;">
    <input type="hidden" name="admin" value="hienthisp">
    <input type="text" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES); ?>" 
           placeholder="🔍 Nhập tên sản phẩm cần tìm" 
           style="padding:6px; width:300px;">
    <button type="submit" style="padding:6px 12px;">Tìm kiếm</button>
</form>

<!-- Bảng danh sách sản phẩm -->
<table class="table-order">
    <thead>
      <tr>
    <th>STT</th>
    <th>Tên</th>
    <th>Giá</th>
    <th>Hình ảnh</th>
    <th>Mô tả</th>
    <th>Số lượng</th>
    <th>Loại</th>
    <th>Thương hiệu</th>
    <th>Trạng thái</th>
    <th>Hành động</th>
</tr>

    </thead>
    <tbody>
        <?php $stt = $start + 1; ?>
        <?php while ($row = $sanpham->fetch_assoc()): ?>
        <tr>
            <td><?php echo $stt++; ?></td>
            <td><?php echo htmlspecialchars($row['tensp']); ?></td>
            <td><?php echo number_format($row['gia']); ?></td>
            <td>
                <?php 
                    $imgPath = "../img/" . ($row['hinhanh'] ?: 'no-image.png');
                    if(!file_exists($imgPath)) $imgPath = "../img/no-image.png";
                ?>
                <img src="<?php echo $imgPath; ?>" width="50" alt="Hình sản phẩm">
            </td>
            <td><?php echo htmlspecialchars($row['mota']); ?></td>
            <td><?php echo $row['soluong']; ?></td>
            <td><?php echo htmlspecialchars($row['tenloai'] ?? 'Chưa chọn'); ?></td>
            <td><?php echo htmlspecialchars($row['tenthuonghieu'] ?? 'Chưa chọn'); ?></td>
            <td>
    <form method="get" style="margin:0;">
        <input type="hidden" name="admin" value="hienthisp">
        <input type="hidden" name="toggle" value="<?php echo $row['id']; ?>">
        <select name="status" onchange="this.form.submit()">
            <option value="hien" <?php if($row['trangthai']=='hien') echo 'selected'; ?>>Hiện</option>
            <option value="an" <?php if($row['trangthai']=='an') echo 'selected'; ?>>Ẩn</option>
        </select>
    </form>
</td>

            <td>
                <a href="?admin=hienthisp&suasp=<?php echo $row['id']; ?>">Sửa</a> |
                <a href="?admin=hienthisp&xoasp=<?php echo $row['id']; ?>" 
                onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</a> |
            </td>

        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- Phân trang -->
<div style="text-align:center; margin-top:10px;">
    <?php if($pageNum > 1): ?>
        <a href="?admin=hienthisp&page=<?php echo $pageNum-1; ?>&search=<?php echo urlencode($search); ?>">&laquo; Trước</a>
    <?php endif; ?>

    <?php for($i=1; $i<=$totalPages; $i++): ?>
        <a href="?admin=hienthisp&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
           style="<?php echo $i==$pageNum ? 'font-weight:bold;color:#27ae60;' : ''; ?>">
           <?php echo $i; ?>
        </a>
    <?php endfor; ?>

    <?php if($pageNum < $totalPages): ?>
        <a href="?admin=hienthisp&page=<?php echo $pageNum+1; ?>&search=<?php echo urlencode($search); ?>">Sau &raquo;</a>
    <?php endif; ?>
</div>

<style>
/* Reset cơ bản */
* { margin:0; padding:0; box-sizing:border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.table-order { width:100%; border-collapse:collapse; margin-bottom:20px; }
.table-order th, .table-order td { border:1px solid #bdc3c7; padding:10px; text-align:center; }
.table-order th { background:#27ae60; color:#fff; }
.table-order tr:nth-child(even) { background:#ecf0f1; }
.table-order tr:hover { background:#d1f2eb; }
.table-order img { border-radius:4px; }
.table-order a { color:#e74c3c; text-decoration:none; font-weight:bold; margin:0 5px; }
.table-order a:hover { text-decoration:underline; }
form { display:flex; flex-wrap:wrap; gap:15px; margin-bottom:25px; background:#ecf0f1; padding:15px; border-radius:8px; }
form input, form select, form textarea { flex:1 1 200px; padding:8px 10px; border-radius:6px; border:1px solid #ccc; font-size:14px; }
form textarea { flex:1 1 100%; min-height:60px; }
form button { padding:10px 20px; background:#27ae60; color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:bold; transition:0.3s; }
form button:hover { background:#1abc9c; }
</style>
