<?php 
session_start();
$conn = mysqli_connect("localhost","root","", "banhangonline");
mysqli_set_charset($conn,"utf8");
if(!$conn) die("Kết nối thất bại: ".mysqli_connect_error());

// --- Kiểm tra đăng nhập ---
if(!isset($_SESSION['user_id'])){
    die("<h2 style='text-align:center;color:red;'>Bạn cần đăng nhập!</h2>
         <p style='text-align:center;'><a href='../dangnhap.php'>Đăng nhập</a></p>");
}
$id_nguoidung = intval($_SESSION['user_id']);

// --- Lấy thông tin user ---
$sql = "SELECT username,email,phone,diem,diachi FROM taikhoan WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$id_nguoidung);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

$username = $user_data['username'];
$email = $user_data['email'];
$phone = $user_data['phone'] ?? '';
$diem_hienco = intval($user_data['diem']);
$diachi = $user_data['diachi'] ?? '';

// --- Lấy sản phẩm trong giỏ ---
$chonsp_ids = $_SESSION['chonsp'] ?? [];
if(empty($chonsp_ids)){
    die("<h2 style='text-align:center;'>Bạn chưa chọn sản phẩm!</h2>
         <p style='text-align:center;'><a href='../index.php?content=giohang'>Quay lại giỏ hàng</a></p>");
}

$ids_str = implode(",", array_map('intval',$chonsp_ids));
$sql = "SELECT g.soluong AS sl_dat, s.id, s.tensp, g.gia, s.hinhanh, s.soluong AS kho,
               COALESCE(l.tenloai,'Chưa có') AS loai_sanpham,
               COALESCE(t.tenthuonghieu,'Chưa có') AS thuonghieu
        FROM giohang g
        JOIN sanpham s ON g.sanpham_id=s.id
        LEFT JOIN loaisanpham l ON s.id_loai=l.id
        LEFT JOIN thuonghieu t ON s.id_thuonghieu=t.id
        WHERE g.taikhoan_id=? AND s.id IN ($ids_str)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$id_nguoidung);
$stmt->execute();
$result = $stmt->get_result();

$cart_temp = [];
$tong_tien = 0;
while($row = $result->fetch_assoc()){
   $row['thanhtien'] = intval($row['gia']) * intval($row['sl_dat']);
    $cart_temp[] = $row;
   $tong_tien += $row['thanhtien'];
}

// Điểm thưởng dự kiến (1%)
$diem_thuong_du_kien = floor($tong_tien*0.01);

// --- Xử lý submit ---
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['thanhtoan'])){
    // <-- thêm: lấy phương thức thanh toán từ form -->
    $phuongthuc = trim($_POST['phuongthucthanhtoan'] ?? 'cod'); // 'cod' | 'momo' | 'zalopay'

    $diem_sudung = intval($_POST['diemsudung'] ?? 0);
    $diachi_post = trim($_POST['diachi'] ?? $diachi);
    $phone_post = trim($_POST['phone'] ?? $phone);

    // --- KIỂM TRA ĐỊA CHỈ VÀ SĐT ---
    if(empty($diachi_post) || empty($phone_post)){
        echo "<h2 style='color:red;text-align:center;'>⚠️ Vui lòng nhập đầy đủ số điện thoại và địa chỉ để giao hàng!</h2>
              <p style='text-align:center;'><a href='check_out.php'>Quay lại</a></p>";
        exit;
    }

    if($diem_sudung < 0) $diem_sudung = 0;
    if($diem_sudung > $diem_hienco) $diem_sudung = $diem_hienco;
    if($diem_sudung > $tong_tien) $diem_sudung = $tong_tien;

    $tong_tien_sau_diem = $tong_tien - $diem_sudung;
    if($tong_tien_sau_diem < 0) $tong_tien_sau_diem = 0;

    // --- KIỂM TRA TỒN KHO ---
    $loi = false;
    foreach($cart_temp as $sp){
        if(intval($sp['sl_dat']) > intval($sp['kho'])){
            $loi = true;
            break;
        }
    }
    if($loi){
        echo "<h2 style='color:red;text-align:center;'>⚠️ Hết hàng hoặc mua quá số lượng trong kho!</h2>
              <p style='text-align:center;'><a href='../index.php?content=giohang'>Quay lại giỏ hàng</a></p>";
        exit;
    }
    

    mysqli_begin_transaction($conn);
    try{
        $tyle_giam = ($diem_sudung > 0) ? $diem_sudung / $tong_tien : 0;
        $id_donhang_last = 0; // sẽ giữ id cuối cùng để redirect nếu cần

        foreach($cart_temp as $sp){
            $id_sp = intval($sp['id']);
            $soluong_dat = intval($sp['sl_dat']);
            $gia = intval($sp['gia']);

            $thanhtien = $gia * $soluong_dat * (1 - $tyle_giam);
            if($thanhtien < 0) $thanhtien = 0;
            $trangthai = 'dang_cho';

            // Lưu đơn hàng (giữ nguyên INSERT cấu trúc của bạn)
            $sql_dh = "INSERT INTO donhang
                (id_nguoidung,id_sanpham,tensanpham,hinhanh,loai_sanpham,thuonghieu,soluong,giaban,thanhtien,trangthai,ngaydat)
                VALUES (?,?,?,?,?,?,?,?,?,?,NOW())";
            $stmt_dh = $conn->prepare($sql_dh);
            $stmt_dh->bind_param("iissssiiis",
                $id_nguoidung,
                $id_sp,
                $sp['tensp'],
                $sp['hinhanh'],
                $sp['loai_sanpham'],
                $sp['thuonghieu'],
                $soluong_dat,
                $gia,
                $thanhtien,
                $trangthai
            );
            if(!$stmt_dh->execute()) throw new Exception($stmt_dh->error);

            $id_donhang_last = $conn->insert_id;

            // <-- thêm: lưu phương thức thanh toán vào donhang bằng UPDATE (không thay đổi INSERT ban đầu) -->
            // (lưu phuongthuc cho từng don hang)
            $stmt_update_pay = $conn->prepare("UPDATE donhang SET phuongthucthanhtoan=? WHERE id=?");
            if($stmt_update_pay){
                $stmt_update_pay->bind_param("si", $phuongthuc, $id_donhang_last);
                $stmt_update_pay->execute();
            }

            // Lịch sử điểm
            if($diem_sudung > 0){
                $sql_ls = "INSERT INTO lichsu_diem (taikhoan_id,diem,loai,mota,id_donhang) 
                           VALUES (?,?, 'tieu', ?, ?)";
                $stmt_ls = $conn->prepare($sql_ls);
                $mota = "Sử dụng $diem_sudung điểm để thanh toán đơn hàng #$id_donhang_last";
                $stmt_ls->bind_param("iiii",$id_nguoidung,$diem_sudung,$mota,$id_donhang_last);
                $stmt_ls->execute();
            }

            // Trừ tồn kho
            $sql_up = "UPDATE sanpham SET soluong=soluong-? WHERE id=?";
            $stmt_up = $conn->prepare($sql_up);
            $stmt_up->bind_param("ii",$soluong_dat,$id_sp);
            if(!$stmt_up->execute()) throw new Exception($stmt_up->error);
        }

        // Cập nhật điểm và địa chỉ
        $new_diem = $diem_hienco - $diem_sudung;
        $sql_user = "UPDATE taikhoan SET diem=?,diachi=? WHERE id=?";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param("isi",$new_diem,$diachi_post,$id_nguoidung);
        $stmt_user->execute();

        // Xóa giỏ hàng
        $ids_str_del = implode(",", array_map('intval',$chonsp_ids));
        $sql_del = "DELETE FROM giohang WHERE taikhoan_id=? AND sanpham_id IN ($ids_str_del)";
        $stmt_del = $conn->prepare($sql_del);
        $stmt_del->bind_param("i",$id_nguoidung);
        $stmt_del->execute();
        unset($_SESSION['chonsp']);

        $sql_user = "UPDATE taikhoan SET diem=?, diachi=?, phone=? WHERE id=?";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param("issi",$new_diem,$diachi_post,$phone_post,$id_nguoidung);
        $stmt_user->execute();

        mysqli_commit($conn);

        // --- Sau khi commit: nếu chọn phương thức online -> redirect tới xử lý cổng thanh toán ---
        // Sử dụng $id_donhang_last (id của đơn hàng cuối cùng) làm tham chiếu để xử lý thanh toán online
        if($phuongthuc === 'momo'){
            // chuyển sang file xử lý Momo (bạn cần tạo thanhtoan_momo.php theo mẫu của bạn)
            // truyền id và amount để xử lý
            $amount = intval($tong_tien_sau_diem);
            header("Location: thanhtoan_momo.php?order_id={$id_donhang_last}&amount={$amount}");
            exit;
        } elseif($phuongthuc === 'zalopay'){
            $amount = intval($tong_tien_sau_diem);
            header("Location: thanhtoan_zalopay.php?order_id={$id_donhang_last}&amount={$amount}");
            exit;
        }

        // Nếu là COD (mặc định) -> hiển thị hóa đơn như trước
       echo "<div style='max-width:720px;margin:50px auto;padding:30px;background:#ffffff;border-radius:15px;
             box-shadow:0 8px 20px rgba(0,0,0,0.1);font-family:\"Segoe UI\", Tahoma, Geneva, Verdana, sans-serif;'>

        <h2 style='text-align:center;color:#27ae60;margin-bottom:20px;'>✅ Đơn hàng đã được ghi nhận!</h2>

        <h3 style='border-bottom:2px solid #27ae60;padding-bottom:6px;color:#27ae60;'>Thông tin người mua</h3>
        <p>Tên: <b>".htmlspecialchars($username)."</b></p>
        <p>Email: <b>".htmlspecialchars($email)."</b></p>
        <p>Số điện thoại: <b>".htmlspecialchars($phone_post)."</b></p>
        <p>Địa chỉ: <b>".htmlspecialchars($diachi_post)."</b></p>

        <h3 style='border-bottom:2px solid #27ae60;padding-bottom:6px;color:#27ae60;margin-top:20px;'>Sản phẩm</h3>";


       foreach($cart_temp as $sp){
    $tonkho_conlai = max(0,$sp['kho']-$sp['sl_dat']);
    $thanhtien = $sp['gia']*$sp['sl_dat']*(1-$tyle_giam);
    if($thanhtien < 0) $thanhtien = 0;

             echo "<div style='display:flex;align-items:center;margin:12px 0;padding:12px;background:#f9f9f9;
                     border-radius:10px;box-shadow:0 4px 10px rgba(0,0,0,0.05);transition:0.3s;'>
            <img src='../img/".htmlspecialchars($sp['hinhanh'])."' 
                 style='width:80px;height:80px;object-fit:cover;margin-right:15px;border-radius:8px;'>
            <div>
                <p style='margin:0;font-weight:600;font-size:16px;color:#2c3e50;'>".htmlspecialchars($sp['tensp'])."</p>
                <p style='margin:2px 0;'>Loại: <b>".htmlspecialchars($sp['loai_sanpham'])."</b></p>
                <p style='margin:2px 0;'>Thương hiệu: <b>".htmlspecialchars($sp['thuonghieu'])."</b></p>
                <p style='margin:2px 0;color:#e74c3c;font-weight:600;'>Giá: ".number_format($sp['gia'],0,',','.')."đ</p>
                <p style='margin:2px 0;'>Số lượng đặt: <b>".$sp['sl_dat']."</b></p>
                <p style='margin:2px 0;'>Tồn kho còn lại: <b>$tonkho_conlai</b></p>
                <p style='margin:2px 0;color:#2980b9;font-weight:600;'>Thành tiền: ".number_format($thanhtien,0,',','.')."đ</p>
            </div>
          </div>";
        }

      echo "<h3 style='color:#27ae60;margin-top:20px;'>Tổng tiền sau trừ điểm: 
        <span style='color:#e74c3c;'>".number_format($tong_tien_sau_diem,0,',','.')."đ</span></h3>
      <p style='color:#27ae60;font-weight:600;'>Điểm thưởng nhận được: ".floor($tong_tien_sau_diem*0.01)." điểm</p>

      <div style='text-align:center;margin-top:20px;'>
        <a href='../index.php?content=giohang' 
           style='padding:12px 25px;background:#27ae60;color:#fff;text-decoration:none;
                  border-radius:10px;font-weight:600;transition:0.3s;display:inline-block;'>
          🔙 Quay lại giỏ hàng
        </a>
      </div>
      </div>";
exit;

    }catch(Exception $e){
        mysqli_rollback($conn);
        die("<h2 style='color:red;text-align:center;'>⚠️ Thanh toán thất bại! ".$e->getMessage()."</h2>");
    }
}
?>

