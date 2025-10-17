<?php

// --- Kết nối DB ---
$conn = mysqli_connect("localhost", "root", "", "banhangonline");
mysqli_set_charset($conn, "utf8");
if (!$conn) die("Kết nối thất bại: " . mysqli_connect_error());

// --- Kiểm tra đăng nhập ---
if (!isset($_SESSION['user_id'])) {
    die("<p style='text-align:center;color:red;'>Bạn chưa đăng nhập.</p>");
}

// --- Load PHPMailer ---
require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- CSS trạng thái ---
echo "
<style>
.status { padding: 4px 10px; border-radius: 12px; font-weight: bold; color: #fff; }
.status-dangcho { background: #f1c40f; }
.status-danggiao { background: #e67e22; }
.status-hoantat { background: #27ae60; }
.status-dahuy { background: #95a5a6; }
.pagination {text-align:center;margin:20px 0;}
.pagination a {padding:6px 12px;margin:0 2px;border:1px solid #ccc;text-decoration:none;color:#333;border-radius:4px;}
.pagination a.active {background:#2980b9;color:#fff;}
.pagination a:hover {background:#eee;}
</style>
";

// --- Thanh tìm kiếm ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
echo "
<form method='get' style='text-align:center;margin:20px;'>
    <input type='hidden' name='admin' value='xacnhandonhang'>
    <input type='text' name='search' value='".htmlspecialchars($search, ENT_QUOTES)."' 
           placeholder='🔍 Tìm theo tên sản phẩm hoặc khách hàng' 
           style='padding:6px;width:300px;'>
    <button type='submit' style='padding:6px 12px;'>Tìm kiếm</button>
</form>
";

// --- Xử lý POST xác nhận đơn ---
if(isset($_POST['xacnhan_id'])){
    $id_donhang = intval($_POST['xacnhan_id']);
    $donhang = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT d.*, t.username, t.email, t.diachi, t.phone,
               s.tensp, s.hinhanh, s.gia AS giaban,
               l.tenloai AS loai_sanpham, th.tenthuonghieu AS thuonghieu
        FROM donhang d
        JOIN taikhoan t ON d.id_nguoidung=t.id
        JOIN sanpham s ON d.id_sanpham=s.id
        LEFT JOIN loaisanpham l ON s.id_loai=l.id
        LEFT JOIN thuonghieu th ON s.id_thuonghieu=th.id
        WHERE d.id=$id_donhang
    "));
    if(!$donhang) die("<p style='text-align:center;color:red;'>Đơn hàng không tồn tại!</p>");

    $soluong = max(1,intval($donhang['soluong']));
    $giaban_thucte = floatval($donhang['giaban']);
    
    // Tổng điểm đã sử dụng
    $result_diem = mysqli_query($conn,"SELECT SUM(diem) AS diem_tru FROM lichsu_diem WHERE id_donhang={$donhang['id']} AND loai='tieu'");
    $diem_tru = floatval(mysqli_fetch_assoc($result_diem)['diem_tru'] ?? 0);
    $tongtien_thucte = $giaban_thucte * $soluong - $diem_tru;

    // --- Chuyển trạng thái ---
    if($donhang['trangthai']=='dang_cho'){
        $trangthai_new = 'dang_giao';
        $stmt = $conn->prepare("UPDATE donhang SET trangthai=?, giaban=?, thanhtien=? WHERE id=?");
        $stmt->bind_param("sddi", $trangthai_new, $giaban_thucte, $tongtien_thucte, $id_donhang);
        $stmt->execute();
        echo "<p style='text-align:center;color:blue;'>🚚 Đơn hàng #$id_donhang đang giao.</p>";
    }
    elseif($donhang['trangthai']=='dang_giao'){
    $trangthai_new = 'hoan_tat';
    $anh_nhanhang = null;

    // Xử lý ảnh khách hàng nhận hàng
    if(isset($_FILES['anh_nhanhang']) && $_FILES['anh_nhanhang']['error']==0){
        $ext = pathinfo($_FILES['anh_nhanhang']['name'], PATHINFO_EXTENSION);
        $anh_nhanhang = 'nhanhang_'.$id_donhang.'_'.time().'.'.$ext;
        $upload_dir = __DIR__ . '/../uploads/';
        if(!is_dir($upload_dir)) mkdir($upload_dir,0777,true);
        move_uploaded_file($_FILES['anh_nhanhang']['tmp_name'],$upload_dir.$anh_nhanhang);
    }

    // --- Cập nhật trạng thái, giaban, thanhtien và id_admin nếu chưa có ---
    $stmt = $conn->prepare("
        UPDATE donhang 
        SET trangthai=?, giaban=?, thanhtien=?, id_admin=IF(id_admin IS NULL, ?, id_admin)
        WHERE id=?
    ");
    $stmt->bind_param("sddii", $trangthai_new, $giaban_thucte, $tongtien_thucte, $_SESSION['user_id'], $id_donhang);
    $stmt->execute();



        // Lưu lịch sử giao dịch
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

        // Cộng điểm thưởng
        $diem_thuong = floor($tongtien_thucte*0.01);
        if($diem_thuong>0){
            mysqli_query($conn,"UPDATE taikhoan SET diem=diem+$diem_thuong WHERE id=".$donhang['id_nguoidung']);
        }
        echo "<p style='text-align:center;color:green;'>✅ Đơn hàng #$id_donhang hoàn tất. Điểm thưởng: $diem_thuong.</p>";

        // --- Gửi email cảm ơn ---
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
        $mail->Password   = 'heqwfsjnixruxjtw';       // App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('suongnie2k5@gmail.com','TheGioiNongSan');
        $mail->addAddress($user_email, $user_name);

        // logo
       $mail->AddEmbeddedImage('../img/logochinh.jpg', 'logo_cid'); // thêm logo
       $mail->AddEmbeddedImage('../img/'.$donhang['hinhanh'], 'product_cid');

        $mail->isHTML(true);
        $mail->Subject = "Đơn hàng #$id_donhang đã hoàn tất!";

        // Xử lý icon, màu và text
        $pt = strtolower($donhang['phuongthucthanhtoan']);
        switch($pt){
            case 'cod': $pt_icon='💵'; $pt_color='#f39c12'; $pt_text='Thanh toán tiền mặt'; break;
            case 'momo': $pt_icon='📱'; $pt_color='#8e44ad'; $pt_text='Momo'; break;
            case 'zalopay': $pt_icon='💳'; $pt_color='#2ecc71'; $pt_text='ZaloPay'; break;
            case 'visa': $pt_icon='💳'; $pt_color='#3498db'; $pt_text='Visa/MasterCard'; break;
            case 'vnpay': $pt_icon='🏦'; $pt_color='#e74c3c'; $pt_text='VNPay'; break;
            default: $pt_icon='❔'; $pt_color='#7f8c8d'; $pt_text=$donhang['phuongthucthanhtoan'];
        }

        $mail->Body = "
        <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto; border:1px solid #e0e0e0; border-radius:15px; overflow:hidden;'>
            <div style='background: linear-gradient(135deg, #28a745, #85e085); padding:20px; text-align:center; color:white;'>
                <img src='cid:logo_cid' alt='Logo' style='width:80px; margin-bottom:10px; border-radius:10px;'>
                <h2>Đơn hàng #$id_donhang đã hoàn tất!</h2>
            </div>
            <div style='padding:20px; background:#f9f9f9; color:#333;'>
                <p>Chào <strong>$user_name</strong>,</p>
                <p>Cảm ơn bạn đã mua hàng tại <strong>TheGioiNongSan</strong>! Dưới đây là chi tiết đơn hàng của bạn:</p>
                <div style='padding:15px; background:#eafaf1; border-radius:10px;'>
                <p><strong>Hình ảnh sản phẩm:</strong><br>
                <img src='cid:product_cid' alt='{$donhang['tensp']}' style='width:200px; border-radius:10px;'>
                </p>
                    <p><strong>Sản phẩm:</strong> {$donhang['tensp']}</p>
                    <p><strong>Loại:</strong> {$donhang['loai_sanpham']}</p>
                    <p><strong>Thương hiệu:</strong> {$donhang['thuonghieu']}</p>
                    <p><strong>Số lượng:</strong> $soluong</p>
                    <p><strong>Tổng tiền:</strong> <span style='color:#28a745; font-weight:bold;'>".number_format($tongtien_thucte,0,',','.')."đ</span></p>
                    <p><strong>Phương thức thanh toán:</strong> <span style='color:$pt_color; font-weight:bold;'>$pt_icon $pt_text</span></p>
                </div>
                <hr style='margin:20px 0; border:none; border-top:1px solid #ccc;'>
                <p style='font-size:12px; color:#666;'>Đây là email tự động từ TheGioiNongSan. Vui lòng không trả lời trực tiếp.</p>
            </div>
        </div>
        ";

        $mail->send();
        echo "<p style='text-align:center;color:green;'>📧 Mail cảm ơn đã gửi tới $user_email</p>";

    } catch (Exception $e){
        echo "<p style='text-align:center;color:red;'>⚠ Không gửi được email: ".$mail->ErrorInfo."</p>";
    }
}

    }
}

// --- PHÂN TRANG & Hiển thị đơn ---
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page-1) * $limit;

$where = $search ? "WHERE s.tensp LIKE '%".mysqli_real_escape_string($conn,$search)."%' OR t.username LIKE '%".mysqli_real_escape_string($conn,$search)."%' " : "";

$total_res = mysqli_query($conn,"
    SELECT COUNT(*) AS total 
    FROM donhang d 
    JOIN taikhoan t ON d.id_nguoidung=t.id
    JOIN sanpham s ON d.id_sanpham=s.id
    $where
");
$total_rows = mysqli_fetch_assoc($total_res)['total'];
$total_pages = ceil($total_rows / $limit);

$sql = "
    SELECT d.*, t.username,t.diachi,t.phone,
           s.tensp,s.hinhanh,s.gia AS giaban,
           l.tenloai AS loai_sanpham, th.tenthuonghieu AS thuonghieu
    FROM donhang d
    JOIN taikhoan t ON d.id_nguoidung=t.id
    JOIN sanpham s ON d.id_sanpham=s.id
    LEFT JOIN loaisanpham l ON s.id_loai=l.id
    LEFT JOIN thuonghieu th ON s.id_thuonghieu=th.id
    $where
    ORDER BY d.ngaydat DESC
    LIMIT $limit OFFSET $offset
";
$result = mysqli_query($conn,$sql);

// Hiển thị danh sách
echo '<h2 style="text-align:center;">Danh sách đơn hàng</h2>';
while($row = mysqli_fetch_assoc($result)){
    $soluong = max(1, intval($row['soluong']));
    $giaban_thucte = floatval($row['giaban']);
    $result_diem = mysqli_query($conn,"SELECT SUM(diem) AS diem_tru FROM lichsu_diem WHERE id_donhang={$row['id']} AND loai='tieu'");
    $diem_tru = floatval(mysqli_fetch_assoc($result_diem)['diem_tru'] ?? 0);
    $tongtien_thucte = $giaban_thucte * $soluong - $diem_tru;

    $trangthai_text = match($row['trangthai']){
        'dang_cho' => "<span class='status status-dangcho'>Đang chờ</span>",
        'dang_giao' => "<span class='status status-danggiao'>Đang giao</span>",
        'hoan_tat' => "<span class='status status-hoantat'>Hoàn tất</span>",
        default => "<span class='status status-dahuy'>Đã hủy</span>",
    };

    $pt = strtolower($row['phuongthucthanhtoan']);
    switch($pt){
        case 'cod': $pt_colorr='background:#f39c12;color:#fff;'; $pt_icon='💵'; $pt_text='Thanh toán khi nhận hàng'; break;
        case 'momo': case 'zalopay': case 'visa': case 'vnpay': $pt_colorr='background:#2ecc71;color:#fff;'; $pt_icon='✅'; $pt_text='Đã thanh toán online'; break;
        default: $pt_colorr='background:#7f8c8d;color:#fff;'; $pt_icon='❔'; $pt_text=$row['phuongthucthanhtoan']; break;
    }

    echo "<div style='border:1px solid #ccc;padding:15px;margin:10px auto;width:900px;border-radius:8px;background:#f9f9f9;display:flex;'>
            <div style='flex:0 0 120px;margin-right:15px;'><img src='/banhangonline/img/{$row['hinhanh']}' width='120'></div>
            <div style='flex:1;color:#333;'>
                <b>ID Đơn:</b> {$row['id']}<br>
                <b>Người mua:</b> {$row['username']}<br>
                <b>Điện thoại:</b> {$row['phone']}<br>
                <b>Địa chỉ:</b> {$row['diachi']}<br>
                <b>Sản phẩm:</b> {$row['tensp']}<br>
                <b>Loại:</b> {$row['loai_sanpham']}<br>
                <b>Thương hiệu:</b> {$row['thuonghieu']}<br>
                <b>Số lượng:</b> {$row['soluong']}<br>
                <b>Giá:</b> ".number_format($giaban_thucte,0,',','.')."đ<br>
                <b>Điểm sử dụng:</b> ".number_format($diem_tru,0,',','.')."<br>
                <b>Tổng tiền thực tế:</b> ".number_format($tongtien_thucte,0,',','.')."đ<br>
                <span style='display:inline-block;padding:4px 10px;border-radius:12px;font-weight:bold;$pt_colorr'>$pt_icon $pt_text</span><br>
                <b>Ngày đặt:</b> {$row['ngaydat']}<br>
                <b>Trạng thái:</b> $trangthai_text
            </div>";

    if($row['trangthai']=='dang_cho'){
        echo "<form method='post' style='margin-left:20px;'>
                <input type='hidden' name='xacnhan_id' value='{$row['id']}'>
                <button type='submit' style='padding:8px 15px;background:#2980b9;color:#fff;border:none;border-radius:6px;'>Xác nhận giao hàng</button>
              </form>";
    } elseif($row['trangthai']=='dang_giao'){
        echo "<form method='post' enctype='multipart/form-data' style='margin-left:20px;'
            onsubmit=\"
                if(!document.querySelector('[name=anh_nhanhang]').value){alert('⚠️ Vui lòng tải lên ảnh khách hàng đã nhận hàng!');return false;}
            \">
            <input type='hidden' name='xacnhan_id' value='{$row['id']}'>
            <input type='file' name='anh_nhanhang' accept='image/*'><br><br>
            <button type='submit' style='padding:8px 15px;background:#27ae60;color:#fff;border:none;border-radius:6px;'>Xác nhận hoàn tất</button>
        </form>";
    }

    echo "</div>";
}

// --- Phân trang ---
if($total_pages > 1){
    echo "<div class='pagination'>";
    for($i=1;$i<=$total_pages;$i++){
        $active = $i==$page ? "active" : "";
        $url = "?admin=donhang&page=$i&search=".urlencode($search);
        echo "<a class='$active' href='$url'>$i</a>";
    }
    echo "</div>";
}

mysqli_close($conn);
?>
