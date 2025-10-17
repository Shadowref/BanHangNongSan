<?php
// 1. Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "banhangonline";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// 2. Lấy maloai từ URL
$maloai = isset($_GET['maloai']) ? intval($_GET['maloai']) : 0;

// 3. Lấy danh sách sản phẩm theo maloai
$sql = "SELECT * FROM sanpham WHERE maloai = $maloai";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sản phẩm theo loại</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f8f8;
            padding: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .product-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .product-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0px 2px 6px rgba(0,0,0,0.1);
            text-align: center;
            padding: 15px;
            transition: 0.3s;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }
        .product-name {
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
            color: #333;
        }
        .product-price {
            color: red;
            font-size: 14px;
            margin: 8px 0;
        }
        .buy-btn {
            background: #27ae60;
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            display: inline-block;
        }
        .buy-btn:hover {
            background: #1e8449;
        }
    </style>
</head>
<body>

<h2>Danh sách sản phẩm</h2>
<div class="product-container">
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='product-card'>
                    <img src='images/" . htmlspecialchars($row['hinhanh']) . "' alt='" . htmlspecialchars($row['tensp']) . "'>
                    <div class='product-name'>" . htmlspecialchars($row['tensp']) . "</div>
                    <div class='product-price'>" . number_format($row['gia'], 0, ',', '.') . " VNĐ</div>
                    <a class='buy-btn' href='chitiet.php?id=" . $row['id'] . "'>Xem chi tiết</a>
                  </div>";
        }
    } else {
        echo "<p>Không có sản phẩm nào thuộc loại này.</p>";
    }
    ?>
</div>

</body>
</html>
<?php
$conn->close();
?>
