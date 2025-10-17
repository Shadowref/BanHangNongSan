<?php
$conn = mysqli_connect("localhost", "root", "", "banhangonline");
mysqli_set_charset($conn, "utf8");
if (!$conn) die("Kết nối thất bại: " . mysqli_connect_error());

// --- Thêm ---
if (isset($_POST['add'])) {
    $tenloai = trim($_POST['tenloai']);
    if ($tenloai != "") {
        mysqli_query($conn, "INSERT INTO loaisanpham (tenloai) VALUES ('$tenloai')") or die(mysqli_error($conn));

        // Thông báo
        $msg = "Thêm loại sản phẩm mới: '$tenloai'";
        mysqli_query($conn, "INSERT INTO thongbao (user_id, message) VALUES (1, '".mysqli_real_escape_string($conn,$msg)."')");

        header("Location: index.php?admin=hienthidm");
        exit;
    }
}

// --- Xóa ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $res = mysqli_query($conn, "SELECT tenloai FROM loaisanpham WHERE id=$id");
    $row = mysqli_fetch_assoc($res);
    $tenloai = $row ? $row['tenloai'] : '';

    mysqli_query($conn, "DELETE FROM loaisanpham WHERE id=$id") or die(mysqli_error($conn));

    // Thông báo
    if ($tenloai != "") {
        $msg = "Đã xóa loại sản phẩm: '$tenloai'";
        mysqli_query($conn, "INSERT INTO thongbao (user_id, message) VALUES (1, '".mysqli_real_escape_string($conn,$msg)."')");
    }

    header("Location: index.php?admin=hienthidm");
    exit;
}

// --- Cập nhật ---
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $tenloai = trim($_POST['tenloai']);

    // Lấy tên cũ
    $res = mysqli_query($conn, "SELECT tenloai FROM loaisanpham WHERE id=$id");
    $row = mysqli_fetch_assoc($res);
    $oldName = $row ? $row['tenloai'] : '';

    if ($tenloai != "") {
        mysqli_query($conn, "UPDATE loaisanpham SET tenloai='$tenloai' WHERE id=$id") or die(mysqli_error($conn));

        // Thông báo
        if ($oldName != "" && $oldName != $tenloai) {
            $msg = "Đã sửa tên loại sản phẩm từ '$oldName' thành '$tenloai'";
        } else {
            $msg = "Cập nhật loại sản phẩm: '$tenloai'";
        }
        mysqli_query($conn, "INSERT INTO thongbao (user_id, message) VALUES (1, '".mysqli_real_escape_string($conn,$msg)."')");

        header("Location: index.php?admin=hienthidm");
        exit;
    }
}

// --- Thanh tìm kiếm ---
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$where = $search ? "WHERE tenloai LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'" : "";

// Lấy dữ liệu
$result = mysqli_query($conn, "SELECT * FROM loaisanpham $where ORDER BY id DESC");
?>



<div class="container">
    <h2>Quản lý loại sản phẩm</h2>

    <!-- Form thêm -->
    <form method="post">
        <input type="text" name="tenloai" placeholder="Nhập tên danh mục..." required>
        <button type="submit" name="add">➕ Thêm</button>
    </form>

    <!-- Form tìm kiếm -->
    <form method="get" style="margin-bottom:20px; text-align:center;">
        <input type="hidden" name="admin" value="hienthidm">
        <input type="text" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES) ?>" placeholder="🔍 Tìm loại sản phẩm..." style="padding:8px;width:300px;">
        <button type="submit" style="padding:8px 14px;">Tìm kiếm</button>
    </form>

    <!-- Bảng hiển thị -->
    <table class="table-order">
        <thead>
            <tr>
                <th width="60">STT</th>
                <th>Tên loại sản phẩm</th>
                <th width="200">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                $stt = 1;
                while ($row = mysqli_fetch_assoc($result)) {
                    if (isset($_GET['edit']) && $_GET['edit'] == $row['id']) {
                        echo "<tr>
                                <form method='post'>
                                    <td class='text-center'>".$stt."
                                        <input type='hidden' name='id' value='".$row['id']."'>
                                    </td>
                                    <td><input type='text' name='tenloai' value='".htmlspecialchars($row['tenloai'])."' required></td>
                                    <td>
                                        <button type='submit' name='update'>💾 Lưu</button>
                                        <a href='index.php?admin=hienthidm' class='cancel'>❌ Hủy</a>
                                    </td>
                                </form>
                              </tr>";
                    } else {
                        echo "<tr>
                                <td>".$stt."</td>
                                <td>".htmlspecialchars($row['tenloai'])."</td>
                                <td>
                                    <a href='index.php?admin=hienthidm&edit=".$row['id']."' class='edit'>✏️ Sửa</a>
                                    <a href='index.php?admin=hienthidm&delete=".$row['id']."' class='delete' onclick=\"return confirm('Bạn có chắc muốn xóa?')\">🗑️ Xóa</a>
                                </td>
                              </tr>";
                    }
                    $stt++;
                }
            } else {
                echo "<tr><td colspan='3' class='text-muted'>Không tìm thấy danh mục nào</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<style>
/* Tổng thể */
body {
    background: #f4f6f9;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 15px;
    color: #333;
}
.container {
    width: 900px;
    margin: 40px auto;
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0px 2px 8px rgba(0,0,0,0.1);
}

/* Tiêu đề */
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

/* Form thêm */
form {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-bottom: 10px;
}
form input {
    flex: 1;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
}
form button {
    padding: 10px 18px;
    background: #27ae60;
    border: none;
    border-radius: 6px;
    color: #fff;
    cursor: pointer;
    font-weight: bold;
    transition: 0.3s;
}
form button:hover { background: #1abc9c; }

/* Bảng */
.table-order {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}
.table-order th, .table-order td {
    border: 1px solid #ddd;
    padding: 12px;
    text-align: center;
}
.table-order th {
    background: #27ae60;
    color: white;
    text-transform: uppercase;
    font-size: 14px;
}
.table-order tr:nth-child(even) { background: #f9f9f9; }
.table-order tr:hover { background: #eafaf1; }

/* Nút hành động */
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

/* Text muted */
.text-muted {
    font-style: italic;
    color: #7f8c8d !important;
}
</style>
