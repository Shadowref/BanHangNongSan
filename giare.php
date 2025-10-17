<?php
$conn = mysqli_connect("localhost", "root", "", "banhangonline");
mysqli_set_charset($conn, "utf8");

if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// ======== PHÂN TRANG CHỈ FILE GIÁ RẺ ========
$limit = 6;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// ====== Tính tổng sản phẩm (chỉ lấy sp hiện) ======
$total_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM sanpham sp WHERE sp.trangthai = 'hien'");
$total_row = mysqli_fetch_assoc($total_result);
$total_items = $total_row['total'];
$total_pages = ceil($total_items / $limit);

// ====== Lấy dữ liệu sản phẩm giá rẻ (chỉ lấy sp hiện) ======
$sql = "
SELECT sp.*, l.tenloai, 
       km.giakhuyenmai, km.giamgia
FROM sanpham sp
LEFT JOIN loaisanpham l ON sp.id_loai = l.id
LEFT JOIN khuyenmai km 
       ON sp.id = km.sanpham_id 
      AND NOW() BETWEEN km.ngay_bat_dau AND km.ngay_ket_thuc
WHERE sp.trangthai = 'hien'
ORDER BY 
  CASE 
    WHEN km.giakhuyenmai IS NOT NULL AND km.giakhuyenmai < sp.gia THEN km.giakhuyenmai
    WHEN sp.giamgia IS NOT NULL AND sp.giamgia < 100 THEN sp.gia*(100-sp.giamgia)/100
    ELSE sp.gia
  END ASC
LIMIT $offset, $limit
";

$result = mysqli_query($conn, $sql);
?>

<style>
.product-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    justify-items: center;
}
.product-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 10px;
    text-align: center;
    background: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    width: 100%;
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
.product-card img { width: 100%; height: 180px; object-fit: contain; margin-bottom: 8px; }
.product-card .name { display: block; margin: 8px 0; color: #333; font-weight: bold; }
.product-card .price { color: red; font-size: 16px; margin-bottom: 5px; }
.product-card .old-price { text-decoration: line-through; color: #888; font-size: 14px; margin-left: 5px; }
.product-card .discount { color: #e74c3c; font-size: 12px; margin-left: 5px; }
.product-card .sold { font-size: 12px; color: #555; margin-bottom: 8px; }
.product-card form input[type="number"] { width: 50px; margin-bottom: 5px; }
.product-card form button {
    background: #27ae60;
    color: white;
    padding: 5px 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: 0.3s;
}
.product-card form button:hover { background: #219150; }
</style>

<div class="product-grid">
<?php
while ($row = mysqli_fetch_assoc($result)) {
    $gia_goc = (float)$row['gia'];
    $gia_km = !empty($row['giakhuyenmai']) ? (float)$row['giakhuyenmai'] : 0;
    $giamgia = !empty($row['giamgia']) ? (float)$row['giamgia'] : 0;
    $gia_cuoi = $gia_goc;
    $percent = 0;

    if ($gia_km > 0 && $gia_km < $gia_goc) {
        $gia_cuoi = $gia_km;
        $percent = round(100 - ($gia_cuoi / $gia_goc * 100));
    } elseif ($giamgia > 0 && $giamgia < 100) {
        $gia_cuoi = $gia_goc * (100 - $giamgia) / 100;
        $percent = $giamgia;
    }

    $soluong_con = intval($row['soluong']);

    echo '<div class="product-card">';
    echo '<a href="index.php?content=chitiet&id=' . $row['id'] . '">';
    $img_sp = !empty($row['hinhanh']) ? $row['hinhanh'] : 'default.png';
    echo '<img src="img/' . htmlspecialchars($img_sp) . '">';
    echo '<span class="name">' . htmlspecialchars($row['tensp']) . '</span>';
    echo '</a>';

    echo '<div class="price">';
    echo number_format($gia_cuoi,0,',','.') . ' đ';
    if($gia_cuoi < $gia_goc){
        echo '<span class="old-price">' . number_format($gia_goc,0,',','.') . ' đ</span>';
        echo '<span class="discount">-' . $percent . '%</span>';
    }
    echo '</div>';

    echo '<div class="sold">';
    echo 'Còn: '.$soluong_con;
    echo '</div>';

    echo '<form action="./card/cart.php" method="POST">';
    echo '<input type="hidden" name="id" value="'.$row['id'].'">';
    echo '<input type="hidden" name="tensp" value="'.htmlspecialchars($row['tensp']).'">';
    echo '<input type="hidden" name="gia" value="'.$gia_cuoi.'">';
    echo '<input type="number" name="soluong" value="1" min="1" max="'.$soluong_con.'" 
           oninput="if(this.value>this.max)this.value=this.max;"> <br>';
    echo '<button type="submit" name="action" value="add">🛒 Thêm giỏ hàng</button>';
    echo '</form>';

    echo '</div>';
}
?>
</div>

<?php
// ===== PHÂN TRANG RIÊNG CHO FILE GIÁ RẺ =====
echo '<div style="text-align:center; margin-top:20px; font-family:Arial;">';
if($total_pages > 1){
    for($i=1;$i<=$total_pages;$i++){
        if($i == $page){
            echo "<span style='margin:0 5px; padding:5px 10px; background:#27ae60; color:#fff; border-radius:5px; font-weight:bold;'>$i</span>";
        } else {
            echo "<a href='?content=giare&page=$i' style='margin:0 5px; padding:5px 10px; background:#f1f1f1; color:#333; border-radius:5px; text-decoration:none; transition:0.3s;'>$i</a>";
        }
    }
}
echo '</div>';

mysqli_close($conn);
?>
