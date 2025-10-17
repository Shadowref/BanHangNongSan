<?php
ob_start(); // ✅ Thêm để chống lỗi header already sent
$user_id = $_SESSION['user_id'] ?? 0;
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
$conn = new mysqli("localhost", "root", "", "banhangonline");
$conn->set_charset("utf8");
if ($conn->connect_error) {
  die("Kết nối thất bại: " . $conn->connect_error);
}

if(isset($_GET['ajax']) && $_GET['ajax']==1){
    if(!$user_id){
        echo json_encode(['success'=>false]);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT t.id, t.message, t.created_at,
               IFNULL(tr.is_read,0) AS is_read
        FROM thongbao t
        LEFT JOIN thongbao_read tr 
               ON t.id = tr.thongbao_id AND tr.user_id = ?
        ORDER BY t.created_at DESC
        LIMIT 5
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = [];
    while($row = $result->fetch_assoc()){
        $notifications[] = $row;
    }
    $stmt->close();

    // Đếm số thông báo chưa đọc
    $stmt2 = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM thongbao t
        LEFT JOIN thongbao_read tr 
               ON t.id = tr.thongbao_id AND tr.user_id = ?
        WHERE tr.is_read IS NULL OR tr.is_read=0
    ");
    $stmt2->bind_param("i",$user_id);
    $stmt2->execute();
    $count_result = $stmt2->get_result()->fetch_assoc();
    $noti_count = $count_result['total'] ?? 0;
    $stmt2->close();

    echo json_encode([
        'success'=>true,
        'notifications'=>$notifications,
        'count'=>$noti_count
    ]);
    exit;
}

if(isset($_GET['mark_read']) && $_GET['mark_read']==1){
    if($user_id){
        $stmt = $conn->prepare("
            INSERT INTO thongbao_read (thongbao_id, user_id)
            SELECT id, ? FROM thongbao
            WHERE id NOT IN (SELECT thongbao_id FROM thongbao_read WHERE user_id=?)
        ");
        $stmt->bind_param("ii",$user_id,$user_id);
        $stmt->execute();

        $stmt2 = $conn->prepare("
            UPDATE thongbao_read SET is_read=1 
            WHERE user_id=? AND is_read=0
        ");
        $stmt2->bind_param("i",$user_id);
        $stmt2->execute();
        $stmt2->close();
        $stmt->close();
    }
    echo json_encode(['success'=>true]);
    exit;
}

// Lấy 1 sản phẩm ngẫu nhiên
$rand_id = 0;
$sql_random = "SELECT id FROM sanpham ORDER BY RAND() LIMIT 1";
$res_random = mysqli_query($conn, $sql_random);
if($res_random && mysqli_num_rows($res_random) > 0){
    $row_random = mysqli_fetch_assoc($res_random);
    $rand_id = $row_random['id'];
}



?>
<!DOCTYPE html>
<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="style/giaodien.css" />
  <title>Thế Giới Nông Sản Việt Nam</title>
  <!-- Logo (favicon) -->
<link rel="icon" href="img/logochinh.jpg" type="image/jpeg">

  <!-- hoặc có thể dùng .ico -->
  <!-- <link rel="shortcut icon" href="img/logo.ico" type="image/x-icon"> -->
</head>

<script>
  // thời gian chờ
  let timeout = 1800000; // 3 phút
  let timer;

  function resetTimer() {
    clearTimeout(timer);
    timer = setTimeout(() => {
      // khi hết giờ, gọi logout
      window.location.href = "admin/logout.php";
    }, timeout);
  }

  // reset timer khi có hành động
  window.onload = resetTimer;
  document.onmousemove = resetTimer;
  document.onkeydown = resetTimer;
</script>

<body>
  <div class="waper">

<!-- Banner Carousel -->
<div class="banner-carousel" style="position:relative; overflow:hidden; border-radius:12px; max-width:100%; height:350px; margin-bottom:30px;">

  <!-- Ảnh slide -->
  <img id="slide" src="img/chinh1.jpg" style="width:100%; height:100%; object-fit:cover; transition:opacity 0.8s ease-in-out;">

<div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);
            text-align:center; 
            background:rgba(255,255,255,0.85);  
            padding:25px 40px; 
            border-radius:16px; 
            box-shadow:0 6px 16px rgba(0,0,0,0.25);">

  <h1>
    🔥 Chào mừng đến với Thế Giới Nông Sản Việt Nam 🔥
  </h1>

  <p style="font-size:18px; margin:12px 0 18px 0; color:#444;">
    Săn deal siêu sốc – Nhanh tay kẻo hết!
  </p>



<!-- Nút Mua ngay -->
<a href="index.php?content=chitiet&id=<?= $rand_id ?>" 
   style="display:inline-block; padding:12px 28px; 
          background:linear-gradient(135deg,#e74c3c,#ff7675); 
          color:#fff; font-weight:600; 
          border-radius:8px; text-decoration:none; 
          box-shadow:0 4px 12px rgba(0,0,0,0.25);
          transition:all 0.3s;">
    🚀 Mua ngay
</a>
</div>

<style>
h1 {
  font-size: 38px;
  margin: 0;
  font-weight: 900;

  /* Gradient chữ */
  background: linear-gradient(90deg, #ff6a00, #ee0979, #00c6ff);
  background-size: 200% auto;

  /* Clip background cho text */
  -webkit-background-clip: text;   /* Chrome, Safari */
  background-clip: text;           /* Chuẩn cho Firefox */

  /* Fill color */
  -webkit-text-fill-color: transparent; /* Chrome, Safari */
  color: transparent;                   /* fallback cho Firefox */

  /* Hiệu ứng bóng */
  text-shadow: 2px 3px 8px rgba(0,0,0,0.25);

  /* Animation gradient */
  animation: shine 4s infinite linear;
}

@keyframes shine {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

</style>



  <!-- Nút Prev/Next -->
  <button id="prev" style="position:absolute; top:50%; left:15px; transform:translateY(-50%);
                            background:rgba(0,0,0,0.4); color:#fff; border:none; font-size:24px;
                            padding:8px 12px; border-radius:50%; cursor:pointer; transition:0.3s;">&#10094;</button>
  <button id="next" style="position:absolute; top:50%; right:15px; transform:translateY(-50%);
                            background:rgba(0,0,0,0.4); color:#fff; border:none; font-size:24px;
                            padding:8px 12px; border-radius:50%; cursor:pointer; transition:0.3s;">&#10095;</button>

  <!-- Dấu chấm slide -->
  <div id="dots" style="position:absolute; bottom:15px; left:50%; transform:translateX(-50%); display:flex; gap:8px;">
    <span class="dot" style="width:12px; height:12px; background:#fff; border-radius:50%; opacity:0.5; cursor:pointer;"></span>
    <span class="dot" style="width:12px; height:12px; background:#fff; border-radius:50%; opacity:0.5; cursor:pointer;"></span>
    <span class="dot" style="width:12px; height:12px; background:#fff; border-radius:50%; opacity:0.5; cursor:pointer;"></span>
  </div>
</div>

<script>
  const images = ["img/chinh1.jpg", "img/chinh2.jpg", "img/chinh3.jpg"];
  let index = 0;
  const slide = document.getElementById("slide");
  const dots = document.querySelectorAll("#dots .dot");

  function updateDots() {
    dots.forEach((d, i) => d.style.opacity = i === index ? '1' : '0.5');
  }

  function showSlide(newIndex) {
    slide.style.opacity = 0;
    setTimeout(() => {
      index = newIndex;
      slide.src = images[index];
      slide.style.opacity = 1;
      updateDots();
    }, 400);
  }

  document.getElementById("prev").onclick = () => showSlide((index - 1 + images.length) % images.length);
  document.getElementById("next").onclick = () => showSlide((index + 1) % images.length);

  dots.forEach((dot, i) => {
    dot.onclick = () => showSlide(i);
  });

  // Tự động chuyển slide
  setInterval(() => showSlide((index + 1) % images.length), 5000);
  
  updateDots(); // hiển thị dot ban đầu
</script>


<div class="menu" style="background:#7a7a7a; display:flex; align-items:center; flex-wrap:nowrap; padding:5px 10px;">
  <!-- Menu chính -->
  <ul style="display:flex; align-items:center; gap:15px; list-style:none; padding:0; margin:0; flex:1;">
    <li><a href="./index.php" style="text-decoration:none; color:#333; font-weight:bold;">Trang chủ</a></li>
    <?php include 'menu/menu_top.php'; ?>

    <!-- Container thông báo & giỏ hàng -->
    <li style="margin-left:auto; display:flex; align-items:center; gap:20px; position:relative;">

    <!-- Thanh tìm kiếm trong menu -->
<li style="flex:1; max-width:500px;">
  <form method="GET" action="index.php" class="search-bar" style="position:relative; display:flex; align-items:center;">
    
    <!-- Icon kính lúp bên trái -->
    <img class="searchIcon" src="img/timkiem.png" alt="Tìm kiếm" 
         style="width:20px; height:20px; position:absolute; left:12px;">

    <!-- Input -->
    <input type="text" class="input" name="search" placeholder="Nhập từ khóa..."
      value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
      style="flex:1; padding:8px 40px 8px 40px; border:1px solid #ccc; border-radius:25px; font-size:14px; outline:none;">

    <!-- Button search -->
    <button type="submit" class="micButton" style="
        position:absolute; 
        right:8px; 
        background:#0984e3; 
        border:none; 
        border-radius:50%; 
        width:32px; 
        height:32px; 
        cursor:pointer; 
        display:flex; 
        align-items:center; 
        justify-content:center;
        box-shadow:0 2px 6px rgba(0,0,0,0.2);
    ">
      <img class="micIcon" src="img/timkiem.png" alt="Tìm kiếm" style="width:16px; height:16px; filter:brightness(100);">
    </button>

  </form>
</li>


    <!-- Thông tin User -->
 <?php
  if (isset($_SESSION['username'])) {
    $user = $_SESSION['username'];

    // Kết nối DB
    $conn = new mysqli("localhost", "root", "", "banhangonline");
    $conn->set_charset("utf8");
    if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

    // Lấy avatar + điểm
    $stmt = $conn->prepare("SELECT avatar, diem FROM taikhoan WHERE username=? LIMIT 1");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $avatar = !empty($row['avatar']) ? $row['avatar'] : 'img/avatar.png';
    $diem   = $row['diem'] ?? 0;

    function formatDiem($number) {
        if ($number >= 1000000000) return round($number/1000000000,1).'B';
        elseif ($number >= 1000000) return round($number/1000000,1).'M';
        elseif ($number >= 1000) return round($number/1000,1).'K';
        else return $number;
    }
    ?>

    <!-- Khối user -->
    <div style="
        display:flex; align-items:center;
        background: linear-gradient(135deg, #74b9ff, #0984e3);
        padding:6px 14px;
        border-radius:40px;
        color:#fff;
        font-weight:bold;
        box-shadow:0 3px 8px rgba(0,0,0,0.2);
    ">
      <img src="<?php echo htmlspecialchars($avatar); ?>"
           alt="Avatar"
           style="width:32px; height:32px; border-radius:50%; margin-right:8px; object-fit:cover;">

      <a href="index.php?content=thongtin"
         style="color:#fff; font-weight:bold; text-decoration:none; margin-right:12px;">
         <?php echo htmlspecialchars($user); ?> 👋
      </a>

      <div style="display:flex; align-items:center; background:#fff; color:#2c3e50; padding:4px 10px; border-radius:20px;">
        <img src="img/coin.png" alt="Điểm" style="width:18px; height:18px; margin-right:5px;">
        <span style="font-size:13px;"><?php echo formatDiem($diem); ?> điểm</span>
      </div>
    </div>

       <p style="text-align: center;">
  <a href="./admin/logout.php" class="btn-logout">
    <img src="img/logout.png" alt="Thoát">
    <span class="tooltip">Đăng xuất</span>
  </a>
</p>

<style>
  .btn-logout {
    position: relative;
    display: inline-block;
  }

  .btn-logout img {
    width: 40px;
    height: 40px;
    cursor: pointer;
    transition: transform 0.3s;
  }

  /* Tooltip mặc định ẩn */
  .btn-logout .tooltip {
    visibility: hidden;
    opacity: 0;
    width: max-content;
    background-color: #333;
    color: #fff;
    text-align: center;
    padding: 5px 10px;
    border-radius: 5px;
    position: absolute;
    bottom: -35px; /* chỉnh vị trí dưới icon */
    left: 50%;
    transform: translateX(-50%);
    transition: opacity 0.3s;
    font-size: 14px;
    white-space: nowrap;
    z-index: 1;
  }

  /* Khi hover thì hiện tooltip */
  .btn-logout:hover .tooltip {
    visibility: visible;
    opacity: 1;
  }

  /* Hiệu ứng icon */
  .btn-logout:hover img {
    transform: scale(1.2);
  }
</style>
  <?php } else { ?>
  <?php } ?>
  
     <!-- Thông báo -->
<div style="position:relative; display:inline-block;">
    <img id="notiIcon" src="img/chuong.png" alt="Thông báo" style="
        width:36px;
        cursor:pointer;
        transition: transform 0.2s;
    ">
    <span id="notiCount" style="
        position:absolute;
        top:-5px;
        right:-5px;
        background:#ff4757;
        color:white;
        font-size:12px;
        font-weight:bold;
        padding:2px 6px;
        border-radius:50%;
        min-width:16px;
        text-align:center;
        box-shadow: 0 0 3px rgba(0,0,0,0.3);
    ">0</span>

    <div id="notiPopup" style="
        display:none;
        position:absolute;
        right:0;
        top:45px;
        background:#fff;
        border-radius:10px;
        width:300px;
        max-height:350px;
        overflow-y:auto;
        box-shadow:0 8px 20px rgba(0,0,0,0.3);
        z-index:1000;
        border:2px solid #ff6b81;
        animation: fadeIn 0.3s ease;
    ">
        <ul id="notiList" style="
            list-style:none;
            margin:0;
            padding:0;
        ">
            <li style="
                padding:12px 15px;
                border-bottom:1px solid #eee;
                transition: background 0.2s;
                cursor:pointer;
            ">Đang tải...</li>
        </ul>
        <div style="
            text-align:center;
            padding:8px;
            font-size:13px;
            color:#888;
            background:#f9f9f9;
            border-top:1px solid #eee;
        "></div>
    </div>
</div>

<style>
/* Hover icon */
#notiIcon:hover {
    transform: scale(1.2);
}

/* Hover từng thông báo */
#notiList li:hover {
    background: #ff6b81;
    color:white;
}

/* Animate */
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}
</style>
      <!-- Giỏ hàng -->
      <?php
      // --- PHẦN GIỎ HÀNG ---
      $user_id = $_SESSION['user_id'] ?? 0;
      $count = 0;
      if ($user_id) {
        // dùng @ để tránh in ra warning làm hỏng header
        $conn = @new mysqli("localhost", "root", "", "banhangonline");
        if (!$conn->connect_error) {
          $sql = "SELECT SUM(soluong) AS total FROM giohang WHERE taikhoan_id = ?";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param("i", $user_id);
          $stmt->execute();
          $result = $stmt->get_result()->fetch_assoc();
          $count = $result['total'] ?? 0;
          $stmt->close();
          $conn->close();
        }
      }
      ?>

      <div style="position:relative;">
        <a href="index.php?content=giohang" style="display:inline-block; position:relative;">
          <img src="img/giohang.png" alt="Giỏ hàng" style="width:32px;">
          <span style="
                position:absolute;
                top:-5px;
                right:-5px;
                background:#1dd1a1;
                color:white;
                font-size:12px;
                font-weight:bold;
                padding:2px 6px;
                border-radius:50%;
                min-width:18px;
                text-align:center;
            "><?php echo $count; ?></span>
        </a>
      </div>

    </li>
  </ul>
</div>


<script>
const notiIcon = document.getElementById('notiIcon');
const notiPopup = document.getElementById('notiPopup');
const notiList = document.getElementById('notiList');
const notiCount = document.getElementById('notiCount');
let notifications = [];

// Hiển thị popup
notiIcon.addEventListener('click', function(e){
    e.stopPropagation();
    notiPopup.style.display = notiPopup.style.display==='block' ? 'none':'block';
    if(notiPopup.style.display==='block'){
        markAsRead();
    }
});

// Ẩn popup khi click ra ngoài
document.addEventListener('click', ()=>{ notiPopup.style.display='none'; });

// Render danh sách thông báo
function renderNoti(){
    notiList.innerHTML = '';
    if(notifications.length===0){
        notiList.innerHTML='<li style="padding:10px;">Không có thông báo nào</li>';
        return;
    }
    notifications.forEach(noti=>{
        let li = document.createElement('li');
        li.style.padding='10px';
        li.style.borderBottom='1px solid #eee';
        li.style.fontWeight = noti.is_read==0 ? 'bold':'normal';
        li.innerHTML = `${noti.message}<br><small style="color:#888;">${noti.created_at}</small>`;
        notiList.appendChild(li);
    });
}

// Load thông báo qua AJAX
function loadNoti(){
    fetch('index.php?ajax=1')
    .then(r=>r.json())
    .then(data=>{
        if(data.success){
            notifications = data.notifications;
            notiCount.textContent = data.count;
            renderNoti();
        }
    });
}

// Đánh dấu đã đọc khi mở popup
function markAsRead(){
    fetch('index.php?mark_read=1')
    .then(r=>r.json())
    .then(data=>{
        if(data.success){
            notiCount.textContent = 0;
            notifications.forEach(n=>n.is_read=1);
            renderNoti();
        }
    });
}

// Load lần đầu
loadNoti();
// Load lại mỗi 5 giây
setInterval(loadNoti,5000);
</script>

    <div class="conten">
      <div class="left">
     <p style="text-align:center; 
          background: linear-gradient(90deg, #4CAF50, #2E7D32); 
          color:#FFF; 
          padding:12px; 
          font-weight:bold; 
          font-size:20px; 
          margin:0; 
          border-radius:8px;
          letter-spacing:1px;
          box-shadow:0 3px 6px rgba(0,0,0,0.2);">
  🌿 Sản phẩm
</p>
        <ul>
          <?php include 'menu/menu_top.php'; ?>
        </ul>

        <style></style>

              <p style="
          text-align:center;
          background: linear-gradient(90deg, #ff9800, #e65100);
          color:#fff;
          padding:12px;
          font-weight:bold;
          font-size:20px;
          margin:0;
          border-radius: 8px;
          letter-spacing:1px;
          text-transform: uppercase;
          box-shadow:0 3px 6px rgba(0,0,0,0.2);
        ">
          🏷️ Thương hiệu
        </p>

        <ul>
          <?php include 'menu/menu_left.php'; ?>
        </ul>

      </div>
      <div class="khungchinh">
        <p style="text-align:center; background:#4CAF50; color:#FFF; padding:7px; font-weight:bold; font-size:large;">
          DANH SÁCH CÁC SẢN PHẨM
        </p>
        <div class="danhsachall">
          <ul>
            <?php
            // Lấy biến action và content từ URL
            $action = isset($_GET['action']) ? $_GET['action'] : "";
            $content = isset($_GET['content']) ? $_GET['content'] : "";

            // Xử lý hiển thị nội dung
            if (!empty($content)) {
              switch ($content) {
                case 'hang':
                  include('laytheohang.php');
                  break;
                case 'loai':
                  include('laydsloai.php');
                  break;
                case 'dangky':
                  include('./admin/fr_DKNguoiDung.php');
                  break;
                case 'chitiet':
                  include('chitiet.php');
                  break;
                case 'sanpham':
                  include('layallsp.php'); // hoặc file hiển thị sản phẩm
                  break;
                case 'khuyenmai':
                  include('khuyenmai.php'); // bạn tạo file này để liệt kê khuyến mãi
                  break;
                case 'lienhe':
                  include('lienhe.php'); // bạn tạo file này để hiển thị form liên hệ
                  break;
                case 'lichsu':
                  include('./card/lichsu_giaodich.php'); // file hiển thị lịch sử giao dịch
                  break;
                  case 'donhanguser':
                  include('./card/donhang_user.php'); // file hiển thị đơn hàng của người dùng
                  break;
                case 'thongtin':
                  include('thongtin.php');
                  break;
                   case 'sanphammoi':
                  include('sanphammoi.php');
                  break;
                   case 'banchay':
                  include('banchay.php');
                  break;
                   case 'danhgiacao':
                  include('danhgiacao.php');
                  break;
                   case 'giare':
                  include('giare.php');
                  break;
                   case 'giacao':
                  include('giacao.php');
                  break;
                default:
                  echo "<li></li>";
                  break;
              }
            } else {
              if (!empty($_GET['search'])) {
                include('timkiem.php'); // file query tìm kiếm
              } else {
                include('layallsp.php'); // file hiển thị tất cả sản phẩm
              }
            }
            ?>
          </ul>
        </div>

        <div style="background-color: #dfe6e9">
          <?php
          if (isset($_GET['action'])) {
            $action = $_GET['action'];
          } else $action = "";
          if (isset($_GET['content'])) {
            switch ($_GET['content']) {

              case 'add': {
                  include('./card/dathang.php');
                  break;
                }
              case 'giohang': {
                  include('./card/view_card.php');
                  break;
                }
              case 'delete': {
                  include('./card/dathang.php');
                  break;
                }
              case 'update': {
                  include('./card/dathang.php');
                  break;
                }
              default;
                break;
            }
          }
          ?>
        </div> <!-- chi tiết -->
      </div>
      <div class="right">
        <ul>
          <form action="" method="post">
            <?php
            // Kết nối database
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "banhangonline";

            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
              die("Kết nối thất bại: " . $conn->connect_error);
            }

            if (isset($_SESSION['username'])) {
              $user = $_SESSION['username'];
              $sql = "SELECT diem FROM taikhoan WHERE username = '$user' LIMIT 1";
              $result = mysqli_query($conn, $sql);
              $diem = 0;
              if ($row = mysqli_fetch_assoc($result)) {
                $diem = $row['diem'];
              }
            ?>
              <div style="
                          display: flex;
                          align-items: center;
                          justify-content: space-between;
                          padding: 10px 20px;
                          border-radius: 50px;
                          color: white;
                          font-weight: bold;                       
                          font-family: Arial, sans-serif;
                          margin-bottom: 10px;
                      ">
              </div>

              <div style="text-align: center; margin-top:10px;">
                <!-- Icon Lịch sử giao dịch -->
                    <div class="icon-wrapper">
                      <a href="index.php?content=lichsu">
                        <img src="img/buy1.png" alt="Lịch sử giao dịch" class="icon-btn">
                      </a>
                      <div class="tooltip">Lịch sử giao dịch</div>
                      <div class="popup-info">
                        <h4>Lịch sử giao dịch</h4>
                        <p>• Xem các giao dịch gần đây</p>
                        <p>• Kiểm tra số dư và điểm tích lũy</p>
                      </div>
                    </div>

                    <!-- Icon Đơn hàng -->
                    <div class="icon-wrapper">
                      <a href="index.php?content=donhanguser">
                        <img src="img/donhang.png" alt="Lịch sử đơn hàng" class="icon-btn">
                      </a>
                      <div class="tooltip">Lịch sử đơn hàng</div>
                      <div class="popup-info">
                        <h4>Đơn hàng của bạn</h4>
                        <p>• Xem chi tiết đơn đã mua</p>
                        <p>• Theo dõi trạng thái giao hàng</p>
                      </div>
                    </div>
                    <style>.icon-wrapper {
                      display:inline-block;
                      position:relative;
                      margin-right:10px;
                    }

                    .icon-btn {
                      width:70px;
                      height:70px;
                      cursor:pointer;
                      transition:transform 0.3s;
                    }
                    .icon-btn:hover {
                      transform:scale(1.1);
                    }

                    /* Tooltip chữ nhỏ */
                    .tooltip {
                      position:absolute;
                      bottom:-25px;
                      left:50%;
                      transform:translateX(-50%);
                      background:#333;
                      color:#fff;
                      padding:3px 8px;
                      border-radius:5px;
                      font-size:12px;
                      white-space:nowrap;
                      opacity:0;
                      pointer-events:none;
                      transition:opacity 0.3s;
                    }
                    .icon-wrapper:hover .tooltip {
                      opacity:1;
                    }

                    /* Popup thông tin */
                    .popup-info {
                      position:absolute;
                      bottom:50px;
                      left:50%;
                      transform:translateX(-50%);
                      background:#fff;
                      border:1px solid #ddd;
                      border-radius:8px;
                      box-shadow:0 4px 12px rgba(0,0,0,0.2);
                      width:220px;
                      padding:10px;
                      font-size:13px;
                      color:#333;
                      opacity:0;
                      pointer-events:none;
                      transition:all 0.3s;
                      z-index:100;
                    }
                    .icon-wrapper:hover .popup-info {
                      opacity:1;
                      bottom:60px;
                    }
                    .popup-info h4 {
                      margin:0 0 6px;
                      font-size:14px;
                      color:#0984e3;
                    }
                    </style>

              <?php } else { ?>
                <li style="list-style:none; text-align:center; margin-top:10px; font-family: Arial, sans-serif;">

                  <!-- Gọi form đăng nhập -->
                  <div style="margin-bottom:15px;">
                    <?php include 'dangnhap.php'; ?>
                  </div>

                  <!-- Đăng ký -->
                  <p style="font-weight:bold; font-size:16px; color:#333; margin-bottom:10px;">
                    Chưa có tài khoản?
                  </p>
                  <a href="index.php?content=dangky">
                    <img src="img/dangky.png"
                      alt="Đăng ký"
                      style="width:120px; height:auto; cursor:pointer; transition: transform 0.3s; display:block; margin:0 auto;">
                  </a>
                </li>

                <style>
                  /* Khi hover, hình ảnh phóng to nhẹ để đẹp hơn */
                  a img:hover {
                    transform: scale(1.1);
                  }
                </style>


                </li>
              <?php } ?>
          </form>
        </ul>



<!-- Wrapper ngoài cùng để căn giữa -->
<div style="display:flex; justify-content:center; margin-top:50px;">
  <div class="nav-wrapper" style="position:relative; text-align:center; display:inline-block;">

    <!-- Icon menu -->
    <div id="menu-icon" style="cursor:pointer; padding:10px 20px; border:2px solid #1abc9c; border-radius:25px; display:inline-flex; flex-direction:column; align-items:center; gap:5px; background:#fff;">
      <img src="img/menuu.png" alt="Menu" style="width:50px; height:50px;">
      <span style="font-weight:600; font-size:16px; color:#2c3e50;">Danh mục</span>
    </div>

    <!-- Dropdown menu -->
    <div id="dropdown-menu" style="display:none; position:absolute; top:70px; left:50%; transform:translateX(-50%); background:#fff; border:2px solid #1abc9c; border-radius:10px; min-width:200px; box-shadow:0 4px 12px rgba(0,0,0,0.2); z-index:100;">
      <a href="index.php?content=sanpham" style="display:block; padding:10px 20px; text-decoration:none; color:#2c3e50; text-align:center;">Tất cả sản phẩm</a>
      <a href="index.php?content=sanphammoi" style="display:block; padding:10px 20px; text-decoration:none; color:#2c3e50; text-align:center;">Sản phẩm mới</a>
      <a href="index.php?content=banchay" style="display:block; padding:10px 20px; text-decoration:none; color:#2c3e50; text-align:center;">Bán chạy</a>
      <a href="index.php?content=danhgiacao" style="display:block; padding:10px 20px; text-decoration:none; color:#2c3e50; text-align:center;">Đánh giá cao</a>
      <a href="index.php?content=giare" style="display:block; padding:10px 20px; text-decoration:none; color:#2c3e50; text-align:center;">Giá rẻ</a>
      <a href="index.php?content=giacao" style="display:block; padding:10px 20px; text-decoration:none; color:#2c3e50; text-align:center;">Giá cao</a>
      <a href="index.php?content=khuyenmai" style="display:block; padding:10px 20px; text-decoration:none; color:#2c3e50; text-align:center;">Khuyến mãi</a>
      <a href="index.php?content=lienhe" style="display:block; padding:10px 20px; text-decoration:none; color:#2c3e50; text-align:center;">Liên hệ</a>
    </div>

  </div>
</div>

<style>
  /* Hover để hiện menu */
  .nav-wrapper:hover #dropdown-menu {
    display: block !important;
  }

  /* Hiệu ứng hover item */
  #dropdown-menu a {
    transition: background 0.3s, color 0.3s;
    border-radius: 5px;
  }

  #dropdown-menu a:hover {
    background-color: #1abc9c;
    color: #fff;
  }
</style>

<?php
if(!isset($_SESSION['user_id'])){
    echo '<p></p>';
    return;
}
?>
<!-- Icon tròn góc dưới -->
<div id="chat-icon" style="
    position:fixed;
    bottom:20px;
    right:20px;
    width:60px;
    height:60px;
    border-radius:50%;
    overflow:hidden;
    cursor:pointer;
    z-index:9999;
    box-shadow:0 4px 8px rgba(0,0,0,0.2);
">
   <img src="./img/bot.png" style="width:100%;height:100%;object-fit:cover;">
</div>

<!-- Popup thông báo tin nhắn mới -->
<div id="chat-popup" style="
    position:fixed;
    bottom:90px;
    right:90px;
    background:#ffffff;  /* nền trắng */
    color:#e74c3c;       /* chữ đỏ */
    padding:12px 16px;
    border-radius:8px;
    box-shadow:0 4px 12px rgba(0,0,0,0.2);
    display:none;
    font-family:Arial,sans-serif;
    cursor:pointer;
    z-index:10000;
    font-weight:bold;
">
    💬 Bạn có tin nhắn mới từ Admin
</div>


<script>
const adminId = 1;
const chatIcon = document.getElementById('chat-icon');
const chatPopup = document.getElementById('chat-popup');
let chatContainer = null;
let lastMessageId = 0;

// Mở chat khi click icon hoặc popup
function openChat() {
    chatPopup.style.display='none'; // ẩn popup
    if(!chatContainer){
        chatContainer = document.createElement('div');
        chatContainer.id = "chat-container";
        chatContainer.style.cssText = `
            position:fixed;
            bottom:90px;
            right:20px;
            width:350px;
            height:450px;
            z-index:9999;
            font-family:Arial,sans-serif;
            border-radius:10px;
            overflow:hidden;
            box-shadow:0 4px 12px rgba(0,0,0,0.2);
            background:#fff;
            display:flex;
            flex-direction:column;
        `;
        chatContainer.innerHTML = `
            <div id="chat-header" style="background:#0984e3;color:#fff;padding:10px;">
                💬 Chat với Admin
                <span id="chat-close" style="float:right;cursor:pointer;font-weight:bold;">&#10005;</span>
            </div>
            <div id="chat-content" style="flex:1;padding:10px;overflow-y:auto;background:#f7f9fa;"></div>
            <div id="chat-input" style="padding:10px;border-top:1px solid #ddd;display:flex;background:#fff;">
                <input id="chat-message" style="flex:1;padding:8px;border:1px solid #ccc;border-radius:20px;outline:none;" placeholder="Nhập tin nhắn...">
                <button id="chat-send" style="padding:8px 16px;background:#0984e3;color:#fff;border:none;border-radius:20px;margin-left:6px;cursor:pointer;font-weight:bold;">Gửi</button>
            </div>
        `;
        document.body.appendChild(chatContainer);

        document.getElementById('chat-close').onclick = ()=>{
            chatContainer.remove();
            chatContainer = null;
            clearInterval(window.chatInterval);
        };
        document.getElementById('chat-send').onclick = sendMessage;
        document.getElementById('chat-message').addEventListener('keypress', function(e){
            if(e.key === 'Enter'){ sendMessage(); }
        });
    }
    loadChat();
    if(window.chatInterval) clearInterval(window.chatInterval);
    window.chatInterval = setInterval(loadChat,2000);
}

chatIcon.addEventListener('click', openChat);
chatPopup.addEventListener('click', openChat);

// Load tin nhắn
function loadChat(){
    fetch('chat_load_user.php')
    .then(r=>r.json())
    .then(list=>{
        const chatContent = document.getElementById('chat-content');
        if(!chatContent) return;
        chatContent.innerHTML='';

        list.forEach(m=>{
            const isMe = m.nguoigui_id != adminId;
            const div = document.createElement('div');
            div.style.display='flex'; div.style.margin='6px 0';

            const bub = document.createElement('div');
            bub.style.padding='10px 14px';
            bub.style.borderRadius='16px';
            bub.style.maxWidth='75%';
            bub.style.wordWrap='break-word';
            bub.style.position='relative';
            bub.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)';
            bub.style.fontSize='14px';
            
            if(isMe){
                div.style.justifyContent='flex-end';
                bub.style.background='#0984e3';
                bub.style.color='#fff';
            }else{
                div.style.justifyContent='flex-start';
                bub.style.background='#ffeaa7';
                bub.style.color='#2d3436';
                bub.style.fontWeight='bold';
            }
            bub.textContent = m.noidung;

            const time = document.createElement('div');
            time.style.fontSize='11px';
            time.style.color='#636e72';
            time.style.marginTop='4px';
            time.style.textAlign='right';
            time.textContent = new Date(m.thoigian).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});
            bub.appendChild(time);

            div.appendChild(bub);
            chatContent.appendChild(div);

            if(!isMe && m.id > lastMessageId) lastMessageId = m.id;
        });
        if(chatContent) chatContent.scrollTop = chatContent.scrollHeight;
    });
}

