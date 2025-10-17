<?php
ob_start();
session_start();

// Kiểm tra quyền admin
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../index.php"); // Chuyển hướng nếu không phải admin
    exit();
}

// Lấy page admin từ URL
$page = $_GET['admin'] ?? 'trangchu';

// Mảng ánh xạ page => file quản lý
$adminPages = [
    'trangchu'        => 'trangchu.php',
    'xacnhandonhang'  => '../card/xacnhan_donhang.php',
    'hienthisp'       => './quanly_sp/hienthisp.php',
    'hienthidm'       => './quanly_loai/hienthidm.php',
    'hienthihd'       => './quanly_hd/hienthihd.php',
    'giohang'         => './quanly_giohang/giohang.php',
    'danhgia'         => './quanly_danhgia/danhgia.php',
    'nguoidung'       => './quanly_nd/nguoidung.php',
    'thuonghieu'      => './quanly_th/thuonghieu.php',
    'khuyenmai'       => './quanly_km/khuyenmai.php',
    'diem'            => './quanly_diem/diem.php',
    'lienhe'          => './quanly_lienhe/lienhe.php',
    'chitiet_taikhoan'=> 'chitiet_taikhoan.php',
    'chat_panel'=> 'chat_panel.php',
    'lichsugiaodich'=> './quanly_lichsugiaodich/lichsugiaodich.php',
];

// Menu admin
$menuItems = [
    'trangchu'        => 'Trang chủ',
    'xacnhandonhang'  => 'Xác nhận đơn hàng',
    'hienthisp'       => 'Quản lý sản phẩm',
    'hienthidm'       => 'Quản lý danh mục',
    'hienthihd'       => 'Quản lý hóa đơn',
    'giohang'         => 'Quản lý giỏ hàng',
     'lichsugiaodich'          => 'Quản lý lịch sử giao dịch',
    'danhgia'         => 'Quản lý đánh giá',
    'nguoidung'       => 'Quản lý người dùng',
    'thuonghieu'      => 'Quản lý thương hiệu',
    'khuyenmai'       => 'Quản lý khuyến mại',
    'diem'            => 'Quản lý điểm',
    'lienhe'          => 'Quản lý liên hệ',
    'chat_panel'          => 'Quản lý chat'
   

];

// Kết nối database
$conn = new mysqli("localhost", "root", "", "banhangonline");
$conn->set_charset("utf8");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

// Lấy thông tin người dùng đang đăng nhập
$user = $_SESSION['username'];
$taikhoan_id = 0;
$avatar = 'img/avatar.png'; // avatar mặc định

$stmt = $conn->prepare("SELECT id, avatar FROM taikhoan WHERE username=?");
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();

