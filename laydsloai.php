<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "banhangonline";

// Kết nối database
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

$id_loai = isset($_GET['id']) ? intval($_GET['id']) : 0;
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$limit = 6; // số sản phẩm/trang
$start = ($page - 1) * $limit;

// Lấy tổng số sản phẩm để tính phân trang (chỉ lấy sản phẩm hiện)
$total_sql = "SELECT COUNT(*) AS total FROM sanpham WHERE id_loai=$id_loai AND trangthai='hien'";
$total_result = $conn->query($total_sql);
$total_rows = ($total_result) ? intval($total_result->fetch_assoc()['total']) : 0;
$total_pages = ceil($total_rows / $limit);

// Lấy sản phẩm theo loại + khuyến mãi + đánh giá + đã bán (chỉ sản phẩm hiện)
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
WHERE sp.id_loai = $id_loai AND sp.trangthai='hien'
ORDER BY sp.id DESC
LIMIT $start, $limit
";

$result = $conn->query($sql);

// ==== CSS nhanh trong file (có thể chuyển ra file riêng) ====
echo '<style>
.grid-products {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* 3 sản phẩm/1 hàng trên desktop */
    gap:20px;
    padding:10px;
}

.card {
    border:1px solid #ddd;
    border-radius:8px;
    padding:10px;
    background:#fff;
    text-align:center;
    box-shadow:0 2px 6px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    width:100%;
}

.card:hover { 
    transform: translateY(-5px);
    box-shadow:0 6px 15px rgba(0,0,0,0.2);
}

.card img { 
    width:100%; 
    height:180px; 
    object-fit:contain; 
    border-radius:6px; 
}

.card b { 
    display:block; 
    margin:8px 0; 
    color:#333; 
    font-size:16px; 
}

.price { color:red; font-size:16px; margin-bottom:5px; }
.price del { color:#888; font-size:14px; margin-left:5px; }
.discount { color:#e74c3c; font-size:12px; margin-left:5px; }
.rating { font-size:14px; color:#f39c12; margin-bottom:5px; }
.stock { font-size:12px; color:#555; margin-bottom:8px; }

.pagination { text-align:center; margin:20px 0; }
.pagination a { margin:0 5px; padding:5px 10px; text-decoration:none; color:#27ae60; border:1px solid #27ae60; border-radius:5px; transition:0.3s; }
.pagination a:hover { background:#27ae60; color:#fff; }
.pagination .active { background:#27ae60; color:#fff; border:none; }

/* Responsive cho mobile */
@media (max-width: 768px) {
    .grid-products { grid-template-columns: 1fr; }
}
</style>';

// ==== Hiển thị sản phẩm ====
if ($result && $result->num_rows > 0) {
    echo '<div class="grid-products">';
    while ($row = $result->fetch_assoc()) {
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

        $sao = round($row['avg_rating']);
        $num_review = intval($row['num_reviews']);
        $da_ban = intval($row['soluong_ban']);
        $con_lai = intval($row['soluong']);
        $img_sp = !empty($row['hinhanh']) ? $row['hinhanh'] : 'default.png';

        echo '<div class="card">';
        echo '<a href="index.php?content=chitiet&id='.$row['id'].'" style="text-decoration:none; color:inherit;">';
        echo '<img src="img/'.htmlspecialchars($img_sp).'">';
        echo '<b>'.htmlspecialchars($row['tensp']).'</b></a>';

        echo '<div class="price">'.number_format($gia_cuoi,0,',','.').' đ';
        if($gia_cuoi < $gia_goc){
            echo '<del>'.number_format($gia_goc,0,',','.').' đ</del>';
            echo '<span class="discount">-'.$percent.'%</span>';
        }
        echo '</div>';

        echo '<div class="rating">';
        for($i=1;$i<=5;$i++) echo ($i <= $sao) ? '★' : '☆';
        echo " <span style='color:#555; font-size:12px;'>($num_review)</span>";
        echo '</div>';

        echo '<div class="stock">Đã bán: '.$da_ban.' | Còn: '.$con_lai.'</div>';

        echo '<form action="./card/cart.php" method="POST">';
        echo '<input type="hidden" name="id" value="'.$row['id'].'">';
        echo '<input type="hidden" name="tensp" value="'.htmlspecialchars($row['tensp']).'">';
        echo '<input type="hidden" name="gia" value="'.$gia_cuoi.'">';
        echo '<input type="number" name="soluong" value="1" min="1" max="'.$con_lai.'" 
              oninput="if(this.value>this.max)this.value=this.max;" 
              style="width:50px; padding:3px; border-radius:4px; border:1px solid #ccc; margin-bottom:5px;">';
        echo '<br><button type="submit" name="action" value="add" style="background:#27ae60;color:white;border:none;padding:5px 10px;border-radius:4px; cursor:pointer;">🛒 Thêm giỏ hàng</button>';
        echo '</form>';

        echo '</div>'; // end card
    }
    echo '</div>'; // end grid

    // ==== PHÂN TRANG ====
    if($total_pages > 1){
        echo '<div class="pagination">';
        for($p=1;$p<=$total_pages;$p++){
            if($p == $page){
                echo '<span class="active">'.$p.'</span>';
            } else {
                echo '<a href="?content=loai&id='.$id_loai.'&page='.$p.'">'.$p.'</a>';
            }
        }
        echo '</div>';
    }
} else {
    echo "<p style='text-align:center; color:#555;'>Không có sản phẩm nào thuộc loại này.</p>";
}

$conn->close();
?>