// Gửi tin nhắn
function sendMessage(){
    const input = document.getElementById('chat-message');
    if(!input) return;
    const msg = input.value.trim();
    if(!msg) return;
    fetch('chat_save_user.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'message='+encodeURIComponent(msg)
    }).then(r=>r.json()).then(j=>{
        if(j.success){
            input.value='';
            loadChat();
        }
    });
}

// Hiển thị popup tin nhắn mới
function checkNewAdminMessage(){
    fetch('chat_load_user.php')
    .then(res => res.json())
    .then(list => {
        if(list.length === 0) return;
        const last = list[list.length-1];
        if(last.nguoigui_id === adminId && last.id > lastMessageId){
            chatPopup.style.display='block';
            lastMessageId = last.id;
        }
    });
}

setInterval(checkNewAdminMessage,3000);
</script>

      </div>

    </div> <!-- kết thức conten -->
    <div class="clear"></div>

    <div class="footer">
      <!-- Footer top -->
      <div class="footer-top">
        <p class="footer-country">
          Quốc gia & Khu vực: Singapore | Indonesia | Thái Lan | Malaysia | Việt Nam | Philippines | Brazil | México | Colombia | Chile | Đài Loan
        </p>

        <p class="footer-links">
          <a href="#">CHÍNH SÁCH BẢO MẬT</a> |
          <a href="#">QUY CHẾ HOẠT ĐỘNG</a> |
          <a href="#">CHÍNH SÁCH VẬN CHUYỂN</a> |
          <a href="#">CHÍNH SÁCH TRẢ HÀNG VÀ HOÀN TIỀN</a>
        </p>

        <div class="footer-logos">
          <img src="img/amazon.png" alt="Amazon">
          <img src="img/shopee.jpg" alt="Shopee">
          <img src="img/lazada.jpg" alt="Lazada">
        </div>
      </div>

      <!-- Footer bottom -->
  <div class="footer-bottom" style="font-size:14px; line-height:1.6; color:#555;">
    <p><strong>Công ty TNHH ABC Đắk Lắk</strong></p>
    <p>Địa chỉ: Số 123, Đường Lê Duẩn, Phường Tân Lợi, Thành phố Buôn Ma Thuột, Tỉnh Đắk Lắk, Việt Nam</p>
    <p>Chăm sóc khách hàng: Gọi tổng đài 1800-XXX-XXX (miễn phí) hoặc trò chuyện trực tuyến qua website</p>
    <p>Người chịu trách nhiệm quản lý nội dung: Nguyễn Văn A</p>
    <p>Mã số doanh nghiệp: 6001234567 do Sở Kế hoạch và Đầu tư tỉnh Đắk Lắk cấp lần đầu ngày 15/03/2020</p>
    <p>© 2020 - Bản quyền thuộc về Công ty TNHH ABC Đắk Lắk</p>