if($row = $result->fetch_assoc()){
    $taikhoan_id = $row['id'];
    // Nếu có avatar trong DB và file tồn tại trên server thì dùng
    if(!empty($row['avatar']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/banhangonline/' . $row['avatar'])){
        $avatar = '/banhangonline/' . ltrim($row['avatar'], '/');
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Thế Giới Nông Sản Việt Nam (Quản Trị)</title>
  <!-- Logo (favicon) -->
<link rel="icon" href="../img/logochinh.jpg" type="image/jpeg">
<link rel="stylesheet" type="text/css" href="index.css">
</head>
<body>
<div class="wrapper">

<!-- Header Banner Admin -->
<div class="header-slider">
  <div class="slides">
    <img src="../img/chinh.jpg" alt="Banner 1">
    <img src="../img/chinh2.jpg" alt="Banner 2">
    <img src="../img/chinh3.jpg" alt="Banner 3">
  </div>
</div>

<style>
.header-slider {
  width: 100%;
  max-height: 200px;
  overflow: hidden;
  border-radius: 8px;
  margin-bottom: 20px;
  position: relative;
}

.slides {
  display: flex;
  transition: transform 0.5s ease-in-out;
}

.slides img {
  width: 100%;
  flex-shrink: 0;
  object-fit: cover;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const slides = document.querySelector('.slides');
  const images = slides.children;
  const total = images.length;

  // Tự động set width cho slides
  slides.style.width = `${total * 100}%`;
  for (let img of images) {
    img.style.width = `${100 / total}%`;
  }

  let index = 0;
  setInterval(() => {
    index = (index + 1) % total; // quay vòng
    slides.style.transform = `translateX(-${index * (100 / total)}%)`;
  }, 4000); // 4 giây chuyển slide
});
</script>


  <!-- Top Bar -->
<div class="top-bar">
    <!-- Avatar -->
    <img src="<?php echo htmlspecialchars($avatar); ?>" 
         alt="User Avatar" class="topbar-avatar">

    <!-- Username với hiệu ứng 3D -->
    <div class="input__container topbar-username">
        <span class="shadow__input"></span>
        <a href="?admin=chitiet_taikhoan&id=<?php echo $taikhoan_id; ?>" 
           class="input__button__shadow">
            QT: <?php echo htmlspecialchars($user); ?> 👋
        </a>
    </div>

    <!-- Logout -->
    <a href="logout.php" class="topbar-logout">Thoát</a>
</div>
<style>/* Top Bar chung */
.top-bar {
  display: flex;
  flex-wrap: wrap; /* tự xuống dòng khi màn hình nhỏ */
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  background: #2c3e50;
  padding: 10px 15px;
  color: white; /* chữ mặc định trắng */
}

/* Avatar */
.topbar-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #fff;
}

/* Username container (áp dụng 3D) */
.topbar-username {
  max-width: 200px;
  position: relative;
}

/* Áp dụng CSS 3D từ input__container */
.input__container {
  background: #2c3e50; /* nền trùng Top Bar */
  padding: 10px 15px;
  display: flex;
  justify-content: center;
  align-items: center;
  border: 2px solid #fff;
  transition: all 400ms cubic-bezier(0.23, 1, 0.32, 1);
  transform-style: preserve-3d;
  transform: rotateX(10deg) rotateY(-10deg);
  perspective: 1000px;
  box-shadow: 2px 2px 0 #000;
}

/* Hover 3D */
.input__container:hover {
  transform: rotateX(5deg) rotateY(1deg) scale(1.05);
  box-shadow: 5px 5px 0 -1px #e9b50b, 5px 5px 0 0 #000;
}

/* Shadow dưới chữ */
.shadow__input {
  content: "";
  position: absolute;
  width: 100%;
  height: 100%;
  left: 0;
  bottom: 0;
  z-index: -1;
  transform: translateZ(-20px);
  background: linear-gradient(
    45deg,
    rgba(255, 255, 255, 0.2) 0%,
    rgba(255, 255, 255, 0.05) 100%
  );
  filter: blur(10px);
}

/* Hiệu ứng chữ nổi */
.input__button__shadow {
  cursor: pointer;
  border: 2px solid #fff;
  background: transparent; /* trong suốt để trùng màu Top Bar */
  padding: 5px 10px;
  transform: translateZ(10px);
  font-weight: bold;
  text-transform: uppercase;
  transition: all 400ms cubic-bezier(0.23, 1, 0.32, 1);
  text-decoration: none;
  color: #fff; /* chữ trắng */
  font-size: 14px;
}

.input__button__shadow:hover {
  transform: translateZ(5px) translateX(-3px) translateY(-3px);
  box-shadow: 3px 3px 0 0 #000;
}

/* Logout */
.topbar-logout {
  color: #fff; /* chữ trắng */
  text-decoration: none;
  font-weight: bold;
  margin-left: 15px;
  transition: color 0.3s;
}

.topbar-logout:hover {
  color: #e74c3c;
}

/* Responsive */
@media (max-width: 500px) {
  .top-bar {
    justify-content: center;
  }

  .topbar-username {
    max-width: 150px;
  }

  .input__button__shadow {
    font-size: 12px;
    padding: 4px 8px;
  }

  .topbar-avatar {
    width: 35px;
    height: 35px;
  }
}
</style>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Sidebar Menu -->
        <div class="sidebar-left">
            <h3>Quản lý</h3>
            <ul>
                <?php
                foreach ($menuItems as $key => $label) {
                    $active = ($page === $key) ? 'active' : '';
                    echo "<li><a href='?admin=$key' class='$active'>$label</a></li>";
                }
                ?>
            </ul>
        </div>

        <div class="danhsachall">
            <?php
            if (array_key_exists($page, $adminPages)) {
                $filePath = $adminPages[$page];
                if (file_exists($filePath)) {
                    include($filePath);
                } else {
                    echo "<p>File $filePath không tồn tại!</p>";
                }
            } else {
                include($adminPages['trangchu']);
            }
            ?>
        </div>

    </div>
    
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
      <img src="../img/amazon.png" alt="Amazon">
      <img src="../img/shopee.jpg" alt="Shopee">
      <img src="../img/lazada.jpg" alt="Lazada">
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
<?php ob_end_flush(); ?>