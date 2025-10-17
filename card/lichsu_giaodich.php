<?php

$conn = mysqli_connect("localhost", "root", "", "banhangonline");
mysqli_set_charset($conn, "utf8");
if (!$conn) die("Kết nối thất bại: " . mysqli_connect_error());

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    die("<p style='text-align:center;color:red;'>Bạn chưa đăng nhập. Vui lòng đăng nhập để xem lịch sử giao dịch.</p>");
}

$user_id = intval($_SESSION['user_id']);

// Xử lý khi người dùng xác nhận hoàn tất đơn
if (isset($_GET['hoantat_id'])) {
    $id_donhang = intval($_GET['hoantat_id']);
    $sql_check = "SELECT * FROM donhang WHERE id = $id_donhang AND id_nguoidung = $user_id AND trangthai = 'dang_giao'";
    $result_check = mysqli_query($conn, $sql_check);
    if ($result_check && mysqli_num_rows($result_check) > 0) {
        mysqli_query($conn, "UPDATE donhang SET trangthai = 'hoan_tat' WHERE id = $id_donhang");
        mysqli_query($conn, "UPDATE lichsugiaodich SET trangthai='hoan_tat' WHERE id_donhang=$id_donhang");
        echo "<p style='color:green;text-align:center;'>✅ Đơn hàng #$id_donhang đã hoàn tất.</p>";
    }
}

// ========= PHÂN TRANG =========
$per_page_options = [5, 10, 20, 50]; 
$per_page = isset($_GET['per_page']) && in_array(intval($_GET['per_page']), $per_page_options) 
    ? intval($_GET['per_page']) : 5;

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $per_page;

// Lấy tổng số bản ghi
$total_sql = "SELECT COUNT(*) as total FROM lichsugiaodich WHERE id_nguoidung = $user_id";
$total_result = mysqli_query($conn, $total_sql);
$total_rows = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_rows / $per_page);

// Truy vấn dữ liệu phân trang
$sql = "
SELECT l.*, t.phone, t.username, t.diachi
FROM lichsugiaodich l
JOIN taikhoan t ON l.id_nguoidung = t.id
WHERE l.id_nguoidung = $user_id
ORDER BY l.ngaygiaodich DESC
LIMIT $per_page OFFSET $offset
";
$result = mysqli_query($conn, $sql);

// ================== GIAO DIỆN ==================
echo '<h2 style="text-align:center;color:#333;margin-bottom:20px;">📦 Lịch sử giao dịch</h2><br>';

// Chọn số bản ghi mỗi trang
echo '<form method="GET" style="text-align:center;margin-bottom:20px;">';
echo '<input type="hidden" name="content" value="lichsu">';
echo 'Hiển thị mỗi trang: 
      <select name="per_page" onchange="this.form.submit()" style="padding:5px 10px;border-radius:5px;">';
foreach ($per_page_options as $opt) {
    $selected = ($per_page == $opt) ? "selected" : "";
    echo "<option value='$opt' $selected>$opt</option>";
}
echo '</select></form>';

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<p style='text-align:center;color:#666;'>Bạn chưa có giao dịch nào</p>";
} else {
    while ($row = mysqli_fetch_assoc($result)) {
        // Lấy tổng điểm đã dùng
        $res_diem = mysqli_query($conn, "SELECT SUM(diem) AS diem_tru FROM lichsu_diem WHERE id_donhang={$row['id_donhang']} AND loai='tieu'");
        $diem_tru = floatval(mysqli_fetch_assoc($res_diem)['diem_tru'] ?? 0);

        // Chọn màu theo trạng thái
        switch ($row['trangthai']) {
            case 'dang_cho': 
                $trangthai_text = '⏳ Đang chờ xác nhận';
                $bg_color = '#fff3cd';
                $border_color = '#ffeaa7';
                break;
            case 'dang_giao': 
                $trangthai_text = '🚚 Đang giao';
                $bg_color = '#d1ecf1';
                $border_color = '#bee5eb';
                break;
            case 'hoan_tat': 
                $trangthai_text = '✅ Hoàn tất';
                $bg_color = '#d4edda';
                $border_color = '#c3e6cb';
                break;
            default: 
                $trangthai_text = '❓ Không xác định';
                $bg_color = '#f8d7da';
                $border_color = '#f5c6cb';
        }

        echo "<div style='border:2px solid $border_color;padding:15px;margin:15px auto;background:$bg_color;border-radius:8px;display:flex;flex-wrap:wrap;align-items:flex-start;width:95%;max-width:1000px;'>";

        // Ảnh sản phẩm
        $duongdan_file = $_SERVER['DOCUMENT_ROOT'] . '/banhangonline/img/' . $row['hinhanh'];
        $duongdan_anh = '/banhangonline/img/' . $row['hinhanh'];
        if (!empty($row['hinhanh']) && file_exists($duongdan_file)) {
            echo "<div style='flex:1 1 200px;min-width:150px;margin-right:15px;margin-bottom:10px;'><img src='{$duongdan_anh}' style='width:100%;height:auto;border-radius:6px;'></div>";
        } else {
            echo "<div style='flex:1 1 200px;min-width:150px;margin-right:15px;margin-bottom:10px;'><img src='/banhangonline/img/default.png' style='width:100%;height:auto;border-radius:6px;'></div>";
        }

        // Thông tin đơn hàng
        $tongtien = $row['tongtien'];
        echo "<div style='flex:2 1 300px;min-width:200px;'> 
            <b>📋 ID Đơn:</b> <span style='color:red;'>{$row['id_donhang']}</span><br>
            <b>👤 Người mua:</b> <span style='color:red;'>".htmlspecialchars($row['username'])."</span><br>
            <b>📞 Số điện thoại:</b> <span style='color:red;'>{$row['phone']}</span><br>
            <b>🏠 Địa chỉ:</b> <span style='color:red;'>".htmlspecialchars($row['diachi'])."</span><br>
            <b>🛒 Sản phẩm:</b> <span style='color:red;'>".htmlspecialchars($row['tensanpham'])."</span><br>
            <b>🔖 Loại:</b> <span style='color:red;'>".htmlspecialchars($row['loai_sanpham'])."</span><br>
            <b>🏷️ Thương hiệu:</b> <span style='color:red;'>".htmlspecialchars($row['thuonghieu'])."</span><br>
            <b>🔢 Số lượng:</b> <span style='color:red;'>{$row['soluong']}</span><br>
            <b>💰 Giá:</b> <span style='color:red;'>".number_format($row['giaban'],0,',','.')."₫</span><br>
            <b>🎯 Điểm đã sử dụng:</b> <span style='color:red;'>{$diem_tru} điểm</span><br>
            <b>💵 Tổng tiền:</b> <span style='color:red;'>".number_format($tongtien,0,',','.')."₫</span><br>
            <b>📅 Ngày đặt:</b> <span style='color:red;'>{$row['ngaygiaodich']}</span><br>
            <b>📊 Trạng thái:</b> <b style='color:".($row['trangthai']=='hoan_tat'?'#28a745':'#e67e22')."'>$trangthai_text</b><br>
        </div></div>";
    }
}

// =========== HIỂN THỊ PHÂN TRANG ==========
if ($total_pages > 1) {
    echo '<div style="text-align:center;margin-top:20px;">';
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = ($i == $page) ? "font-weight:bold;color:red;" : "";
        echo "<a href='index.php?content=lichsu&page=$i&per_page=$per_page' style='margin:0 5px;$active'>$i</a>";
    }
    echo '</div>';
}

mysqli_close($conn);
?>
