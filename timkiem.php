<?php
// Kết nối CSDL
$conn = mysqli_connect("localhost", "root", "", "banhangonline");
mysqli_set_charset($conn, "utf8");

$search_results = [];
if (isset($_GET['search']) && $_GET['search'] != "") {
    $keyword = mysqli_real_escape_string($conn, $_GET['search']);
    
    // Lấy dữ liệu sản phẩm cùng khuyến mại, đánh giá, số lượng bán + trạng thái
    $sql = "
    SELECT sp.*, km.giakhuyenmai, km.giamgia,
           IFNULL(dg.avg_rating,0) AS avg_rating,
           IFNULL(dg.num_reviews,0) AS num_reviews,
           IFNULL(dh.daban,0) AS soluong_ban
    FROM sanpham sp
    LEFT JOIN khuyenmai km ON sp.id = km.sanpham_id AND NOW() BETWEEN km.ngay_bat_dau AND km.ngay_ket_thuc
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
    WHERE sp.tensp LIKE '%$keyword%'
    ";
    
    $query = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
        $search_results[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kết quả tìm kiếm</title>
    <style>
        body { font-family: Arial; margin: 0; padding: 0; background:#f5f5f5; }
        .container { width: 90%; margin: auto; padding: 20px; }
        h3 { text-align: center; }
        .grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(250px,1fr)); gap:20px; justify-items:center; }
        .product { width: 250px; border:1px solid #ccc; border-radius:5px; background:white; padding:10px; text-align:center; position:relative; }
        .product img { width:180px; height:180px; object-fit:contain; }
        .price { color:red; font-weight:bold; margin:5px 0; }
        .price del { color:#555; margin-left:5px; font-weight:normal; }
        .percent { color:#e74c3c; margin-left:5px; font-weight:bold; }
        .rating { color:#f39c12; font-size:14px; margin:5px 0; }
        .info { font-size:12px; color:#555; margin:5px 0; }
        .badge { position:absolute; top:10px; left:10px; background:#e74c3c; color:white; padding:3px 5px; font-size:12px; border-radius:3px; font-weight:bold; }
        .disabled { color:#aaa; font-style:italic; margin-top:10px; }
    </style>
</head>
<body>
<div class="container">
    <?php if (isset($_GET['search']) && $_GET['search'] != ""): ?>
        <h3>Kết quả tìm kiếm cho: <span style="color:red"><?= htmlspecialchars($_GET['search']) ?></span></h3>
        <?php if (count($search_results) > 0): ?>
            <div class="grid">
                <?php foreach ($search_results as $sp): ?>
                    <?php
                        $gia_goc = (float)$sp['gia'];
                        $gia_km = !empty($sp['giakhuyenmai']) ? (float)$sp['giakhuyenmai'] : 0;
                        $giamgia = !empty($sp['giamgia']) ? (float)$sp['giamgia'] : 0;
                        $gia_cuoi = $gia_goc;
                        $percent = 0;

                        if ($gia_km > 0 && $gia_km < $gia_goc) {
                            $gia_cuoi = $gia_km;
                            $percent = round(100 - ($gia_cuoi / $gia_goc * 100));
                        } elseif ($giamgia > 0 && $giamgia < 100) {
                            $gia_cuoi = $gia_goc * (100 - $giamgia) / 100;
                            $percent = $giamgia;
                        }

                        $soluong_ban = intval($sp['soluong_ban']);
                        $soluong_con = intval($sp['soluong']) - $soluong_ban;
                        $num_reviews = intval($sp['num_reviews']);
                        $avg_rating = round($sp['avg_rating'],1);
                    ?>
                    <div class="product">
    <?php if($gia_cuoi < $gia_goc): ?>
        <div class="badge">Khuyến mại</div>
    <?php endif; ?>

    <?php if ($sp['trangthai'] == 'hien'): ?>
        <!-- Nếu còn kinh doanh thì cho click -->
        <a href="index.php?content=chitiet&id=<?= $sp['id'] ?>">
            <img src="img/<?= $sp['hinhanh'] ?>" alt="<?= $sp['tensp'] ?>">
            <p><strong><?= $sp['tensp'] ?></strong></p>
        </a>
    <?php else: ?>
        <!-- Nếu đã ẩn thì không cho click -->
        <img src="img/<?= $sp['hinhanh'] ?>" alt="<?= $sp['tensp'] ?>">
        <p><strong><?= $sp['tensp'] ?></strong></p>
        <p style="color:red; font-weight:bold;">Sản phẩm đã ngừng kinh doanh</p>
    <?php endif; ?>

    <div class="price">
        <?= number_format($gia_cuoi,0,',','.') ?> VNĐ
        <?php if($gia_cuoi < $gia_goc): ?>
            <del><?= number_format($gia_goc,0,',','.') ?> VNĐ</del>
            <span class="percent">-<?= $percent ?>%</span>
        <?php endif; ?>
    </div>
    <div class="rating">
        <?php for($i=1;$i<=5;$i++): ?>
            <?= $i <= floor($avg_rating) ? '⭐' : '☆' ?>
        <?php endfor; ?>
        <span style="color:#555;">(<?= $num_reviews ?>)</span>
    </div>
    <div class="info">
        Đã bán: <?= $soluong_ban ?> | Còn: <?= $soluong_con ?>
    </div>
</div>

                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align:center;">Không tìm thấy sản phẩm.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
