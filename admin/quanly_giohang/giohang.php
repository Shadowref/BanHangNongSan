<?php
$conn = mysqli_connect("localhost", "root", "", "banhangonline");
mysqli_set_charset($conn, "utf8");
if (!$conn) die("Kết nối thất bại: " . mysqli_connect_error());

// ====== Thêm giỏ hàng ======
if (isset($_POST['add'])) {
    $taikhoan_id = intval($_POST['taikhoan_id']);
    $sanpham_id  = intval($_POST['sanpham_id']);
    $soluong     = intval($_POST['soluong']);
    $gia         = floatval($_POST['gia']);

    mysqli_query($conn, "INSERT INTO giohang (taikhoan_id, sanpham_id, soluong, gia) 
        VALUES ($taikhoan_id, $sanpham_id, $soluong, $gia)") or die(mysqli_error($conn));

    header("Location: ?admin=giohang");
    exit;
}

// ====== Cập nhật giỏ hàng ======
if (isset($_POST['edit'])) {
    $id          = intval($_POST['id']);
    $taikhoan_id = intval($_POST['taikhoan_id']);
    $sanpham_id  = intval($_POST['sanpham_id']);
    $soluong     = intval($_POST['soluong']);
    $gia         = floatval($_POST['gia']);

    mysqli_query($conn, "UPDATE giohang 
        SET taikhoan_id=$taikhoan_id, sanpham_id=$sanpham_id, soluong=$soluong, gia=$gia 
        WHERE id=$id") or die(mysqli_error($conn));

    header("Location: ?admin=giohang");
    exit;
}

// ====== Xóa ======
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM giohang WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    header("Location: ?admin=giohang");
    exit;
}

// ====== Thanh tìm kiếm sản phẩm ======
$keyword = isset($_GET['search']) ? trim($_GET['search']) : "";

// ====== Lấy dữ liệu giỏ hàng ======
$sql = "SELECT giohang.*, taikhoan.username, taikhoan.avatar AS user_avatar,
               sanpham.tensp, sanpham.hinhanh AS sp_avatar
        FROM giohang 
        JOIN taikhoan ON giohang.taikhoan_id = taikhoan.id 
        JOIN sanpham  ON giohang.sanpham_id  = sanpham.id";

if ($keyword != "") {
    $kw = mysqli_real_escape_string($conn, $keyword);
    $sql .= " WHERE sanpham.tensp LIKE '%$kw%' ";
}

$sql .= " ORDER BY giohang.id DESC";
$result = mysqli_query($conn, $sql);

// Danh sách user và sản phẩm cho select
$users    = mysqli_query($conn, "SELECT id, username FROM taikhoan");
$products = mysqli_query($conn, "SELECT id, tensp FROM sanpham");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý Giỏ hàng</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f4f6f9; }
.container { margin-top: 40px; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
h2 { text-align: center; margin-bottom: 25px; font-weight: 700; color: #2c3e50; }
table th { background: #27ae60; color: #fff; text-transform: uppercase; }
table tr:nth-child(even) { background: #f9f9f9; }
table tr:hover { background: #eafaf1; }
img.avatar { width:40px; height:40px; object-fit:cover; border-radius:50%; }
img.sp-img { width:60px; height:60px; object-fit:cover; border-radius:8px; }
</style>
</head>
<body>
<div class="container">
    <h2>Quản lý Giỏ hàng</h2>

    <!-- Form tìm kiếm -->
    <form class="d-flex mb-3" method="get">
        <input type="hidden" name="admin" value="giohang">
        <input class="form-control me-2" type="search" name="search" placeholder="Tìm sản phẩm..." value="<?= htmlspecialchars($keyword) ?>">
        <button class="btn btn-outline-success" type="submit">🔍 Tìm</button>
    </form>

    <!-- Form thêm / sửa -->
    <form method="post" class="row g-3 mb-4">
        <input type="hidden" name="id" id="id">

        <div class="col-md-3">
            <label class="form-label">Tài khoản</label>
            <select name="taikhoan_id" id="taikhoan_id" class="form-select" required>
                <option value="">-- Chọn tài khoản --</option>
                <?php mysqli_data_seek($users, 0);
                while ($u = mysqli_fetch_assoc($users)) { ?>
                    <option value="<?= $u['id']; ?>"><?= $u['username']; ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Sản phẩm</label>
            <select name="sanpham_id" id="sanpham_id" class="form-select" required>
                <option value="">-- Chọn sản phẩm --</option>
                <?php mysqli_data_seek($products, 0);
                while ($p = mysqli_fetch_assoc($products)) { ?>
                    <option value="<?= $p['id']; ?>"><?= $p['tensp']; ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Số lượng</label>
            <input type="number" name="soluong" id="soluong" class="form-control" value="1" required>
        </div>

        <div class="col-md-2">
            <label class="form-label">Giá</label>
            <input type="number" name="gia" id="gia" class="form-control" required>
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" name="add" class="btn btn-success w-100">➕ Thêm</button>
        </div>
    </form>

    <!-- Bảng hiển thị -->
    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th>STT</th>
                <th>Tài khoản</th>
                <th>Sản phẩm</th>
                <th>Số lượng</th>
                <th>Giá</th>
                <th>Tổng tiền</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        $tongcong = 0;
        if (mysqli_num_rows($result) > 0) {
            $stt = 1;
            while ($row = mysqli_fetch_assoc($result)) { 
                $thanhtien = $row['soluong'] * $row['gia'];
                $tongcong += $thanhtien;
                ?>
                <tr>
                    <td><?= $stt++; ?></td>
                    <td>
                      
                        <?= htmlspecialchars($row['username']); ?>
                    </td>
                    <td>
                     
                        <?= htmlspecialchars($row['tensp']); ?>
                    </td>
                    <td><?= $row['soluong']; ?></td>
                    <td><?= number_format($row['gia'],0,",",".") ?> đ</td>
                    <td><?= number_format($thanhtien,0,",",".") ?> đ</td>
                    <td><?= $row['ngay_tao']; ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="editRow(
                            '<?= $row['id']; ?>',
                            '<?= $row['taikhoan_id']; ?>',
                            '<?= $row['sanpham_id']; ?>',
                            '<?= $row['soluong']; ?>',
                            '<?= $row['gia']; ?>'
                        )">✏️ Sửa</button>
                        <a href="?admin=giohang&delete=<?= $row['id']; ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Bạn có chắc muốn xóa?')">🗑️ Xóa</a>
                    </td>
                </tr>
        <?php }} else {
            echo "<tr><td colspan='8' class='text-center text-muted'>Chưa có dữ liệu giỏ hàng</td></tr>";
        } ?>
        </tbody>
        <?php if ($tongcong > 0) { ?>
        <tfoot>
            <tr>
                <th colspan="5" class="text-end">Tổng cộng:</th>
                <th colspan="3" class="text-start text-danger fw-bold"><?= number_format($tongcong,0,",",".") ?> đ</th>
            </tr>
        </tfoot>
        <?php } ?>
    </table>
</div>

<script>
function editRow(id, taikhoan_id, sanpham_id, soluong, gia) {
    document.getElementById('id').value = id;
    document.getElementById('taikhoan_id').value = taikhoan_id;
    document.getElementById('sanpham_id').value = sanpham_id;
    document.getElementById('soluong').value = soluong;
    document.getElementById('gia').value = gia;

    let btn = document.querySelector("button[name='add']");
    btn.innerText = "💾 Cập nhật";
    btn.name = "edit";
    btn.classList.remove("btn-success");
    btn.classList.add("btn-primary");
}
</script>
</body>
</html>
