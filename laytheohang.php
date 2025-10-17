<?php 
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "banhangonline";

// Kết nối database
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

if (isset($_GET['id'])) {
    $id_thuonghieu = intval($_GET['id']);

    // Lấy sản phẩm theo thương hiệu kèm khuyến mãi, đánh giá, số lượng đã bán
    // Lấy sản phẩm theo thương hiệu kèm khuyến mãi, đánh giá, số lượng đã bán (chỉ hiển thị sản phẩm hiện)
$sql = "
SELECT sp.*, km.giakhuyenmai, km.giamgia,
       IFNULL(dg.avg_rating,0) AS avg_rating,
       IFNULL(dg.num_reviews,0) AS num_reviews,
       IFNULL(dh.daban,0) AS soluong_ban
FROM sanpham sp
LEFT JOIN khuyenmai km 
       ON sp.id = km.sanpham_id 
      AND NOW() BETWEEN km.ngay_bat_dau AND km.ngay_ket_thuc
LEFT JOIN (
    SELECT id_sanpham, AVG(rating) AS avg_rating, COUNT(*) AS num_reviews
    FROM danhgia
    GROUP BY id_sanpham
) dg ON sp.id = dg.id_sanpham
LEFT JOIN (
    SELECT id_sanpham, SUM(soluong) AS daban
    FROM donhang
    GROUP BY id_sanpham
) dh ON sp.id = dh.id_sanpham
WHERE sp.id_thuonghieu = $id_thuonghieu 
  AND sp.trangthai = 'hien'
";

    
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {

        // ==== CSS cho layout responsive & product-card ====
     echo '<style>
.grid-products {
    display: grid;
    grid-template-columns: repeat(4, 1fr); /* 3 sản phẩm/1 hàng */
    gap: 20px;
    padding: 10px;
}
.product-card {
    border:1px solid #ddd;
    border-radius:8px;
    padding:10px;
    background:#fff;
    text-align:center;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    width:100%;
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow:0 6px 15px rgba(0,0,0,0.2);
}
.product-card img {
    width:100%;
    height:150px;
    object-fit:cover;
    border-radius:6px;
}
.product-card b {
    display:block;
    margin:8px 0;
    font-size:16px;
    color:#333;
}
.price { color:red; margin:5px 0; font-size:16px; }
.price del { color:#888; font-size:12px; margin-left:5px; }
.discount { color:#e74c3c; font-size:12px; margin-left:5px; }
.rating { color:#f39c12; margin:5px 0; font-size:14px; }
.stock { font-size:12px; color:#555; margin-bottom:8px; }

/* Nếu muốn xuống 1 cột trên mobile */
@media (max-width: 480px) {
    .grid-products {
        grid-template-columns: 1fr;
    }
}
</style>';

        echo '<div class="grid-products">';

        while ($row = $result->fetch_assoc()) {
            // Tính giá cuối
            $gia_goc = (float)$row['gia'];
            $gia_km = !empty($row['giakhuyenmai']) ? (float)$row['giakhuyenmai'] : 0;
            $giamgia = !empty($row['giamgia']) ? (float)$row['giamgia'] : 0;
            $gia_cuoi = $gia_goc;
            $percent = 0;
            if ($gia_km > 0 && $gia_km < $gia_goc) {
                $gia_cuoi = $gia_km;
                $percent = round(100 - ($gia_cuoi / $gia_goc * 100));
            } elseif ($giamgia > 0 && $giamgia < 100) {
                $gia_cuoi = $gia_goc * (100 - $giamgia)/100;
                $percent = $giamgia;
            }

            // Đánh giá sao & số review
            $sao = round($row['avg_rating']);
            $num_review = $row['num_reviews'];

            // Số lượng bán / còn
            $da_ban = intval($row['soluong_ban']);
            $con_lai = intval($row['soluong']);

            echo '<div class="product-card">';

            // Link chi tiết + ảnh
            echo '<a href="index.php?content=chitiet&id=' . $row['id'] . '" style="text-decoration:none; color:inherit;">';
            $img_sp = !empty($row['hinhanh']) ? $row['hinhanh'] : 'default.png';
            echo '<img src="img/' . htmlspecialchars($img_sp) . '" alt="'.htmlspecialchars($row['tensp']).'">';
            echo '<b>' . htmlspecialchars($row['tensp']) . '</b>';
            echo '</a>';

            // Giá
            echo '<div class="price">'.number_format($gia_cuoi,0,',','.').' đ';
            if($gia_cuoi < $gia_goc){
                echo '<del>'.number_format($gia_goc,0,',','.').' đ</del>';
                echo '<span class="discount">-'.$percent.'%</span>';
            }
            echo '</div>';

            // Đánh giá sao
            echo '<div class="rating">';
            for ($i=1;$i<=5;$i++) echo ($i <= $sao) ? '★' : '☆';
            echo " <span style='color:#555; font-size:12px;'>($num_review đánh giá)</span>";
            echo '</div>';

            // Số lượng bán / còn
            echo '<div class="stock">Đã bán: '.$da_ban.' | Còn: '.$con_lai.'</div>';

            // Form mua hàng
            echo '<form action="./card/cart.php" method="POST">';
            echo '<input type="hidden" name="id" value="'.$row['id'].'">';
            echo '<input type="hidden" name="tensp" value="'.htmlspecialchars($row['tensp']).'">';
            echo '<input type="hidden" name="gia" value="'.$gia_cuoi.'">';
            echo '<input type="number" name="soluong" value="1" min="1" max="'.$con_lai.'" 
                   oninput="if(this.value>this.max)this.value=this.max;" 
                   style="width:50px; padding:3px; border-radius:4px; border:1px solid #ccc;">';
            echo '<br><button type="submit" name="action" value="add" style="margin-top:5px; background:#27ae60;color:white;border:none;padding:5px 10px;border-radius:4px; cursor:pointer;">🛒 Thêm giỏ hàng</button>';
            echo '</form>';

            echo '</div>'; // đóng product-card
        }

        echo '</div>'; // đóng grid-products
    } else {
        echo "<p style='text-align:center; color:#555;'>Không có sản phẩm thuộc thương hiệu này.</p>";
    }
}

$conn->close();
?>
