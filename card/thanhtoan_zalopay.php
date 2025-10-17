<?php
session_start();
$conn = mysqli_connect("localhost","root","", "banhangonline");
mysqli_set_charset($conn,"utf8");
if(!$conn) die("Kết nối thất bại: ".mysqli_connect_error());

// --- Lấy thông tin đơn hàng từ URL ---
$orderId = intval($_GET['order_id'] ?? 0);
$amount = intval($_GET['amount'] ?? 0);
$status = $_GET['status'] ?? '';

// --- Xử lý khi nhấn "Đã thanh toán" ---
if($status === 'success' && $orderId){
    $stmt = $conn->prepare("UPDATE donhang SET trangthai='dang_cho', thanhtien=0 WHERE id=?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $statusMsg = "✅ Thanh toán thành công! Số tiền đã được trừ.";
} elseif($status === 'failed'){
    $statusMsg = "❌ Thanh toán thất bại!";
}

// --- Nếu chưa có status, hiển thị QR code ZaloPay ---
if(!$status){
    // --- Cấu hình ZaloPay ---
    $appid = 2553; // Thay bằng AppID của bạn
    $key1 = "your_key1_here";
    $key2 = "your_key2_here";
    $endpoint = "https://sandbox.zalopay.com.vn/v001/tpe/createorder";

    $transID = rand(100000, 999999);
    $apptransid = date("ymd")."_".$transID;
    $order_data = [
        "appid" => $appid,
        "apptransid" => $apptransid,
        "appuser" => "demo",
        "apptime" => round(microtime(true) * 1000),
        "item" => "[]",
        "embeddata" => json_encode(["order_id"=>$orderId]),
        "amount" => $amount,
        "description" => "Thanh toán đơn hàng #$orderId qua ZaloPay",
        "bankcode" => "zalopayapp",
    ];

    // Tạo chữ ký HMAC
    $data = $order_data["appid"]."|".$order_data["apptransid"]."|".$order_data["appuser"]."|".$order_data["amount"]."|".$order_data["apptime"]."|".$order_data["embeddata"]."|".$order_data["item"];
    $order_data["mac"] = hash_hmac("sha256", $data, $key1);

    // Gửi API
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($order_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    $jsonResult = json_decode($result, true);
    $orderUrl = $jsonResult['orderurl'] ?? '';

    echo "
    <div style='max-width:400px;margin:50px auto;padding:20px;background:#0072c6;border-radius:20px;text-align:center;font-family:Arial,sans-serif;color:#fff;box-shadow:0 4px 12px rgba(0,0,0,0.2);'>
        
        <!-- Logo ZaloPay -->
        <div style='margin-bottom:20px;'>
            <img src='../img/zalopaywebp.webp' 
                 style='width:80px;height:80px;margin:0 auto;display:block;'>
        </div>

        <h2 style='margin-bottom:10px;font-size:20px;'>Thanh toán đơn hàng #$orderId</h2>
        <p style='font-size:18px;margin-bottom:20px;'>
            Số tiền cần thanh toán: <b>".number_format($amount,0,',','.')."đ</b>
        </p>

        <p style='font-size:14px;margin-bottom:20px;'>
            Hoặc chuyển khoản vào số tài khoản: <b>0325581015</b><br>
            <i>Ghi rõ: Mã đơn hàng #$orderId</i>
        </p>

       <div style='margin:20px 0;text-align:center;'>
    <img src='https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=DemoQR' 
         alt='QR code' style='border-radius:15px;'>
</div>


        <a href='?order_id=$orderId&amount=$amount&status=success' 
           style='display:inline-block;padding:12px 25px;background:#fff;color:#0072c6;font-weight:bold;text-decoration:none;border-radius:12px;margin-top:10px;'>
            Đã thanh toán
        </a>
    </div>";
    exit;
}

// --- Lấy thông tin sản phẩm trong đơn hàng ---
$stmt = $conn->prepare("SELECT tensanpham, hinhanh, soluong, giaban, thanhtien FROM donhang WHERE id=?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();
$products = [];
$tong_tien = 0;
while($row = $result->fetch_assoc()){
    $products[] = $row;
    $tong_tien += intval($row['thanhtien']);
}

?>

<div style="max-width:800px;margin:50px auto;font-family:Arial,sans-serif;">

    <!-- Thông báo trạng thái thanh toán -->
    <div style="padding:20px;border-radius:12px;text-align:center;
                background:<?= $status==='success'?'#d4edda':'#f8d7da' ?>;
                color:<?= $status==='success'?'#155724':'#721c24' ?>;
                font-size:18px;
                box-shadow:0 4px 12px rgba(0,0,0,0.1);
                transition: all 0.3s;">
        <?= $statusMsg ?? '' ?>
    </div>

    <!-- Thông tin đơn hàng -->
    <div style="margin-top:20px;padding:25px;background:#fff;border-radius:12px;
                box-shadow:0 6px 15px rgba(0,0,0,0.08);">
        <h2 style="text-align:center;color:#333;">Đơn hàng #<b><?= htmlspecialchars($orderId) ?></b></h2>
        <p style="text-align:center;font-size:16px;color:#555;">Số tiền: <b><?= number_format($tong_tien,0,',','.') ?>đ</b></p>
    </div>

    <!-- Danh sách sản phẩm -->
    <div style="margin-top:35px;">
        <h3 style="color:#333;border-bottom:2px solid #27ae60;padding-bottom:5px;">Sản phẩm:</h3>
        <?php foreach($products as $sp): 
            $sp_tt = intval($sp['thanhtien']);
        ?>
        <div style="display:flex;align-items:center;margin:15px 0;padding:15px;background:#fff;border-radius:12px;
                    box-shadow:0 4px 12px rgba(0,0,0,0.08);transition:all 0.3s;">
            <img src="../img/<?= htmlspecialchars($sp['hinhanh']) ?>" 
                 style="width:90px;height:90px;object-fit:cover;border-radius:12px;margin-right:20px;">
            <div style="flex:1;">
                <p style="margin:0;font-weight:600;font-size:16px;color:#333;"><?= htmlspecialchars($sp['tensanpham']) ?></p>
                <p style="margin:3px 0;color:#555;">Giá: <b><?= number_format($sp['giaban'],0,',','.') ?>đ</b></p>
                <p style="margin:3px 0;color:#555;">Số lượng: <b><?= $sp['soluong'] ?></b></p>
                <p style="margin:3px 0;color:#555;">Thành tiền: <b><?= number_format($sp_tt,0,',','.') ?>đ</b></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Tổng tiền & nút -->
    <div style="margin-top:25px;text-align:right;">
        <p style="font-size:18px;color:#333;font-weight:600;">Tổng tiền: <?= number_format($tong_tien,0,',','.') ?>đ</p>
    </div>

    <div style="text-align:center;margin-top:25px;">
        <a href="../index.php?content=giohang"
           style="padding:12px 25px;background:#2980b9;color:#fff;text-decoration:none;
                  border-radius:12px;margin-right:10px;box-shadow:0 4px 12px rgba(0,0,0,0.15);transition:all 0.3s;">
            Quay lại giỏ hàng
        </a>
        <a href="../index.php"
           style="padding:12px 25px;background:#27ae60;color:#fff;text-decoration:none;
                  border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.15);transition:all 0.3s;">
            Trang chủ
        </a>
    </div>
</div>
