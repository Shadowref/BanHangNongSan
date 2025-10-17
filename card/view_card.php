<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) session_start();

// Kết nối CSDL
$conn = mysqli_connect("localhost", "root", "", "banhangonline");
mysqli_set_charset($conn, "utf8");
if (!$conn) die(json_encode(['success'=>false,'msg'=>'Kết nối thất bại']));
if (!isset($_SESSION['user_id'])) die(json_encode(['success'=>false,'msg'=>'Chưa đăng nhập']));

$taikhoan_id = (int)$_SESSION['user_id'];
$thongbao = "";

// Xử lý thanh toán
if (isset($_POST['thanhtoan'])) {
    $chonsp = $_POST['chonsp'] ?? [];
    if(empty($chonsp)) $thongbao = "⚠️ Vui lòng chọn sản phẩm để thanh toán!";
    else {
        $_SESSION['chonsp'] = array_map('intval', $chonsp);
        header("Location: card/checkout.php"); exit;
    }
}

// AJAX xóa sản phẩm
if(isset($_POST['ajax_remove'])){
    $sanpham_id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM giohang WHERE taikhoan_id=? AND sanpham_id=?");
    $stmt->bind_param("ii", $taikhoan_id, $sanpham_id);
    echo json_encode(['success'=>$stmt->execute()]);
    $stmt->close(); exit;
}

