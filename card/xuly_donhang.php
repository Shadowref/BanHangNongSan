<?php

$conn = mysqli_connect("localhost","root","","banhangonline");
mysqli_set_charset($conn,"utf8");

if(!$conn) die("Kết nối thất bại: " . mysqli_connect_error());

$user_id = $_SESSION['user_id'] ?? 0;
if ($user_id == 0) {
    die("Bạn cần đăng nhập để đặt hàng");
}

// Nhận dữ liệu từ form
$phuongthuc = $_POST['phuongthucthanhtoan'] ?? 'cod';
$ngaydat = date("Y-m-d H:i:s");

// Ví dụ dữ liệu đơn hàng (bạn thay bằng dữ liệu thực)
$tensanpham = "Sản phẩm A";
$soluong = 1;
$giaban = 100000;
$tongtien = $soluong * $giaban;

// Lưu vào bảng donhang
$sql = "INSERT INTO donhang(id_nguoidung, tensanpham, soluong, giaban, tongtien, trangthai, phuongthucthanhtoan, ngaydat)
        VALUES ($user_id, '$tensanpham', $soluong, $giaban, $tongtien, 'dang_cho', '$phuongthuc', '$ngaydat')";
if (mysqli_query($conn, $sql)) {
    $id_donhang = mysqli_insert_id($conn);

    // Đồng thời lưu vào lichsugiaodich
    $sql2 = "INSERT INTO lichsugiaodich(id_donhang, id_nguoidung, tensanpham, soluong, giaban, tongtien, trangthai, phuongthucthanhtoan, ngaygiaodich)
             VALUES ($id_donhang, $user_id, '$tensanpham', $soluong, $giaban, $tongtien, 'dang_cho', '$phuongthuc', '$ngaydat')";
    mysqli_query($conn, $sql2);

    // Điều hướng theo phương thức
    if ($phuongthuc == 'momo') {
        header("Location: thanhtoan_momo.php?id=$id_donhang");
        exit;
    } elseif ($phuongthuc == 'zalopay') {
        header("Location: thanhtoan_zalopay.php?id=$id_donhang");
        exit;
    } else {
        echo "<p style='color:green;text-align:center;'>✅ Đặt hàng thành công (Thanh toán khi nhận hàng)!</p>";
    }
} else {
    echo "Lỗi: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
