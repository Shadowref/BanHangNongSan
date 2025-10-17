<?php
$conn = mysqli_connect("localhost", "root", "", "banhangonline");
mysqli_set_charset($conn, "utf8");
if (!$conn) die("Kết nối thất bại: " . mysqli_connect_error());

// --- Thêm ---
if (isset($_POST['add'])) {
    $id_nguoidung = intval($_POST['id_nguoidung']);
    $id_sanpham   = intval($_POST['id_sanpham']);  
    $soluong      = intval($_POST['soluong']);
    $trangthai    = $_POST['trangthai'];

    $sp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT tensp, gia FROM sanpham WHERE id=$id_sanpham"));
    if ($sp) {
        $tensanpham = $sp['tensp'];
        $giaban     = $sp['gia'];
        $thanhtien  = $soluong * $giaban;

        mysqli_query($conn, "INSERT INTO donhang (id_nguoidung, id_sanpham, tensanpham, soluong, giaban, thanhtien, trangthai) 
        VALUES ($id_nguoidung, $id_sanpham, '$tensanpham', $soluong, $giaban, $thanhtien, '$trangthai')") 
        or die(mysqli_error($conn));

        header("Location: index.php?admin=hienthihd");
        exit;
    }
}

// --- Xóa ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $check = mysqli_query($conn, "SELECT 1 FROM lichsugiaodich WHERE id_donhang = $id LIMIT 1");

    if (mysqli_num_rows($check) > 0) {
        echo "<script>
            alert('❌ Bạn không thể xóa hóa đơn này vì đã có lịch sử giao dịch!\\nNếu muốn xóa, hãy xóa lịch sử giao dịch trước.');
            window.location='index.php?admin=hienthihd';
        </script>";
        exit;
    } else {
        mysqli_query($conn, "DELETE FROM donhang WHERE id=$id") or die(mysqli_error($conn));
        header("Location: index.php?admin=hienthihd");
        exit;
    }
}

// --- Cập nhật ---
if (isset($_POST['update'])) {
    $id        = intval($_POST['id']);
    $soluong   = intval($_POST['soluong']);
    $giaban    = floatval($_POST['giaban']);
    $thanhtien = $soluong * $giaban;
    $trangthai = $_POST['trangthai'];

    mysqli_query($conn, "UPDATE donhang 
        SET soluong=$soluong, giaban=$giaban, thanhtien=$thanhtien, trangthai='$trangthai'
        WHERE id=$id") or die(mysqli_error($conn));

    header("Location: index.php?admin=hienthihd");
    exit;
}

// --- Thanh tìm kiếm ---
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$where = $search ? "AND (taikhoan.username LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' 
                   OR sanpham.tensp LIKE '%" . mysqli_real_escape_string($conn, $search) . "%')" : "";

// --- Lấy danh sách đơn hàng với tên admin ---
$sql = "SELECT donhang.*, taikhoan.username, sanpham.tensp, lichsugiaodich.anh_nhanhang, a.username AS nhanvien_xacnhan
        FROM donhang
        JOIN taikhoan ON donhang.id_nguoidung = taikhoan.id
        JOIN sanpham ON donhang.id_sanpham = sanpham.id
        LEFT JOIN taikhoan a ON donhang.id_admin = a.id
        LEFT JOIN lichsugiaodich ON donhang.id = lichsugiaodich.id_donhang
        WHERE 1 $where
        ORDER BY donhang.id DESC";

$result = mysqli_query($conn, $sql);

// --- Lấy danh sách user ---
$users = mysqli_query($conn, "SELECT id, username FROM taikhoan WHERE role IN ('user','admin')");

// --- Lấy danh sách sản phẩm ---
$products = mysqli_query($conn, "SELECT id, tensp FROM sanpham");

