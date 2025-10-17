<?php

require_once 'vendor/autoload.php'; // PHPMailer
        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\Exception;

$conn = mysqli_connect("localhost", "root", "", "banhangonline");
mysqli_set_charset($conn, "utf8");
if (!$conn) die("Kết nối thất bại: " . mysqli_connect_error());

if (!isset($_SESSION['user_id'])) {
    die("<p style='text-align:center;color:red;'>Bạn chưa đăng nhập. Vui lòng đăng nhập để quản lý đơn hàng của mình.</p>");
}

$user_id = intval($_SESSION['user_id']);

// CSS trạng thái
echo "
<style>
.status { padding: 4px 10px; border-radius: 12px; font-weight: bold; color: #fff; }
.status-dangcho { background: #f1c40f; }
.status-danggiao { background: #e67e22; }
.status-hoantat { background: #27ae60; }
.status-dahuy { background: #95a5a6; }
.order-card { display:flex; flex-wrap:wrap; align-items:flex-start; border-radius:8px; padding:15px; margin:15px auto; width:90%; max-width:none; box-shadow:0 3px 8px rgba(0,0,0,0.1); box-sizing:border-box; }
.order-img { flex:0 0 120px; margin-right:15px; } .order-img img { width:120px; border-radius:6px; }
.order-info { flex:1; font-size:14px; } .order-info b { color:#333; } .order-info span { color:#555; }
.order-actions { display:flex; flex-direction:column; gap:10px; margin-left:15px; } .order-actions button { padding:8px 15px; border:none; border-radius:6px; cursor:pointer; }
.button-confirm { background:#27ae60;color:#fff; } .button-cancel { background:#c0392b;color:#fff; }
@media (max-width:600px) { .order-card { flex-direction:column; align-items:center; text-align:center; } .order-img { margin-bottom:10px; } .order-actions { margin-left:0; flex-direction:row; gap:10px; justify-content:center; } }
</style>
";
// ================= PHÂN TRANG =================
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
$page  = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1; 
$offset = ($page - 1) * $limit;

$res_count = mysqli_query($conn, "SELECT COUNT(*) AS total FROM donhang WHERE id_nguoidung=$user_id");
$row_count = mysqli_fetch_assoc($res_count);
$total_orders = intval($row_count['total']);
$total_pages = ($total_orders > 0) ? ceil($total_orders / $limit) : 1;

echo "<form method='get' style='text-align:center;margin-bottom:10px;'>
        <input type='hidden' name='content' value='donhanguser'>
        Hiển thị: 
        <select name='limit' onchange='this.form.submit()'>
            <option ".($limit==5?'selected':'').">5</option>
            <option ".($limit==10?'selected':'').">10</option>
            <option ".($limit==20?'selected':'').">20</option>
        </select> đơn / trang
      </form>";


// --- Xử lý POST ---
if(isset($_POST['xacnhan_id'])){
    $id_donhang = intval($_POST['xacnhan_id']);
    $donhang = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT d.*, t.username, t.email, t.diachi, t.phone,
           s.tensp, s.hinhanh, s.gia AS giaban,
           l.tenloai AS loai_sanpham, th.tenthuonghieu AS thuonghieu
    FROM donhang d
    JOIN taikhoan t ON d.id_nguoidung=t.id
    JOIN sanpham s ON d.id_sanpham = s.id
    LEFT JOIN loaisanpham l ON s.id_loai=l.id
    LEFT JOIN thuonghieu th ON s.id_thuonghieu=th.id
    WHERE d.id=$id_donhang AND d.id_nguoidung=$user_id
"));

    if(!$donhang) die("<p style='text-align:center;color:red;'>Đơn hàng không tồn tại!</p>");

    $soluong = max(1,intval($donhang['soluong']));
    $giaban_thucte = floatval($donhang['giaban']);
    $result_diem = mysqli_query($conn,"SELECT SUM(diem) AS diem_tru FROM lichsu_diem WHERE id_donhang={$donhang['id']} AND loai='tieu'");
    $diem_tru = floatval(mysqli_fetch_assoc($result_diem)['diem_tru'] ?? 0);
    $tongtien_thucte = $giaban_thucte * $soluong - $diem_tru;

    if($donhang['trangthai']=='dang_cho'){
        $trangthai_new = 'dang_giao';
        $stmt = $conn->prepare("UPDATE donhang SET trangthai=?, giaban=?, thanhtien=? WHERE id=?");
        $stmt->bind_param("sddi", $trangthai_new, $giaban_thucte, $tongtien_thucte, $id_donhang);
        $stmt->execute();
        echo "<p style='text-align:center;color:blue;'>🚚 Đơn hàng #$id_donhang đang giao.</p>";
    } elseif($donhang['trangthai']=='dang_giao'){
    $trangthai_new = 'hoan_tat';
    $anh_nhanhang = null;
    if(isset($_FILES['anh_nhanhang']) && $_FILES['anh_nhanhang']['error']==0){
$ext = pathinfo($_FILES['anh_nhanhang']['name'], PATHINFO_EXTENSION);
$anh_nhanhang = 'nhanhang_'.$id_donhang.'_'.time().'.'.$ext;

// Luôn lưu vào banhangonline/uploads
$upload_dir = dirname(__DIR__) . '/uploads/'; 
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$upload_file = $upload_dir . $anh_nhanhang;

if (move_uploaded_file($_FILES['anh_nhanhang']['tmp_name'], $upload_file)) {
    echo "<p style='color:green;'>Ảnh đã được lưu thành công tại: $upload_file</p>";
} else {
    echo "<p style='color:red;'>Không thể lưu ảnh vào thư mục uploads!</p>";
}




    }

    $stmt = $conn->prepare("UPDATE donhang SET trangthai=?, giaban=?, thanhtien=? WHERE id=?");
    $stmt->bind_param("sddi", $trangthai_new, $giaban_thucte, $tongtien_thucte, $id_donhang);
    $stmt->execute();

    $stmt = $conn->prepare("INSERT INTO lichsugiaodich 
        (id_donhang, id_nguoidung, id_sanpham, tensanpham, hinhanh, soluong, giaban, tongtien, ngaygiaodich, trangthai, ngaydat, diem_sudung, anh_nhanhang, phuongthucthanhtoan, loai_sanpham, thuonghieu) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "iiissddssssssss",
        $id_donhang,
        $donhang['id_nguoidung'],
        $donhang['id_sanpham'],
        $donhang['tensp'],
        $donhang['hinhanh'],
        $soluong,
        $giaban_thucte,
        $tongtien_thucte,
        $trangthai_new,
        $donhang['ngaydat'],
        $diem_tru,
        $anh_nhanhang,
        $donhang['phuongthucthanhtoan'],
        $donhang['loai_sanpham'],
        $donhang['thuonghieu']
    );
    $stmt->execute();

    // Cộng điểm thưởng 1%
    $diem_thuong = floor($tongtien_thucte*0.01);
    if($diem_thuong>0){
        mysqli_query($conn,"UPDATE taikhoan SET diem=diem+$diem_thuong WHERE id=".$donhang['id_nguoidung']);
    }
    echo "<p style='text-align:center;color:green;'>✅ Đơn hàng #$id_donhang hoàn tất. Điểm thưởng: $diem_thuong.</p>";

    // ======= Gửi email xác nhận =======
    $user_email = $donhang['email'] ?? '';
    $user_name  = $donhang['username'] ?? '';

    if($user_email){
    
        try {
            $mail = new PHPMailer(true);
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'suongnie2k5@gmail.com';  // email gửi
            $mail->Password   = 'heqwfsjnixruxjtw';     // App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('suongnie2k5@gmail.com','TheGioiNongSan');
            $mail->addAddress($user_email, $user_name);

           $mail->isHTML(true);
            $mail->Subject = "Đơn hàng #$id_donhang hoàn tất!";
            $mail->AddEmbeddedImage('img/logochinh.jpg', 'logo_cid'); // thêm logo

// Nội dung email đẹp hơn
                    $mail->Body = "
                <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto; border:1px solid #e0e0e0; border-radius:15px; overflow:hidden;'>
                    <!-- Header -->
                <div style='background: linear-gradient(135deg, #28a745, #85e085); padding:20px; text-align:center; color:white;'>
                        <img src='cid:logo_cid' alt='Logo' style='width:80px; margin-bottom:10px; border-radius:10px;'>
                        <h2>Đơn hàng #$id_donhang đã hoàn tất!</h2>
                    </div>

                    <!-- Nội dung chính -->
                    <div style='padding:20px; background:#f9f9f9; color:#333;'>
                        <p>Chào <strong>$user_name</strong>,</p>
                        <p>Cảm ơn bạn đã mua hàng tại <strong>TheGioiNongSan</strong>! Dưới đây là chi tiết đơn hàng của bạn:</p>

                        <div style='padding:15px; background:#eafaf1; border-radius:10px;'>
                            <p><strong>Sản phẩm:</strong> {$donhang['tensp']}</p>
                            <p><strong>Loại:</strong> {$donhang['loai_sanpham']}</p>
                            <p><strong>Thương hiệu:</strong> {$donhang['thuonghieu']}</p>
                            <p><strong>Số lượng:</strong> $soluong</p>
                            <p><strong>Tổng tiền:</strong> <span style='color:#28a745; font-weight:bold;'>".number_format($tongtien_thucte,0,',','.')."đ</span></p>
                            <p><strong>Phương thức thanh toán:</strong> ";

                        // Xử lý màu sắc, icon và text hiển thị cho phương thức thanh toán
               
$pt = strtolower($donhang['phuongthucthanhtoan']);
switch($pt){
    case 'cod':
        $pt_icon  = '💵';
        $pt_color = '#f39c12';
        $pt_text  = 'Thanh toán tiền mặt';
        break;
    case 'momo':
        $pt_icon  = '📱';
        $pt_color = '#8e44ad';
        $pt_text  = 'Momo';
        break;
    case 'zalopay':
        $pt_icon  = '💳';
        $pt_color = '#2ecc71';
        $pt_text  = 'ZaloPay';
        break;
    case 'visa':
        $pt_icon  = '💳';
        $pt_color = '#3498db';
        $pt_text  = 'Visa/MasterCard';
        break;
    case 'vnpay':
        $pt_icon  = '🏦';
        $pt_color = '#e74c3c';
        $pt_text  = 'VNPay';
        break;
    default:
        $pt_icon  = '❔';
        $pt_color = '#7f8c8d';
        $pt_text  = $donhang['phuongthucthanhtoan'];
}

               // switch đã gán $pt_icon, $pt_color, $pt_text
$mail->Body .= "<p><strong>Phương thức thanh toán:</strong> <span style='color:$pt_color; font-weight:bold;'>$pt_icon $pt_text</span></p>";


                $mail->Body .= "</p>
                        </div>

                        <hr style='margin:20px 0; border:none; border-top:1px solid #ccc;'>

                        <p style='font-size:12px; color:#666;'>Đây là email tự động từ TheGioiNongSan. Vui lòng không trả lời trực tiếp.</p>
                    </div>
                </div>
                ";

                $mail->send();

            echo "<p style='text-align:center;color:green;'>$user_email</p>";
        } catch (Exception $e){
            echo "<p style='text-align:center;color:red;'>⚠ Không gửi được email: ".$mail->ErrorInfo."</p>";
        }
    }
}

}

// Xử lý hủy đơn
if(isset($_POST['huydon_id'])){
    $id_donhang = intval($_POST['huydon_id']);
    $donhang = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM donhang WHERE id=$id_donhang AND id_nguoidung=$user_id"));
    if($donhang && $donhang['trangthai']=='dang_cho'){
        mysqli_query($conn, "UPDATE donhang SET trangthai='da_huy' WHERE id=$id_donhang");
        echo "<p style='text-align:center;color:red;'>❌ Đơn hàng #$id_donhang đã hủy thành công!</p>";
    }
}

// Hiển thị danh sách đơn hàng
$result = mysqli_query($conn, "
    SELECT d.*, t.username, t.diachi, t.phone,
           s.tensp, s.hinhanh, s.gia AS giaban,
           l.tenloai AS loai_sanpham, th.tenthuonghieu AS thuonghieu
    FROM donhang d
    JOIN taikhoan t ON d.id_nguoidung=t.id
    JOIN sanpham s ON d.id_sanpham=s.id
    LEFT JOIN loaisanpham l ON s.id_loai=l.id
    LEFT JOIN thuonghieu th ON s.id_thuonghieu=th.id
    WHERE d.id_nguoidung=$user_id
    ORDER BY d.ngaydat DESC
    LIMIT $limit OFFSET $offset
");

$res_sum = mysqli_query($conn, "SELECT SUM(soluong) AS total_items 
                                FROM donhang 
                                WHERE id_nguoidung=$user_id");
$row_sum = mysqli_fetch_assoc($res_sum);
$total_items = intval($row_sum['total_items'] ?? 0);
echo "<p style='text-align:center;color:#555;'>
        Tổng số đơn hàng: <b>$total_orders</b> | 
      </p>";

echo "<div style='text-align:center; margin:20px 0;'>
        <h2>📦 Đơn hàng của bạn</h2>
        <p style='color:#555;'>Tổng số đơn hàng: <b>$total_orders</b></p>
      </div>";






if (!$result || mysqli_num_rows($result) == 0) {
    echo "<p style='text-align:center;color:#888;'>Bạn chưa có đơn hàng nào</p>";
} else {
    while($row = mysqli_fetch_assoc($result)){
        $soluong = max(1,intval($row['soluong']));
        $giaban_thucte = floatval($row['giaban']);
        $result_diem = mysqli_query($conn,"SELECT SUM(diem) AS diem_tru FROM lichsu_diem WHERE id_donhang={$row['id']} AND loai='tieu'");
        $diem_tru = floatval(mysqli_fetch_assoc($result_diem)['diem_tru'] ?? 0);
        $tongtien_thucte = $giaban_thucte * $soluong - $diem_tru;

        switch($row['trangthai']){
            case 'dang_cho': $trangthai_text="<span class='status status-dangcho'>Đang chờ</span>"; break;
            case 'dang_giao': $trangthai_text="<span class='status status-danggiao'>Đang giao</span>"; break;
            case 'hoan_tat': $trangthai_text="<span class='status status-hoantat'>Hoàn tất</span>"; break;
            default: $trangthai_text="<span class='status status-dahuy'>Đã hủy</span>";
        }

        $pt = strtolower($row['phuongthucthanhtoan']);
        switch($pt){
            case 'cod': $pt_color='background:#fff;color:#000;border:1px solid #ccc;'; $pt_icon='💵'; break;
            case 'momo': $pt_color='background:#8e44ad;color:#fff;'; $pt_icon='📱'; break;
            case 'zalopay': $pt_color='background:#2ecc71;color:#fff;'; $pt_icon='💳'; break;
            case 'visa': $pt_color='background:#3498db;color:#fff;'; $pt_icon='💳'; break;
            case 'vnpay': $pt_color='background:#e74c3c;color:#fff;'; $pt_icon='🏦'; break;
            default: $pt_color='background:#7f8c8d;color:#fff;'; $pt_icon='❔';
        }
        $pt = strtolower($row['phuongthucthanhtoan']);

// Gộp xử lý màu sắc, icon và text
switch($pt){
    case 'cod':
        $pt_colorr = 'background:#f39c12;color:#fff;'; // cam
        $pt_iconn  = '💵';
        $pt_text  = 'Thanh toán khi nhận hàng';
        break;
    case 'momo':
    case 'zalopay':
    case 'visa':
    case 'vnpay':
        $pt_colorr = 'background:#2ecc71;color:#fff;'; // xanh lá
        $pt_iconn  = '✅';
        $pt_text  = 'Đã thanh toán online';
        break;
    default:
        $pt_colorr = 'background:#7f8c8d;color:#fff;'; // xám
        $pt_iconn  = '❔';
        $pt_text  = $row['phuongthucthanhtoan'];
        break;
}

        echo "<div class='order-card' style='border:1px solid #ccc;'>
                <div class='order-img'><img src='/banhangonline/img/".($row['hinhanh'] ?? 'default.png')."'></div>
                <div class='order-info'>
                    <b>📋 ID Đơn:</b> <span>{$row['id']}</span><br>
                    <b>🛒 Sản phẩm:</b> <span>{$row['tensp']}</span><br>
                    <b>Loại:</b> {$row['loai_sanpham']}<br>
                    <b>Thương hiệu:</b> {$row['thuonghieu']}<br>
                    <b>🔢 Số lượng:</b> {$row['soluong']}<br>
                    <b>💰 Giá:</b> ".number_format($giaban_thucte,0,',','.')."đ<br>
                    <b>Điểm sử dụng:</b> ".number_format($diem_tru,0,',','.')."<br>
                    <b>Tổng tiền thực tế:</b> ".number_format($tongtien_thucte,0,',','.')."đ<br>
                    <span style='display:inline-block;padding:4px 10px;border-radius:12px;font-weight:bold;$pt_color'>$pt_icon {$row['phuongthucthanhtoan']}</span><br>
                     <span style='display:inline-block;padding:4px 10px;border-radius:12px;font-weight:bold;$pt_colorr'>$pt_iconn $pt_text</span><br>
                    <b>📅 Ngày đặt:</b> {$row['ngaydat']}<br>
                    <b>📊 Trạng thái:</b> $trangthai_text
                </div>
                <div class='order-actions'>";

        if($row['trangthai']=='dang_giao'){
            echo "<form method='post' enctype='multipart/form-data'>
                    <input type='hidden' name='xacnhan_id' value='{$row['id']}'>
                    <input type='file' name='anh_nhanhang'><br><br>
                    <button class='button-confirm' type='submit'>Xác nhận hoàn tất</button>
                  </form>";
        }
        if($row['trangthai']=='dang_cho'){
            echo "<form method='post'>
                    <input type='hidden' name='huydon_id' value='{$row['id']}'>
                    <button class='button-cancel' type='submit'>Hủy đơn</button>
                  </form>";
        }

        echo "</div></div>";
    }
     // ================= PHÂN TRANG =================
   
}

mysqli_close($conn);
?>
