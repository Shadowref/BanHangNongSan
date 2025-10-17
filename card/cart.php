<?php
session_start();

// Kết nối CSDL
$conn = mysqli_connect("localhost", "root", "", "banhangonline");
mysqli_set_charset($conn, "utf8");
if (!$conn) die("Kết nối thất bại: " . mysqli_connect_error());

// Xử lý khi POST thêm giỏ hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $sanpham_id = (int)($_POST['id'] ?? 0);
    $tensp = trim($_POST['tensp'] ?? '');
    $gia = (float)($_POST['gia'] ?? 0);
    $soluong = max(1, (int)($_POST['soluong'] ?? 1));
    $action = $_POST['action'] ?? '';

    if ($sanpham_id <= 0 || $gia <= 0 || $tensp === '') {
        die("Dữ liệu sản phẩm không hợp lệ.");
    }

    // Nếu chưa đăng nhập -> lưu tạm vào session
    if (!isset($_SESSION['user_id'])) {
        if (!isset($_SESSION['cart_temp'])) $_SESSION['cart_temp'] = [];

        if (isset($_SESSION['cart_temp'][$sanpham_id])) {
            $_SESSION['cart_temp'][$sanpham_id]['soluong'] += $soluong;
        } else {
            $_SESSION['cart_temp'][$sanpham_id] = [
                'tensp' => $tensp,
                'gia' => $gia,
                'soluong' => $soluong
            ];
        }

        die("
        <div style='
            max-width:500px;
            margin:50px auto;
            padding:20px;
            border:2px solid #f5c2c7;
            border-radius:10px;
            background:#f8d7da;
            color:#842029;
            font-family:Arial, sans-serif;
            text-align:center;
            box-shadow:0 4px 10px rgba(0,0,0,0.1);
        '>
            <h2 style='margin-bottom:15px;'>⚠️ Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng</h2>
            <a href='../index.php?content=dangnhap' style='
                display:inline-block;
                padding:10px 20px;
                background:#dc3545;
                color:#fff;
                text-decoration:none;
                font-weight:bold;
                border-radius:5px;
                transition:0.3s;
            ' onmouseover=\"this.style.background='#bb2d3b'\" onmouseout=\"this.style.background='#dc3545'\">
                🔑 Đăng nhập ngay
            </a>
        </div>
        ");
    }

    // Nếu đã đăng nhập -> thêm sản phẩm vào CSDL
    $taikhoan_id = (int)$_SESSION['user_id'];

    // Hàm thêm sản phẩm vào giỏ
    function addToCart($conn, $taikhoan_id, $sanpham_id, $soluong, $gia) {
        $stmt_check = $conn->prepare("SELECT soluong FROM giohang WHERE taikhoan_id=? AND sanpham_id=?");
        $stmt_check->bind_param("ii", $taikhoan_id, $sanpham_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $stmt_update = $conn->prepare("UPDATE giohang SET soluong = soluong + ? WHERE taikhoan_id=? AND sanpham_id=?");
            $stmt_update->bind_param("iii", $soluong, $taikhoan_id, $sanpham_id);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            $stmt_insert = $conn->prepare("INSERT INTO giohang (taikhoan_id, sanpham_id, soluong, gia) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("iiid", $taikhoan_id, $sanpham_id, $soluong, $gia);
            $stmt_insert->execute();
            $stmt_insert->close();
        }

        $stmt_check->close();
    }

    // Thêm sản phẩm hiện tại
    addToCart($conn, $taikhoan_id, $sanpham_id, $soluong, $gia);

    // Nếu có sản phẩm tạm trong session -> chuyển hết vào CSDL
    if (isset($_SESSION['cart_temp'])) {
        foreach ($_SESSION['cart_temp'] as $sp_id => $sp) {
            addToCart($conn, $taikhoan_id, $sp_id, $sp['soluong'], $sp['gia']);
        }
        unset($_SESSION['cart_temp']); // xóa session tạm sau khi chuyển
    }

    // Xử lý chuyển hướng
    if ($action === 'buy_now' || $action === 'add') {
        echo "<script>
            alert('✅ Sản phẩm đã được thêm vào giỏ hàng!');
            window.location.href='../index.php?content=giohang';
        </script>";
        exit();
    }
}
?>
