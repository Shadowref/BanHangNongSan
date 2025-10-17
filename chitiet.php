<?php 
// session_start();
$conn = new mysqli("localhost", "root", "", "banhangonline");
$conn->set_charset("utf8");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

if (!isset($_GET['id'])) {
    echo "<p style='color:red;font-weight:bold;'>Không có sản phẩm nào được chọn.</p>";
    exit;
}
$id_sp = intval($_GET['id']);

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
if ($user_id) {
    $check = $conn->query("SELECT role FROM taikhoan WHERE id=$user_id LIMIT 1");
    if ($check && $check->num_rows>0) {
        $rowRole = $check->fetch_assoc();
        $role = $rowRole['role'];
        
    }
}

$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';


// --- POST Like ---
if (isset($_POST['like_btn']) && isset($_POST['like_id'])) {
    $like_id = intval($_POST['like_id']);
    $conn->query("UPDATE sanpham SET likes = likes + 1 WHERE id = $like_id");
    echo "<script>window.location='?content=chitiet&id=$like_id';</script>";
    exit;
}

// --- POST thêm review ---
if (isset($_POST['submit_review'])) {
    if (!$user_id) {
        echo "<script>alert('Bạn cần đăng nhập để đánh giá!'); window.location='login.php';</script>";
        exit;
    }

    // ================== KIỂM TRA USER ĐÃ MUA HÀNG ==================
    $check_buy = $conn->query("
    SELECT COUNT(*) AS c 
    FROM donhang 
    WHERE id_nguoidung = $user_id 
      AND id_sanpham = $id_sp
      AND trangthai = 'hoan_tat'
");

    $has_bought = $check_buy->fetch_assoc()['c'] ?? 0;

    if ($has_bought == 0) {
        echo "<script>alert('Bạn phải mua sản phẩm này mới được phép đánh giá!'); window.location='?content=chitiet&id=$id_sp';</script>";
        exit;
    }
    // ================================================================

    $rating = intval($_POST['rating']);
    $comment = $conn->real_escape_string($_POST['comment']);
    $user_id_session = $user_id ? $user_id : NULL;

    $user_name = $user_id_session 
        ? $conn->query("SELECT username FROM taikhoan WHERE id = $user_id_session")->fetch_assoc()['username'] 
        : (!empty($_POST['user_name']) ? $conn->real_escape_string($_POST['user_name']) : 'Khách');

    $anh_review = $video_review = NULL;
    $upload_dir = 'uploads/';
    if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    if(isset($_FILES['anh_review']) && $_FILES['anh_review']['error'] == 0){
        $ext = pathinfo($_FILES['anh_review']['name'], PATHINFO_EXTENSION);
        $anh_review = 'review_img_'.time().'.'.$ext;
        move_uploaded_file($_FILES['anh_review']['tmp_name'], $upload_dir.$anh_review);
    }
    if(isset($_FILES['video_review']) && $_FILES['video_review']['error'] == 0){
        $ext = pathinfo($_FILES['video_review']['name'], PATHINFO_EXTENSION);
        $video_review = 'review_vid_'.time().'.'.$ext;
        move_uploaded_file($_FILES['video_review']['tmp_name'], $upload_dir.$video_review);
    }

    if ($rating >= 1 && $rating <= 5) {
        $conn->query("
            INSERT INTO danhgia (id_sanpham, id_nguoidung, user_name, rating, comment, ngaydat, anh_review, video_review, trangthai)
            VALUES ($id_sp, ".($user_id_session ? $user_id_session : "NULL").", '$user_name', $rating, '$comment', NOW(), ".($anh_review ? "'$anh_review'" : "NULL").", ".($video_review ? "'$video_review'" : "NULL").", 0)
        ");

        if ($conn->affected_rows > 0) {
            $user_id = $_SESSION['user_id']; // ID người dùng
            $username = mysqli_real_escape_string($conn, $user_name); // tên người bình luận

            // Lấy tên sản phẩm từ DB
            $ten_sanpham = $conn->query("SELECT tensp FROM sanpham WHERE id=$id_sp")->fetch_assoc()['tensp'];
            $sanpham = mysqli_real_escape_string($conn, $ten_sanpham);

            $msg = "Người dùng $username vừa bình luận về $sanpham";

            $sql = "INSERT INTO thongbao (user_id, message) VALUES ('$user_id', '$msg')";
            $conn->query($sql);
        }
    }

    echo "<script>window.location='?content=chitiet&id=$id_sp';</script>";
    exit;
}

// --- POST cập nhật review ---
if (isset($_POST['update_review']) && $user_id) {
    $review_id = intval($_POST['review_id']);
    $rating = intval($_POST['rating']);
    $comment = $conn->real_escape_string($_POST['comment']);

    $res_old = $conn->query("SELECT * FROM danhgia WHERE id = $review_id");
    if ($res_old && $res_old->num_rows > 0) {
        $old = $res_old->fetch_assoc();
        if($role == 'admin' || $old['id_nguoidung']==$user_id){
            $anh_review = $old['anh_review'];
            $video_review = $old['video_review'];
            $upload_dir = 'uploads/';

            if(isset($_FILES['anh_review']) && $_FILES['anh_review']['error'] == 0){
                $ext = pathinfo($_FILES['anh_review']['name'], PATHINFO_EXTENSION);
                $anh_review = 'review_img_'.time().'.'.$ext;
                move_uploaded_file($_FILES['anh_review']['tmp_name'], $upload_dir.$anh_review);
            }
            if(isset($_FILES['video_review']) && $_FILES['video_review']['error'] == 0){
                $ext = pathinfo($_FILES['video_review']['name'], PATHINFO_EXTENSION);
                $video_review = 'review_vid_'.time().'.'.$ext;
                move_uploaded_file($_FILES['video_review']['tmp_name'], $upload_dir.$video_review);
            }

            $conn->query("
                UPDATE danhgia 
                SET rating=$rating, comment='$comment',
                    anh_review=".($anh_review?"'$anh_review'":"NULL").",
                    video_review=".($video_review?"'$video_review'":"NULL")."
                WHERE id=$review_id
            ");
            echo "<script>window.location='?content=chitiet&id=$id_sp';</script>";
            exit;
        }
    }
}

$review_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$type = $_POST['type'] ?? 'review'; // 'review' hoặc 'reply'

if (!isset($_SESSION['liked'])) {
    $_SESSION['liked'] = [];
}
if (!isset($_SESSION['liked'][$type])) {
    $_SESSION['liked'][$type] = [];
}

$table = ($type === 'review') ? 'danhgia' : 'phanhoi_review';

// Nếu user đã like rồi => unlike
if (isset($_SESSION['liked'][$type][$review_id]) && $_SESSION['liked'][$type][$review_id] === true) {
    $conn->query("UPDATE $table SET likes = CASE WHEN likes > 0 THEN likes - 1 ELSE 0 END WHERE id=$review_id");
    $_SESSION['liked'][$type][$review_id] = false;
    $likes = $conn->query("SELECT likes FROM $table WHERE id=$review_id")->fetch_assoc()['likes'] ?? 0;
    
}
// Nếu chưa like => like
else {
    $conn->query("UPDATE $table SET likes = likes + 1 WHERE id=$review_id");
    $_SESSION['liked'][$type][$review_id] = true;
    $likes = $conn->query("SELECT likes FROM $table WHERE id=$review_id")->fetch_assoc()['likes'] ?? 0;
    
}

if(isset($_POST['submit_reply']) && $user_id){
    $review_id = intval($_POST['review_id']);
    $reply_comment = $conn->real_escape_string($_POST['reply_comment']);

    // Tên người reply
    $user_name = $conn->query("SELECT username FROM taikhoan WHERE id=$user_id")->fetch_assoc()['username'];

    // Lấy thông tin review gốc
    $review = $conn->query("SELECT id_nguoidung, user_name FROM danhgia WHERE id=$review_id")->fetch_assoc();
    $review_owner_name = $review['user_name'] ?? 'Người dùng';

    // Nếu reply cho 1 người cụ thể (ví dụ truyền reply_to_user_id từ form)
    $reply_to_user_id = isset($_POST['reply_to_user_id']) ? intval($_POST['reply_to_user_id']) : NULL;
    $reply_to_user_name = NULL;
    if($reply_to_user_id){
        $reply_to_user_name = $conn->query("SELECT username FROM taikhoan WHERE id=$reply_to_user_id")->fetch_assoc()['username'];
        $reply_to_user_name = mysqli_real_escape_string($conn, $reply_to_user_name);
    }

    // Thêm vào DB
    $conn->query("
        INSERT INTO phanhoi_review
        (review_id, id_nguoidung, user_name, comment, ngaydat, reply_to_user_id, reply_to_user_name)
        VALUES
        ($review_id, $user_id, '$user_name', '$reply_comment', NOW(), ".($reply_to_user_id?$reply_to_user_id:"NULL").", ".($reply_to_user_name?"'$reply_to_user_name'":"NULL").")
    ");
     // Lấy tên sản phẩm từ DB
    $ten_sanpham = $conn->query("SELECT tensp FROM sanpham WHERE id=$id_sp")->fetch_assoc()['tensp'];
    $sanpham = mysqli_real_escape_string($conn, $ten_sanpham);

    // --- Tạo thông báo ---
    if($conn->affected_rows > 0){
        $msg = $reply_to_user_name
            ? "Người dùng $user_name vừa trả lời $reply_to_user_name"
            : "Người dùng $user_name vừa trả lời đánh giá của $review_owner_name trong sản phẩm $sanpham";
        $sql = "INSERT INTO thongbao (user_id, message) VALUES ('$user_id', '$msg')";
        $conn->query($sql);
    }

    echo "<script>window.location='?content=chitiet&id=$id_sp';</script>";
    exit;
}

// --- POST cập nhật reply ---
if(isset($_POST['update_reply']) && $user_id){
    $reply_id = intval($_POST['reply_id']);
    $reply_comment = $conn->real_escape_string($_POST['reply_comment']);
    $res_r = $conn->query("SELECT * FROM phanhoi_review WHERE id=$reply_id");
    if($res_r && $res_r->num_rows>0){
        $row_r = $res_r->fetch_assoc();
        if($role=='admin' || $row_r['id_nguoidung']==$user_id){
            $conn->query("UPDATE phanhoi_review SET comment='$reply_comment', ngaydat=NOW() WHERE id=$reply_id");
            echo "<script>window.location='?content=chitiet&id=$id_sp';</script>";
            exit;
        }
    }
}

// --- GET Xóa reply ---
if(isset($_GET['delete_reply']) && $user_id){
    $reply_id = intval($_GET['delete_reply']);
    $res_r = $conn->query("SELECT * FROM phanhoi_review WHERE id=$reply_id");
    if($res_r && $res_r->num_rows>0){
        $row_r = $res_r->fetch_assoc();
        if($role=='admin' || $row_r['id_nguoidung']==$user_id){
            $conn->query("DELETE FROM phanhoi_review WHERE id=$reply_id");
            echo "<script>window.location='?content=chitiet&id=$id_sp';</script>";
            exit;
        }
    }
}

// --- Xóa / Ẩn / Hiện review ---
if (isset($_GET['delete_review']) && $user_id) {
    $delete_id = intval($_GET['delete_review']);
    if($role == 'admin'){
        $conn->query("DELETE FROM danhgia WHERE id = $delete_id");
        echo "<script>alert('Admin đã xóa đánh giá!'); window.location='?content=chitiet&id=$id_sp';</script>";
        exit;
    } else {
        $res_check = $conn->query("SELECT * FROM danhgia WHERE id = $delete_id AND id_nguoidung = $user_id");
        if ($res_check && $res_check->num_rows > 0) {
            $conn->query("DELETE FROM danhgia WHERE id = $delete_id");
            echo "<script>alert('Xóa đánh giá thành công!'); window.location='?content=chitiet&id=$id_sp';</script>";
            exit;
        } else {
            echo "<script>alert('Bạn không có quyền xóa đánh giá này!'); window.location='?content=chitiet&id=$id_sp';</script>";
            exit;
        }
    }
}
if(isset($_GET['toggle_hide']) && $role=='admin'){
    $hid_id = intval($_GET['toggle_hide']);
    $res = $conn->query("SELECT trangthai FROM danhgia WHERE id=$hid_id");
    if($res && $res->num_rows>0){
        $current = $res->fetch_assoc()['trangthai'];
        $conn->query("UPDATE danhgia SET trangthai = ".($current?0:1)." WHERE id=$hid_id");
    }
    echo "<script>window.location='?content=chitiet&id=$id_sp';</script>";
    exit;
}

// --- Lấy chi tiết sản phẩm ---
$sql = "SELECT sp.*, l.tenloai FROM sanpham sp LEFT JOIN loaisanpham l ON sp.id_loai = l.id WHERE sp.id = $id_sp";
$result = $conn->query($sql);
if (!$result || $result->num_rows == 0) { echo "<p style='color:red;'>Sản phẩm không tồn tại.</p>"; exit; }
$row = $result->fetch_assoc();

// --- Tồn kho, bán, avg review, khuyến mại ---
$tonkho = intval($row['soluong']);
$da_ban = intval($conn->query("SELECT SUM(soluong) as da_ban FROM donhang WHERE id_sanpham = $id_sp")->fetch_assoc()['da_ban'] ?? 0);

// Lấy trung bình số sao và số lượt đánh giá từ bảng danhgia
// Lấy tổng số lượt và trung bình
$sql_avg = "SELECT AVG(rating) AS avg_star, COUNT(*) AS num_review 
            FROM danhgia 
            WHERE id_sanpham = $id_sp AND trangthai=0";
$avg_data = $conn->query($sql_avg)->fetch_assoc();
$avg_star = round(floatval($avg_data['avg_star'] ?? 0), 1);
$num_review = intval($avg_data['num_review'] ?? 0);

// Lấy số lượt cho từng mức sao
$stars = [];
for ($i = 1; $i <= 5; $i++) {
    $sql_star = "SELECT COUNT(*) AS c FROM danhgia 
                 WHERE id_sanpham=$id_sp AND trangthai=0 AND rating=$i";
    $res = $conn->query($sql_star)->fetch_assoc();
    $stars[$i] = intval($res['c'] ?? 0);
}       // số lượt đánh giá


// --- Giá khuyến mại ---
$res_km = $conn->query("SELECT * FROM khuyenmai WHERE sanpham_id=$id_sp AND (ngay_bat_dau IS NULL OR ngay_bat_dau<=CURDATE()) AND (ngay_ket_thuc IS NULL OR ngay_ket_thuc>=CURDATE()) ORDER BY id DESC LIMIT 1");
$km = $res_km->fetch_assoc() ?? null;
$gia_hientai = $km && floatval($km['giakhuyenmai'])>0 ? floatval($km['giakhuyenmai']) : floatval($row['gia']);

?>

<!-- HIỂN THỊ SẢN PHẨM -->
<div style="display:flex; flex-wrap:wrap; gap:50px; padding:10px; font-family: Arial, sans-serif; background:#fdfdfd; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1);width:auto;">
    
    <!-- Ảnh sản phẩm -->
        <!-- Ảnh sản phẩm -->
        <div style="
            flex:1 1 280px; 
            text-align:center; 
            background:#fff; 
            padding:20px; 
            border-radius:16px; 
            box-shadow:0 4px 12px rgba(0,0,0,0.12); 
            transition:all 0.3s ease;
            cursor:pointer;
        "
        onmouseover="this.style.transform='scale(1.03)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.18)';"
        onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.12)';"
        >
            <?php
            $img_sp = !empty($row['hinhanh']) ? $row['hinhanh'] : 'default.png';
            echo "<img src='img/{$img_sp}' 
                    alt='".htmlspecialchars($row['tensp'])."' 
                    style='width:100%; max-width:260px; border-radius:12px; object-fit:contain; box-shadow:0 2px 6px rgba(0,0,0,0.08);'>";
            ?>
        </div>
    <!-- Thông tin sản phẩm -->
    <div style="flex:2 1 400px; display:flex; flex-direction:column; justify-content:space-between;">
        <div>
<!-- Thông tin sản phẩm -->
            <div style="font-family:Arial, sans-serif; margin-bottom:20px;">
<!-- Tên sản phẩm -->
        <h1 style="
            font-size:32px;
            font-weight:800;
            margin-bottom:16px;
            line-height:1.4;
            font-family:'Segoe UI', Roboto, Arial, sans-serif;
            background: linear-gradient(90deg, #2d3436, #0984e3);
            background-clip: text;           /* ✅ thuộc tính chuẩn */
            -webkit-background-clip: text;   /* ✅ cho Chrome/Safari */
            color: transparent;              /* ✅ fallback */
            -webkit-text-fill-color: transparent;
            letter-spacing: 0.5px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        ">
            <?= htmlspecialchars($row['tensp']) ?>
            
        </h1>

            <!-- Loại sản phẩm -->
            <p style="font-size:15px; color:#636e72; margin:6px 0;">
                Loại: <span style="color:#0984e3; font-weight:600;">
                <?= htmlspecialchars($row['tenloai']) ?>
                </span>
            </p>

            <!-- Tồn kho + đã bán -->
            <p style="font-size:15px; color:#2d3436; margin:6px 0;">
                <span style="margin-right:15px;">📦 Tồn kho: 
                <b style="color:#d63031;"><?= $tonkho ?></b>
                </span> 
                | 🔥 Đã bán: 
                <b style="color:#00b894;"><?= $da_ban ?></b>
            </p>

            <!-- Đánh giá -->
            <div style="font-size:15px; color:#2d3436; margin-top:8px;">
                ⭐ <b style="color:#e67e22; font-size:16px;"><?= $avg_star ?>/5</b> 
                <span style="color:#636e72;">từ <?= $num_review ?> nhận xét</span>
            </div>
            </div>

           <div style="background:#f9f9f9; padding:15px; border-radius:8px; font-family:Arial, sans-serif; font-size:14px; color:#2d3436; line-height:1.6; max-height:150px; overflow:hidden; position:relative;" id="descBox">
            <?= nl2br(htmlspecialchars($row['mota'])); ?>
            <div id="fadeOverlay" style="position:absolute; bottom:0; left:0; right:0; height:40px; background:linear-gradient(to top, #f9f9f9, transparent);"></div>
            </div>
            <button onclick="toggleDesc()" style="margin-top:8px; background:none; border:none; color:#0984e3; cursor:pointer; font-size:13px;" id="toggleBtn">Xem thêm</button>

            <script>
            function toggleDesc(){
            const box = document.getElementById("descBox");
            const overlay = document.getElementById("fadeOverlay");
            const btn = document.getElementById("toggleBtn");
            if(box.style.maxHeight){
                box.style.maxHeight = "";
                overlay.style.display = "none";
                btn.textContent = "Ẩn bớt";
            } else {
                box.style.maxHeight = "150px";
                overlay.style.display = "block";
                btn.textContent = "Xem thêm";
            }
            }
            </script>

                        <!-- Giá hiển thị -->
                    <div style="margin-top:12px; font-family:Arial, sans-serif;">
            <?php if($km && floatval($km['giakhuyenmai'])>0): ?>
                <!-- Giá gốc gạch bỏ -->
                <span style="font-size:16px; text-decoration:line-through; color:#888; margin-right:8px;">
                <?= number_format($row['gia'],0,',','.') ?>₫
                </span>

                <!-- Giá khuyến mãi nổi bật -->
                <span style="font-size:24px; font-weight:bold; color:#e53935; margin-right:8px;">
                <?= number_format($gia_hientai,0,',','.') ?>₫
                </span>

                <!-- % giảm giá -->
                <?php if(intval($km['giamgia'])>0): ?>
                <span style="background:#e53935; color:#fff; font-size:14px; font-weight:bold; padding:2px 6px; border-radius:4px;">
                    -<?= $km['giamgia'] ?>%
                </span>
                <?php endif; ?>

            <?php else: ?>
                <!-- Giá thường -->
                <span style="font-size:22px; font-weight:bold; color:#2d3436;">
                <?= number_format($gia_hientai,0,',','.') ?>₫
                </span>
            <?php endif; ?>
            </div>
        </div>

 <!-- Form mua hàng -->
            <form action="./card/cart.php" method="POST" style="margin-top:25px; font-family:Arial, sans-serif;">
                <!-- Hidden input -->
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <input type="hidden" name="tensp" value="<?= htmlspecialchars($row['tensp']) ?>">
                <input type="hidden" name="gia" value="<?= $gia_hientai ?>">

                <!-- Chọn số lượng -->
                <div style="display:flex; align-items:center; margin-bottom:20px;">
                    <label for="soluong" style="font-size:15px; font-weight:600; color:#2d3436; margin-right:10px;">
                        Số lượng:
                    </label>
                    <input 
                        type="number" 
                        id="soluong"
                        name="soluong" 
                        value="1" 
                        min="1" 
                        max="<?= $tonkho ?>" 
                        style="width:70px; text-align:center; padding:6px; border:1px solid #ccc; border-radius:6px; font-size:15px;">
                    <span style="margin-left:10px; font-size:13px; color:#636e72;">
                        (Còn <?= $tonkho ?> sản phẩm)
                    </span>
                </div>

                <!-- Nút hành động -->
                <div style="display:flex; gap:12px;">
                    <!-- Mua ngay -->
                    <button 
                        type="submit" 
                        name="action" 
                        value="buy_now" 
                        style="flex:1; background:linear-gradient(135deg,#ff4d4d,#e74c3c); color:#fff; padding:12px; font-size:16px; font-weight:600; border:none; border-radius:8px; cursor:pointer; box-shadow:0 4px 8px rgba(0,0,0,0.15); transition:0.3s;">
                        ⚡ Mua ngay
                    </button>

                    <!-- Thêm vào giỏ -->
                    <button 
                        type="submit" 
                        name="action" 
                        value="add" 
                        style="flex:1; background:linear-gradient(135deg,#2ecc71,#27ae60); color:#fff; padding:12px; font-size:16px; font-weight:600; border:none; border-radius:8px; cursor:pointer; box-shadow:0 4px 8px rgba(0,0,0,0.15); transition:0.3s;">
                        🛒 Thêm giỏ hàng
                    </button>
                </div>
            </form>

    <!-- Nút Like -->
            <form method="post" style="margin-top:15px; font-family:Arial, sans-serif;">
                <input type="hidden" name="like_id" value="<?= $row['id'] ?>">
                <button 
                    type="submit" 
                    name="like_btn" 
                    style="
                        display:inline-flex; 
                        align-items:center; 
                        gap:6px;
                        background:linear-gradient(135deg,#ff7675,#e74c3c);
                        color:#fff;
                        padding:10px 18px;
                        font-size:15px;
                        font-weight:600;
                        border:none;
                        border-radius:25px;
                        cursor:pointer;
                        box-shadow:0 4px 10px rgba(0,0,0,0.15);
                        transition:all 0.3s ease;
                    "
                    onmouseover="this.style.opacity='0.9'; this.style.transform='scale(1.05)';"
                    onmouseout="this.style.opacity='1'; this.style.transform='scale(1)';"
                >
                    ❤️ <span>Thích (<?= intval($row['likes']) ?>)</span>
                </button>
            </form>

    </div>

<div style="  width: 90%;            /* Chiếm 90% chiều rộng màn hình */
    max-width: 800px;      /* Tối đa 800px để không quá to trên desktop */
    min-width: 300px;      /* Tối thiểu 300px để không quá nhỏ */
    margin: 10px auto;     /* Canh giữa */
    font-family: Arial, sans-serif;
    background: #fff;
    padding: 15px 20px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    box-sizing: border-box; /* Bao gồm padding vào width */">
  <!-- Điểm trung bình -->
  <div style="display:flex; align-items:center; margin-bottom:10px;">
    <div style="font-size:40px; font-weight:bold; margin-right:10px;">
      <?= $avg_star ?>
    </div>
    <div>
      <div style="color:#ffb400; font-size:20px;">
        <?php for($i=1;$i<=5;$i++): ?>
          <?= ($i <= round($avg_star)) ? "★" : "☆" ?>
        <?php endfor; ?>
      </div>
      <small><?= $num_review ?> đánh giá</small>
    </div>
  </div>

  <!-- Thanh rating -->
  <?php for($i=5;$i>=1;$i--): 
    $percent = $num_review > 0 ? round(($stars[$i]/$num_review)*100) : 0;
  ?>
    <div style="display:flex; align-items:center; margin:4px 0;">
      <div style="width:20px;"><?= $i ?></div>
      <div style="flex:1; background:#eee; height:10px; border-radius:5px; margin:0 8px; overflow:hidden;">
        <div style="width:<?= $percent ?>%; height:100%; background:#ffb400;"></div>
      </div>
      <div style="width:40px; text-align:right; font-size:12px; color:#333;">
        <?= $stars[$i] ?>
      </div>
    </div>
  <?php endfor; ?>

<!-- FORM THÊM / SỬA ĐÁNH GIÁ -->
<div style="
    margin-top:30px; 
    padding:20px; 
    background:#fff; 
    border-radius:12px; 
    box-shadow:0 4px 12px rgba(0,0,0,0.1); 
    font-family:'Segoe UI', sans-serif;
">
<?php
if(isset($_GET['edit_review']) && $user_id){
    $edit_id = intval($_GET['edit_review']);
    $res_edit = $conn->query("SELECT * FROM danhgia WHERE id=$edit_id");
    if($res_edit && $res_edit->num_rows>0){
        $edit = $res_edit->fetch_assoc();
        if($role=='admin' || $edit['id_nguoidung']==$user_id):
?>
    <h3 style="margin-bottom:15px; color:#e67e22;">✏️ Sửa đánh giá</h3>
    <form action="?content=chitiet&id=<?= $id_sp ?>" method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:15px;">
        <input type="hidden" name="review_id" value="<?= $edit['id'] ?>">

        <!-- Rating -->
        <label style="font-weight:600;">Số sao:</label>
        <select name="rating" required style="padding:8px; border:1px solid #ddd; border-radius:6px;">
            <?php for($i=1;$i<=5;$i++): ?>
            <option value="<?= $i ?>" <?= $edit['rating']==$i?'selected':'' ?>><?= str_repeat('⭐',$i) ?></option>
            <?php endfor; ?>
        </select>

        <!-- Comment -->
        <label style="font-weight:600;">Nhận xét:</label>
        <textarea name="comment" rows="4" required
            style="padding:10px; border:1px solid #ddd; border-radius:6px; resize:vertical;"><?= htmlspecialchars($edit['comment']) ?></textarea>

        <!-- Upload -->
        <div>
            <label style="font-weight:600;">Ảnh (tùy chọn):</label><br>
            <input type="file" name="anh_review" accept="image/*" style="margin-top:5px;">
            <?php if(!empty($edit['anh_review']) && file_exists('uploads/'.$edit['anh_review'])): ?>
                <img src="uploads/<?= $edit['anh_review'] ?>" style="max-width:120px; border-radius:8px; margin-top:10px; box-shadow:0 2px 6px rgba(0,0,0,0.1);">
            <?php endif; ?>
        </div>

        <div>
            <label style="font-weight:600;">Video (tùy chọn):</label><br>
            <input type="file" name="video_review" accept="video/*" style="margin-top:5px;">
            <?php if(!empty($edit['video_review']) && file_exists('uploads/'.$edit['video_review'])): ?>
                <video controls style="max-width:200px; margin-top:10px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1);">
                    <source src="uploads/<?= $edit['video_review'] ?>" type="video/mp4">
                    Trình duyệt không hỗ trợ video.
                </video>
            <?php endif; ?>
        </div>

        <!-- Button -->
        <button type="submit" name="update_review"
            style="background:#e67e22; color:white; padding:10px 18px; border:none; border-radius:6px; cursor:pointer; font-weight:600; transition:0.3s;">
            ✅ Cập nhật đánh giá
        </button>
    </form>

<?php 
        endif;
    }
} else {
    if($user_id):
?>
    <h3 style="margin-bottom:15px; color:#27ae60;">⭐ Đánh giá & Phản hồi</h3>
    <form action="?content=chitiet&id=<?= $id_sp ?>" method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:15px;">

        <!-- Rating -->
        <label style="font-weight:600;">Số sao:</label>
        <select name="rating" required style="padding:8px; border:1px solid #ddd; border-radius:6px;">
            <option value="">-- Chọn --</option>
            <?php for($i=1;$i<=5;$i++): ?>
            <option value="<?= $i ?>"><?= str_repeat('⭐',$i) ?></option>
            <?php endfor; ?>
        </select>

        <!-- Comment -->
        <label style="font-weight:600;">Nhận xét:</label>
        <textarea name="comment" rows="4" required
            style="padding:10px; border:1px solid #ddd; border-radius:6px; resize:vertical;"></textarea>

        <!-- Upload -->
        <div>
            <label style="font-weight:600;">Ảnh (tùy chọn):</label><br>
            <input type="file" name="anh_review" accept="image/*" style="margin-top:5px;">
        </div>
        <div>
            <label style="font-weight:600;">Video (tùy chọn):</label><br>
            <input type="file" name="video_review" accept="video/*" style="margin-top:5px;">
        </div>

        <!-- Button -->
        <button type="submit" name="submit_review"
            style="background:#27ae60; color:white; padding:10px 18px; border:none; border-radius:6px; cursor:pointer; font-weight:600; transition:0.3s;">
            🚀 Gửi đánh giá
        </button>
    </form>

<?php else: ?>
    <p style="color:red;">Bạn cần <a href="?content=dangnhap" style="color:#2980b9; font-weight:600;">đăng nhập</a> để bình luận.</p>
<?php endif; } ?>
</div>

<!-- HIỂN THỊ REVIEW & REPLY -->
<style>
.review{border-bottom:1px solid #ccc; padding:10px 0; position:relative;}
.review strong{color:#2c3e50;}
.review p{margin:5px 0;}
.reply{background:#f1f1f1;padding:5px 10px;margin-top:5px;border-radius:6px;position:relative;}
.reply-owner{border-left:3px solid #3498db;}
.reply-admin{border-left:3px solid #e74c3c;}
.reply form{margin-top:5px;}
.reply-actions{position:absolute; right:5px; top:5px;}
textarea.inline-edit{width:100%; padding:4px; border-radius:4px;}
button.btn-reply, button.btn-edit{background:#3498db;color:white;padding:4px 10px;border:none;border-radius:4px;margin-top:5px; cursor:pointer;}
button.btn-cancel{background:#95a5a6;color:white;padding:4px 10px;border:none;border-radius:4px;margin-top:5px; cursor:pointer;}
</style>

<div>

<?php
$sql_reviews = "
    SELECT dg.*, tk.avatar, tk.username 
    FROM danhgia dg
    LEFT JOIN taikhoan tk ON dg.id_nguoidung = tk.id
    WHERE dg.id_sanpham = $id_sp
";

if ($role != 'admin') {
    $sql_reviews .= " AND dg.trangthai = 0";
}

$sql_reviews .= " ORDER BY dg.ngaydat DESC";

$res_reviews = $conn->query($sql_reviews);

if ($res_reviews && $res_reviews->num_rows > 0):
    while ($r = $res_reviews->fetch_assoc()):
        $ten_hien_thi = !empty($r['username']) ? $r['username'] : "Người dùng";
        $id_review = $r['id'];
        $likes_review = $r['likes'];

        // Avatar
        $avatar = "img/default_user.png";
        if (!empty($r['avatar'])) {
            if (file_exists(__DIR__."/uploads/".$r['avatar'])) $avatar = "uploads/".htmlspecialchars($r['avatar']);
            elseif (file_exists(__DIR__."/".$r['avatar'])) $avatar = htmlspecialchars($r['avatar']);
        }
?>
<div class="review" id="review-<?= $id_review ?>">
    <div style="display:flex; align-items:flex-start; gap:10px; margin-bottom:15px;">
        <img src="<?= $avatar ?>" alt="Avatar" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid #27ae60;">

        <div style="flex:1;">
            <strong><?= htmlspecialchars($ten_hien_thi) ?></strong>
            <?php if ($r['id_nguoidung'] == $user_id): ?>
                (<?= $role=='admin' ? 'Quản trị' : 'Khách hàng' ?>)
            <?php elseif (!empty($r['role']) && $r['role']=='admin'): ?>
                (Quản trị)
            <?php else: ?>
                (Khách hàng)
            <?php endif; ?>
            - <?= str_repeat('⭐', $r['rating']) ?><br>
            <p><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
            <small style="color:#888;"><?= $r['ngaydat'] ?></small><br>

            <!-- Like button -->
<!-- Like button -->
<button 
    id="likeBtn_review_<?= $r['id'] ?>" 
    class="like-btn <?= (!empty($_SESSION['liked']['review'][$r['id']])) ? 'liked' : '' ?>" 
    data-id="<?= $r['id'] ?>" 
    data-type="review">
    ❤️ <span class="like-count"><?= $r['likes'] ?></span>
</button>

            <!-- Admin/Owner actions -->
            <?php if ($role=='admin' || $r['id_nguoidung']==$user_id): ?>
                <div class="review-actions">
                    <a href="?content=chitiet&id=<?= $id_sp ?>&edit_review=<?= $id_review ?>">✏️ Sửa</a> | 
                    <a href="?content=chitiet&id=<?= $id_sp ?>&delete_review=<?= $id_review ?>" onclick="return confirm('Xóa review?');">🗑️ Xóa</a>
                    <?php if($role=='admin'): ?>
                        | <a href="?content=chitiet&id=<?= $id_sp ?>&toggle_hide=<?= $id_review ?>">
                            <?= $r['trangthai'] ? 'Hiện' : 'Ẩn' ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Ảnh/Video review -->
            <?php if(!empty($r['anh_review']) && file_exists('uploads/'.$r['anh_review'])): ?>
                <img src="uploads/<?= $r['anh_review'] ?>" style="max-width:200px; margin-top:5px; border-radius:6px;"><br>
            <?php endif; ?>
            <?php if(!empty($r['video_review']) && file_exists('uploads/'.$r['video_review'])): ?>
                <video controls style="max-width:300px; margin-top:5px; border-radius:6px;">
                    <source src="uploads/<?= $r['video_review'] ?>" type="video/mp4">
                    Trình duyệt không hỗ trợ video.
                </video><br>
            <?php endif; ?>

         <!-- Replies -->
<?php
$res_reply = $conn->query("
    SELECT ph.*, tk.username as user_name, tk.avatar as user_avatar 
    FROM phanhoi_review ph 
    LEFT JOIN taikhoan tk ON ph.id_nguoidung = tk.id 
    WHERE ph.review_id=$id_review 
    ORDER BY ph.ngaydat ASC
");
if ($res_reply && $res_reply->num_rows > 0):
    while ($rep = $res_reply->fetch_assoc()):
        $owner_class = ($rep['id_nguoidung'] == $user_id) ? 'reply-owner' : 'reply-admin';
        $is_owner = ($role=='admin' || $rep['id_nguoidung']==$user_id);

        // Avatar reply
        $avatar_reply = "img/default_user.png";
        if (!empty($rep['user_avatar'])) {
            if (file_exists(__DIR__."/uploads/".$rep['user_avatar'])) $avatar_reply = "uploads/".htmlspecialchars($rep['user_avatar']);
            elseif (file_exists(__DIR__."/".$rep['user_avatar'])) $avatar_reply = htmlspecialchars($rep['user_avatar']);
        }
?>
<div class="reply <?= $owner_class ?>" id="reply-<?= $rep['id'] ?>" style="margin-left:20px; display:flex; gap:10px; align-items:flex-start;">
    <img src="<?= $avatar_reply ?>" alt="Avatar" style="width:50px;height:50px;border-radius:50%;object-fit:cover;border:1px solid #27ae60;">
    <div>
        <strong><?= htmlspecialchars($rep['user_name']) ?></strong>: 
        <span class="reply-text"><?= nl2br(htmlspecialchars($rep['comment'])) ?></span>
        <br>
        <small style="color:#888;"><?= $rep['ngaydat'] ?></small><br>

        <!-- Like reply -->
<button 
    id="likeBtn_reply_<?= $rep['id'] ?>" 
    class="like-btn <?= (!empty($_SESSION['liked']['reply'][$rep['id']])) ? 'liked' : '' ?>" 
    data-id="<?= $rep['id'] ?>" 
    data-type="reply">
    ❤️ <span class="like-count"><?= $rep['likes'] ?></span>
</button>


        <?php if($is_owner): ?>
            <div class="reply-actions">
                <a href="#" class="edit-reply" data-id="<?= $rep['id'] ?>">✏️</a> | 
                <a href="?content=chitiet&id=<?= $id_sp ?>&delete_reply=<?= $rep['id'] ?>" onclick="return confirm('Xóa phản hồi này?');">🗑️</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
    endwhile; 
endif; 
?>
            <!-- Form reply -->
            <?php if($user_id): ?>
            <div class="reply-form" style="margin-left:20px; margin-top:5px;">
                <form action="?content=chitiet&id=<?= $id_sp ?>" method="POST">
                    <input type="hidden" name="review_id" value="<?= $id_review ?>">
                    <textarea name="reply_comment" rows="2" placeholder="Viết phản hồi..." required style="width:100%; padding:5px; border-radius:4px;"></textarea><br>
                    <button type="submit" name="submit_reply" class="btn-reply">Trả lời</button>
                </form>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>
<?php
    endwhile;
endif;
?>

<script>
document.querySelectorAll('.like-btn').forEach(btn => {
    btn.addEventListener('click', e => {
        const id = btn.dataset.id;
        fetch('like.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}&type=review`
        })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                btn.querySelector('.like-count').textContent = data.likes;
            }
        });
    });
});
</script>

<style>/* Trước: */
.review-actions, .reply-actions{
    position:absolute; right:5px; top:5px;
}

/* Sau: */
.review-actions, .reply-actions{
    position: static; /* bỏ absolute */
    margin-top:5px;
    font-size:14px;
}

.review-actions a, .reply-actions a{
    display:inline-block;
    margin-right:10px;
    color:#3498db;
    text-decoration:none;
}

@media(max-width:600px){
    .review-actions a, .reply-actions a{
        font-size:13px;
        margin-right:8px;
    }
}
</style>