<!-- Form thanh toán -->
<div style="max-width:720px;margin:50px auto;padding:30px;background:#ffffff;border-radius:15px;box-shadow:0 8px 20px rgba(0,0,0,0.1);font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <h2 style="text-align:center;color:#2980b9;margin-bottom:25px;">🛒 Thanh toán đơn hàng</h2>

    <form method="post">
        <!-- Thông tin người mua -->
        <h3 style="border-bottom:2px solid #2980b9;padding-bottom:8px;color:#2980b9;">Thông tin người mua</h3>
        <p>Tên: <b><?= htmlspecialchars($username) ?></b></p>
        <p>Email: <b><?= htmlspecialchars($email) ?></b></p>

        <!-- Phương thức thanh toán -->
        <h3 style="border-bottom:2px solid #2980b9;padding-bottom:8px;color:#2980b9;">Phương thức thanh toán</h3>
        <div style="display:flex;flex-direction:column;margin-bottom:15px;">
            <label style="margin:6px 0;cursor:pointer;">
                <input type="radio" name="phuongthucthanhtoan" value="cod" checked> 💵 Thanh toán khi nhận hàng (COD)
            </label> 
            <label style="margin:6px 0;cursor:pointer;">
                <input type="radio" name="phuongthucthanhtoan" value="momo"> 🌐 Thanh toán Online - MoMo
            </label>
            <label style="margin:6px 0;cursor:pointer;">
                <input type="radio" name="phuongthucthanhtoan" value="zalopay"> 🌐 Thanh toán Online - ZaloPay
            </label>
        </div>

        <!-- Thông tin bổ sung -->
        <label>Số điện thoại:</label><br>
        <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>" required
               style="width:100%;padding:10px;margin:5px 0 15px 0;border:1px solid #ccc;border-radius:8px;transition:0.3s;">
        
        <label>Địa chỉ giao hàng:</label><br>
        <input type="text" name="diachi" value="<?= htmlspecialchars($diachi) ?>" required
               style="width:100%;padding:10px;margin:5px 0 15px 0;border:1px solid #ccc;border-radius:8px;transition:0.3s;">
        
        <label>Sử dụng điểm (hiện có <?= $diem_hienco ?> điểm):</label><br>
        <input type="number" name="diemsudung" min="0" max="<?= $diem_hienco ?>" value="0"
               style="width:100%;padding:10px;margin:5px 0 15px 0;border:1px solid #ccc;border-radius:8px;transition:0.3s;">
        <p style="color:#27ae60;font-weight:600;">🎁 Điểm thưởng dự kiến: <?= $diem_thuong_du_kien ?> điểm</p>

        <!-- Sản phẩm -->
        <h3 style="border-bottom:2px solid #2980b9;padding-bottom:8px;color:#2980b9;">Sản phẩm</h3>
        <?php foreach($cart_temp as $sp): ?>
        <div style="display:flex;align-items:center;margin:12px 0;padding:12px;background:#f9f9f9;border-radius:10px;box-shadow:0 4px 10px rgba(0,0,0,0.05);transition:0.3s;">
            <img src="../img/<?= htmlspecialchars($sp['hinhanh']) ?>" 
                 style="width:80px;height:80px;object-fit:cover;margin-right:15px;border-radius:8px;">
            <div>
                <p style="margin:0;font-weight:600;font-size:16px;color:#2c3e50;"><?= htmlspecialchars($sp['tensp']) ?></p>
                <p style="margin:2px 0;">Loại: <b><?= htmlspecialchars($sp['loai_sanpham']) ?></b></p>
                <p style="margin:2px 0;">Thương hiệu: <b><?= htmlspecialchars($sp['thuonghieu']) ?></b></p>
                <p style="margin:2px 0;color:#e74c3c;font-weight:600;">Giá: <?= number_format($sp['gia'],0,',','.') ?>đ</p>
                <p style="margin:2px 0;">Số lượng đặt: <b><?= $sp['sl_dat'] ?></b></p>
                <p style="margin:2px 0;">Tồn kho: <b><?= $sp['kho'] ?></b></p>
            </div>
        </div>
        <?php endforeach; ?>

        <h3 style="color:#2980b9;margin-top:20px;">Tổng tiền: <span style="color:#e74c3c;"><?= number_format($tong_tien,0,',','.') ?>đ</span></h3>
        <button type="submit" name="thanhtoan"
                style="width:100%;padding:15px;background:#27ae60;color:#fff;font-size:16px;font-weight:600;border:none;border-radius:10px;margin-top:15px;cursor:pointer;transition:0.3s;">
            ✅ Thanh toán
        </button>
    </form>
</div>

