<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Xóa toàn bộ session
$_SESSION = [];
session_destroy();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đang đăng xuất...</title>
  <style>
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      background: #222;
      color: white;
      font-family: Arial, sans-serif;
      flex-direction: column;
    }
    .loader {
      position: relative;
      width: 120px;
      height: 90px;
      margin: 20px auto;
    }

    .loader:before {
      content: "";
      position: absolute;
      bottom: 30px;
      left: 50px;
      height: 30px;
      width: 30px;
      border-radius: 50%;
      background: #2a9d8f;
      animation: loading-bounce 0.5s ease-in-out infinite alternate;
    }

    .loader:after {
      content: "";
      position: absolute;
      right: 0;
      top: 0;
      height: 7px;
      width: 45px;
      border-radius: 4px;
      box-shadow: 0 5px 0 #f2f2f2, -35px 50px 0 #f2f2f2, -70px 95px 0 #f2f2f2;
      animation: loading-step 1s ease-in-out infinite;
    }

    @keyframes loading-bounce {
      0% {
        transform: scale(1, 0.7);
      }
      40% {
        transform: scale(0.8, 1.2);
      }
      60% {
        transform: scale(1, 1);
      }
      100% {
        bottom: 140px;
      }
    }

    @keyframes loading-step {
      0% {
        box-shadow: 0 10px 0 rgba(0, 0, 0, 0),
                0 10px 0 #f2f2f2,
                -35px 50px 0 #f2f2f2,
                -70px 90px 0 #f2f2f2;
      }
      100% {
        box-shadow: 0 10px 0 #f2f2f2,
                -35px 50px 0 #f2f2f2,
                -70px 90px 0 #f2f2f2,
                -70px 90px 0 rgba(0, 0, 0, 0);
      }
    }
  </style>
  <script>
    // Tự động chuyển về index.php sau 2 giây
    setTimeout(() => {
      window.location.href = "../index.php"; // chỉnh đúng đường dẫn
    }, 2000);
  </script>
</head>
<body>
  <h2>Đang đăng xuất...</h2>
  <div class="loader"></div>
  <p>Bạn sẽ được chuyển về trang chủ trong giây lát.</p>
</body>
</html>