// --- Upload ảnh ---
if (isset($_POST['upload_img'])) {
    $id = intval($_POST['id']);
    if (isset($_FILES['anh_nhanhang']) && $_FILES['anh_nhanhang']['error'] == 0) {
        $filename = time() . '_' . basename($_FILES['anh_nhanhang']['name']);
        $target = __DIR__ . "/uploads/" . $filename;
        if (move_uploaded_file($_FILES['anh_nhanhang']['tmp_name'], $target)) {
            mysqli_query($conn, "UPDATE donhang SET anh_nhanhang='$filename' WHERE id=$id");
            header("Location: index.php?admin=hienthihd");
            exit;
        } else {
            echo "<script>alert('Upload thất bại! Kiểm tra thư mục uploads có quyền ghi.')</script>";
        }
    }
}
?>

<div class="container">
    <h2>Quản lý hóa đơn</h2>

    <!-- Form thêm -->
    <form method="post">
        <select name="id_nguoidung" required>
            <option value="">-- Khách hàng --</option>
            <?php while ($u = mysqli_fetch_assoc($users)) {
                echo "<option value='".$u['id']."'>".$u['username']."</option>";
            } ?>
        </select>
        <select name="id_sanpham" required>
            <option value="">-- Sản phẩm --</option>
            <?php while ($p = mysqli_fetch_assoc($products)) {
                echo "<option value='".$p['id']."'>".$p['tensp']."</option>";
            } ?>
        </select>
        <input type="number" name="soluong" placeholder="Số lượng" required>
        <select name="trangthai">
            <option value="dang_cho">Đang chờ</option>
            <option value="dang_giao">Đang giao</option>
            <option value="hoan_tat">Hoàn tất</option>
        </select>
        <button type="submit" name="add">➕ Thêm</button>
    </form>

    <!-- Form tìm kiếm -->
    <form method="get" style="margin-bottom:20px; text-align:center;">
        <input type="hidden" name="admin" value="hienthihd">
        <input type="text" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES) ?>" 
               placeholder="🔍 Tìm theo khách hàng hoặc sản phẩm" style="padding:8px;width:300px;">
        <button type="submit" style="padding:8px 14px;">Tìm kiếm</button>
    </form>

    <!-- Bảng hiển thị -->
    <table class="table-order">
        <thead>
            <tr>
                <th>STT</th>
                <th>Khách hàng</th>
                <th>Sản phẩm</th>
                <th>Số lượng</th>
                <th>Giá bán</th>
                <th>Thành tiền</th>
                <th>Trạng thái</th>
                <th>Ảnh nhận hàng</th>
                <th>Người xác nhận</th>
                <th>Ngày đặt</th>
                <th width="200">Hành động</th>
            </tr>
        </thead>
        <tbody>