</div>

    </div>

    <style>
      .footer {
        background-color: #f8f8f8;
        padding: 30px 15px;
        font-family: Arial, sans-serif;
        color: #000;
        border-top: 1px solid #ddd;
      }

      .footer-top {
        text-align: center;
        margin-bottom: 20px;
      }

      .footer-country {
        color: #555;
        font-size: 14px;
        margin: 5px 0;
      }

      .footer-links {
        font-size: 14px;
        margin: 10px 0;
      }

      .footer-links a {
        color: #333;
        text-decoration: none;
        margin: 0 8px;
        font-weight: 500;
      }

      .footer-links a:hover {
        color: #27ae60;
        text-decoration: underline;
      }

      .footer-logos {
        margin-top: 15px;
      }

      .footer-logos img {
        height: 40px;
        margin: 0 8px;
        vertical-align: middle;
        transition: transform 0.3s;
      }

      .footer-logos img:hover {
        transform: scale(1.1);
      }

      .footer-bottom {
        text-align: center;
        color: #666;
        font-size: 13px;
        line-height: 1.6;
        border-top: 1px solid #ddd;
        padding-top: 15px;
      }
    </style>



  </div>
</body>

</html>
<?php ob_end_flush(); // ✅ Thêm cuối để xả buffer 
?>