// AJAX cập nhật số lượng
if(isset($_POST['ajax_update'])){
    $id = intval($_POST['id']);
    $soluong = max(1,intval($_POST['soluong']));
    $stmt = $conn->prepare("UPDATE giohang SET soluong=? WHERE taikhoan_id=? AND sanpham_id=?");
    $stmt->bind_param("iii",$soluong,$taikhoan_id,$id);
    $stmt->execute(); $stmt->close();

    // Cập nhật tổng tiền (tính cả khuyến mãi nếu có)
    $today = date("Y-m-d");
    $res = mysqli_query($conn,"
        SELECT g.soluong, s.gia, km.giakhuyenmai, km.ngay_bat_dau, km.ngay_ket_thuc
        FROM giohang g 
        JOIN sanpham s ON g.sanpham_id=s.id
        LEFT JOIN khuyenmai km ON km.sanpham_id=s.id
        WHERE g.taikhoan_id=$taikhoan_id
    ");
    $tong = 0;
    while($r=mysqli_fetch_assoc($res)){
        $gia = $r['gia'];
        if($r['giakhuyenmai'] && $r['ngay_bat_dau'] <= $today && $r['ngay_ket_thuc'] >= $today){
            $gia = $r['giakhuyenmai'];
        }
        $tong += $gia*$r['soluong'];
    }
    echo json_encode(['success'=>true,'tong'=>$tong]);
    exit;
}

// Lấy giỏ hàng
$sql_cart = "
SELECT g.soluong, s.tensp, s.gia, s.hinhanh, s.id,
       km.giakhuyenmai, km.giamgia, km.ngay_bat_dau, km.ngay_ket_thuc
FROM giohang g
JOIN sanpham s ON g.sanpham_id = s.id
LEFT JOIN khuyenmai km ON km.sanpham_id = s.id
WHERE g.taikhoan_id = $taikhoan_id
";
$result_cart = mysqli_query($conn, $sql_cart);

$cart_items = []; $tongtien = 0; $today = date("Y-m-d");
while($row=mysqli_fetch_assoc($result_cart)){
    $gia_hientai = $row['gia'];
    if ($row['giakhuyenmai'] && $row['ngay_bat_dau'] <= $today && $row['ngay_ket_thuc'] >= $today) {
        $gia_hientai = $row['giakhuyenmai'];
    }
    $row['gia_hientai'] = $gia_hientai;
    $row['thanhtien'] = $gia_hientai * $row['soluong'];
    $tongtien += $row['thanhtien'];
    $cart_items[] = $row;
}

if(empty($cart_items)){
    echo "<h2 style='text-align:center;'>Giỏ hàng trống!</h2>
          <div style='text-align:center;'><a href='index.php'>⬅️ Quay lại mua hàng</a></div>";
    ob_end_flush(); exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Giỏ hàng</title>
<style>
body { 
    font-family: "Segoe UI", Tahoma, sans-serif; 
    background: #f4f7fb; 
    padding:20px; 
    color:#333;
}
h2 { 
    text-align:center; 
    color:#222; 
    margin-bottom:20px;
    font-size:26px;
}
table { 
    width:100%; 
    border-collapse: collapse; 
    background:#fff; 
    border-radius:12px; 
    overflow:hidden; 
    box-shadow:0 6px 18px rgba(0,0,0,0.08); 
}
th, td { 
    padding:14px; 
    text-align:center; 
    border-bottom:1px solid #eee; 
    vertical-align: middle;
}
th { 
    background: linear-gradient(135deg, #00cc1bff, #039900ff); 
    color:#fff; 
    font-size:15px; 
    letter-spacing:0.5px;
}
td img { 
    max-width:70px; 
    border-radius:8px; 
    box-shadow:0 2px 6px rgba(0,0,0,0.15); 
}
.checkout-btn { 
    padding:14px 30px; 
    border:none; 
    border-radius:8px; 
    background: linear-gradient(135deg, #28a745, #218838); 
    color:#fff; 
    cursor:pointer; 
    font-size:17px; 
    margin-top:20px; 
    font-weight:bold; 
    transition: all 0.3s ease;
}
.checkout-btn:hover { 
    transform: translateY(-2px); 
    box-shadow:0 4px 12px rgba(0,0,0,0.15); 
}
.total-box { 
    text-align:right; 
    font-weight:bold; 
    font-size:20px; 
    margin-top:20px; 
    color:#d62828; 
}
input[type=number]{ 
    width:65px; 
    text-align:center; 
    padding:6px; 
    border-radius:6px; 
    border:1px solid #ccc; 
    transition:0.2s;
}
input[type=number]:focus{
    border-color:#007bff; 
    outline:none; 
    box-shadow:0 0 4px rgba(0,123,255,0.4);
}
a.remove-btn { 
    color:#e63946; 
    cursor:pointer; 
    text-decoration:none; 
    font-weight:bold; 
    font-size:18px; 
    transition:0.2s;
}
a.remove-btn:hover { 
    color:#b71c1c; 
}
.thongbao { 
    text-align:center; 
    padding:12px; 
    background:#d4edda; 
    color:#155724; 
    border-radius:8px; 
    margin-bottom:20px; 
    font-size:15px; 
}
.price-old { 
    text-decoration: line-through; 
    color: #888; 
    margin-right:6px; 
    font-size:14px;
}
.price-new { 
    color: #e63946; 
    font-weight:bold; 
    font-size:16px; 
}
@media(max-width:600px){
    table, thead, tbody, th, td, tr { display:block; }
    tr { margin-bottom:15px; border:1px solid #ddd; padding:10px; border-radius:10px; background:#fff; }
    th { display:none; }
    td { text-align:left; border:none; padding:8px 12px; position:relative; }
    td::before { position:absolute; left:12px; font-weight:bold; color:#555; }
    td:nth-of-type(1)::before { content:"Chọn"; }
    td:nth-of-type(2)::before { content:"Sản phẩm"; }
    td:nth-of-type(3)::before { content:"Giá"; }
    td:nth-of-type(4)::before { content:"Số lượng"; }
    td:nth-of-type(5)::before { content:"Hành động"; }
}
.checkout-container {
    display:flex;
    justify-content:flex-end;
    align-items:center;
    gap:20px;
    margin-top:25px;
}

.total-box { 
    font-weight:bold; 
    font-size:20px; 
    color:#d62828; 
    background:#fff4f4; 
    padding:10px 20px; 
    border-radius:8px; 
    box-shadow:0 2px 6px rgba(0,0,0,0.1);
}

.checkout-btn { 
    padding:14px 28px; 
    border:none; 
    border-radius:8px; 
    background: linear-gradient(135deg, #28a745, #218838); 
    color:#fff; 
    cursor:pointer; 
    font-size:17px; 
    font-weight:bold; 
    transition: all 0.3s ease;
}

.checkout-btn:hover { 
    transform: translateY(-2px); 
    box-shadow:0 4px 12px rgba(0,0,0,0.15); 
}

</style>
</head>
<body>


<h2>🛒 Giỏ hàng của bạn</h2>

<?php if($thongbao): ?>
<div class="thongbao"><?= $thongbao ?></div>
<?php endif; ?>

<form id="cartForm" method="POST">
<table>
<tr>
<th><input type="checkbox" id="chonAll" checked></th>
<th>Sản phẩm</th>
<th>Giá</th>
<th>Số lượng</th>
<th>Hành động</th>
</tr>

<?php foreach($cart_items as $sp): ?>
<tr data-id="<?= $sp['id'] ?>">
<td><input type="checkbox" class="chonsp" name="chonsp[]" value="<?= $sp['id'] ?>" data-gia="<?= $sp['gia_hientai'] ?>" checked></td>
<td style="display:flex; align-items:center; gap:10px;">
    <img src="<?= $sp['hinhanh'] && file_exists('img/'.$sp['hinhanh']) ? 'img/'.htmlspecialchars($sp['hinhanh']) : 'img/no-image.png' ?>" 
     alt="<?= htmlspecialchars($sp['tensp']) ?>" 
     style="width:80px;height:80px;object-fit:cover;margin-right:15px;">
    <span><?= htmlspecialchars($sp['tensp']) ?></span>
</td>
<td>
    <?php if($sp['gia_hientai'] < $sp['gia']): ?>
        <span class="price-old"><?= number_format($sp['gia'],0,',','.') ?>đ</span>
        <span class="price-new"><?= number_format($sp['gia_hientai'],0,',','.') ?>đ</span>
    <?php else: ?>
        <?= number_format($sp['gia'],0,',','.') ?>đ
    <?php endif; ?>
</td>
<td><input type="number" class="soluong" value="<?= $sp['soluong'] ?>" min="1" data-gia="<?= $sp['gia_hientai'] ?>"></td>
<td><a class="remove-btn" data-id="<?= $sp['id'] ?>">❌</a></td>
</tr>
<?php endforeach; ?>
</table>

<div class="checkout-container">
    <div class="total-box" id="tongcong">
        Tổng cộng: <?= number_format($tongtien,0,',','.') ?>đ
    </div>
    <button type="submit" name="thanhtoan" class="checkout-btn">✅ Thanh toán</button>
</div>

</form>

<script>
function capNhatTong(){
    let tong = 0;
    document.querySelectorAll(".chonsp:checked").forEach(cb=>{
        let row = cb.closest('tr');
        let gia = parseInt(row.querySelector('.soluong').dataset.gia);
        let soluong = parseInt(row.querySelector('.soluong').value);
        tong += gia*soluong;
    });
    document.getElementById('tongcong').innerText = "Tổng cộng: "+tong.toLocaleString()+"đ";
}

document.getElementById("chonAll").addEventListener("change", function(){
    document.querySelectorAll(".chonsp").forEach(cb => cb.checked = this.checked);
    capNhatTong();
});

document.querySelectorAll(".soluong").forEach(input=>{
    input.addEventListener("change", function(){
        let row = this.closest('tr');
        let id = row.dataset.id;
        let soluong = this.value;
        capNhatTong();
        fetch("",{
            method:"POST",
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:"ajax_update=1&id="+id+"&soluong="+soluong
        }).then(r=>r.json()).then(d=>{
            if(d.success) capNhatTong();
        });
    });
});

document.querySelectorAll(".remove-btn").forEach(btn=>{
    btn.addEventListener("click", function(){
        let id = this.dataset.id;
        if(!confirm("Xóa sản phẩm này?")) return;
        fetch("",{
            method:"POST",
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:"ajax_remove=1&id="+id
        }).then(r=>r.json()).then(d=>{
            if(d.success) location.reload();
        });
    });
});

document.querySelectorAll(".chonsp").forEach(cb=>cb.addEventListener("change", capNhatTong));
</script>

</body>
</html>
<?php ob_end_flush(); ?>