<?php
if (mysqli_num_rows($result) > 0) {
    $stt = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";

        if (isset($_GET['edit']) && $_GET['edit'] == $row['id']) {
            // Form edit
            echo "<form method='post' enctype='multipart/form-data'>
                    <td>".$stt."<input type='hidden' name='id' value='".$row['id']."'></td>
                    <td>".$row['username']."</td>
                    <td>".$row['tensp']."</td>
                    <td><input type='number' name='soluong' value='".$row['soluong']."' required></td>
                    <td><input type='number' name='giaban' value='".$row['giaban']."' required></td>
                    <td>".number_format($row['soluong']*$row['giaban'],0,",",".")." đ</td>
                    <td>
                        <select name='trangthai'>
                            <option ".($row['trangthai']=="dang_cho"?"selected":"")." value='dang_cho'>Đang chờ</option>
                            <option ".($row['trangthai']=="dang_giao"?"selected":"")." value='dang_giao'>Đang giao</option>
                            <option ".($row['trangthai']=="hoan_tat"?"selected":"")." value='hoan_tat'>Hoàn tất</option>
                        </select>
                    </td>
                    <td>";
            if (!empty($row['anh_nhanhang'])) {
                echo "<img src='uploads/".$row['anh_nhanhang']."' style='width:60px; height:auto; border-radius:5px;'>";
            } else {
                echo "<input type='file' name='anh_nhanhang' accept='image/*' style='width:100px;'>";
            }
            echo "</td>
                  <td>".htmlspecialchars($row['nhanvien_xacnhan'] ?? '')."</td>
                  <td>".$row['ngaydat']."</td>
                  <td>
                      <button type='submit' name='update'>💾 Lưu</button>
                      <a href='index.php?admin=hienthihd' class='cancel'>❌ Hủy</a>
                  </td>
                  </form>";
        } else {
            // Xem bình thường
            echo "<td>".$stt."</td>
                  <td>".$row['username']."</td>
                  <td>".$row['tensp']."</td>
                  <td>".$row['soluong']."</td>
                  <td>".number_format($row['giaban'],0,",",".")." đ</td>
                  <td>".number_format($row['thanhtien'],0,",",".")." đ</td>
                  <td>".$row['trangthai']."</td>
                  <td>";

            $anh = isset($row['anh_nhanhang']) ? $row['anh_nhanhang'] : '';
            $fullPath = $_SERVER['DOCUMENT_ROOT'].'/banhangonline/uploads/'.$anh;

            if (!empty($anh) && file_exists($fullPath)) {
                echo "<img src='/banhangonline/uploads/".htmlspecialchars($anh)."' style='width:60px; height:auto; border-radius:5px;'>";
            } else {
                echo "Chưa có ảnh";
            }

            echo "</td>
                  <td>".htmlspecialchars($row['nhanvien_xacnhan'] ?? '')."</td>
                  <td>".$row['ngaydat']."</td>
                  <td>
                      <a href='index.php?admin=hienthihd&edit=".$row['id']."' class='edit'>✏️ Sửa</a>
                      <a href='index.php?admin=hienthihd&delete=".$row['id']."' class='delete' onclick=\"return confirm('Xóa đơn hàng này?')\">🗑️ Xóa</a>
                  </td>";
        }

        echo "</tr>";
        $stt++;
    }
} else {
    echo "<tr><td colspan='11' class='text-muted'>Không tìm thấy đơn hàng</td></tr>";
}
?>
        </tbody>
    </table>
</div>


<style>
/* CSS giống style danh mục */
body {
    background: #f4f6f9;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 15px;
    color: #333;
}
.container {
    width: 1100px;
    margin: 40px auto;
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0px 2px 8px rgba(0,0,0,0.1);
}
h2 {
    font-weight: 700;
    font-size: 22px;
    text-transform: uppercase;
    color: #2c3e50;
    margin-bottom: 20px;
    text-align: center;
    position: relative;
}
h2::after {
    content: "";
    position: absolute;
    width: 60px;
    height: 4px;
    background: #27ae60;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    border-radius: 2px;
}
form {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    justify-content: center;
}
form input, form select {
    padding: 8px 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
}
form button {
    padding: 8px 14px;
    background: #27ae60;
    border: none;
    border-radius: 6px;
    color: #fff;
    cursor: pointer;
    font-weight: bold;
    transition: 0.3s;
}
form button:hover { background: #1abc9c; }
.table-order {
    width: 100%;
    border-collapse: collapse;
}
.table-order th, .table-order td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}
.table-order th {
    background: #27ae60;
    color: white;
    text-transform: uppercase;
}
.table-order tr:nth-child(even) { background: #f9f9f9; }
.table-order tr:hover { background: #eafaf1; }
a.edit, button[name="update"] {
    background: #f39c12;
    padding: 6px 12px;
    color: #fff;
    border-radius: 5px;
    text-decoration: none;
    margin-right: 5px;
}
a.edit:hover, button[name="update"]:hover { background: #e67e22; }
a.delete {
    background: #e74c3c;
    padding: 6px 12px;
    color: #fff;
    border-radius: 5px;
    text-decoration: none;
}
a.delete:hover { background: #c0392b; }
a.cancel {
    background: #7f8c8d;
    padding: 6px 12px;
    color: #fff;
    border-radius: 5px;
    text-decoration: none;
}
a.cancel:hover { background: #95a5a6; }
.text-muted {
    font-style: italic;
    color: #7f8c8d !important;
}
</style>
