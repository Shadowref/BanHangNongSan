<?php
// khuyenmai.php
$conn = new mysqli("localhost", "root", "", "banhangonline");
$conn->set_charset("utf8");
if ($conn->connect_error) { die("Kết nối thất bại: " . $conn->connect_error); }

// --- Ghi thông báo các khuyến mại đã hết hạn ---
$expired = $conn->query("
    SELECT sp.tensp, km.ngay_ket_thuc 
    FROM khuyenmai km
    JOIN sanpham sp ON km.sanpham_id = sp.id
    WHERE km.ngay_ket_thuc < NOW()
");
while($row = $expired->fetch_assoc()) {
    $msg = "Khuyến mại sản phẩm '".htmlspecialchars($row['tensp'])."' đã kết thúc vào ".$row['ngay_ket_thuc'];
    $conn->query("INSERT INTO thongbao (user_id, message) VALUES (1, '".$conn->real_escape_string($msg)."')");
}

// --- Phân trang ---
$limit = 12; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

// --- Đếm tổng sản phẩm có khuyến mại còn hiệu lực ---
$countRes = $conn->query("
    SELECT COUNT(*) as total
    FROM sanpham sp
    INNER JOIN khuyenmai km ON sp.id = km.sanpham_id
    WHERE sp.trangthai = 'hien'
      AND NOW() BETWEEN km.ngay_bat_dau AND km.ngay_ket_thuc
");
$totalRow = $countRes->fetch_assoc()['total'];
$totalPages = ceil($totalRow / $limit);

// --- Lấy sản phẩm có khuyến mãi còn hiệu lực ---
$sql = "
SELECT sp.*, km.giakhuyenmai, km.giamgia, km.ngay_bat_dau, km.ngay_ket_thuc
FROM sanpham sp
INNER JOIN khuyenmai km 
        ON sp.id = km.sanpham_id
WHERE sp.trangthai = 'hien'
  AND NOW() BETWEEN km.ngay_bat_dau AND km.ngay_ket_thuc
ORDER BY sp.id DESC
LIMIT $start, $limit
";
$result = $conn->query($sql);
?>
<li style="list-style:none; width:100%;">
  <div style="padding:20px; background:#f8f9fa; border-radius:12px; width:100%; box-sizing:border-box;">
    
    <!-- Banner -->
    <div style="background:linear-gradient(135deg,#ff7675,#e17055); 
                color:#fff; padding:18px; border-radius:12px; 
                text-align:center; margin-bottom:18px;
                box-shadow:0 4px 10px rgba(0,0,0,0.15);">
      <div style="font-size:24px; font-weight:700; letter-spacing:1px;">🔥 Siêu khuyến mãi hôm nay 🔥</div>
      <div style="opacity:0.95; margin-top:4px;">Săn deal sốc – Nhanh tay kẻo hết!</div>
    </div>

    <!-- Danh sách sản phẩm -->
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:20px;">
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()):
          $gia = (float)$row['gia'];
          $gia_km = !empty($row['giakhuyenmai']) ? (float)$row['giakhuyenmai'] : null;
          $giamgia = !empty($row['giamgia']) ? (float)$row['giamgia'] : null;

          $gia_cuoi = $gia;
          $percent = 0;
          if ($gia_km && $gia_km > 0 && $gia_km < $gia) {
              $gia_cuoi = $gia_km;
              $percent = round(100 - ($gia_cuoi / $gia * 100));
          } elseif ($giamgia && $giamgia > 0 && $giamgia < 100) {
              $gia_cuoi = $gia * (100 - $giamgia) / 100.0;
              $percent = $giamgia;
          }
        ?>
          <!-- Một sản phẩm -->
          <div style="background:#fff; border-radius:12px; padding:14px; 
                      box-shadow:0 3px 8px rgba(0,0,0,0.08); 
                      position:relative; transition:all 0.3s ease;
                      border:1px solid #f0f0f0;"
               onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.15)';"
               onmouseout="this.style.transform=''; this.style.boxShadow='0 3px 8px rgba(0,0,0,0.08)';">
            
            <!-- Nhãn khuyến mãi -->
            <?php if ($percent > 0): ?>
              <span style="position:absolute; top:10px; left:10px; 
                           background:#e74c3c; color:#fff; font-size:12px; 
                           padding:5px 10px; border-radius:20px; font-weight:600;">
                -<?php echo $percent; ?>%
              </span>
            <?php endif; ?>

            <!-- Hình ảnh + Tên -->
            <a href="index.php?content=chitiet&id=<?php echo (int)$row['id']; ?>"
               style="text-decoration:none; color:inherit; display:block; text-align:center;">
              <img src="img/<?php echo htmlspecialchars($row['hinhanh']); ?>" alt=""
                   style="width:100%; height:200px; object-fit:cover; 
                          border-radius:10px; margin-bottom:10px;">
              <div style="font-weight:600; min-height:40px; font-size:15px; color:#333;">
                <?php echo htmlspecialchars($row['tensp']); ?>
              </div>
            </a>

            <!-- Giá -->
            <div style="margin-top:8px;">
              <?php if ($gia_cuoi < $gia): ?>
                <div style="display:flex; gap:10px; align-items:baseline;">
                  <span style="color:#e74c3c; font-weight:700; font-size:18px;">
                    <?php echo number_format($gia_cuoi, 0, ',', '.'); ?> đ
                  </span>
                  <span style="text-decoration:line-through; color:#888; font-size:13px;">
                    <?php echo number_format($gia, 0, ',', '.'); ?> đ
                  </span>
                </div>
              <?php else: ?>
                <div style="color:#e74c3c; font-weight:700; font-size:18px;">
                  <?php echo number_format($gia, 0, ',', '.'); ?> đ
                </div>
              <?php endif; ?>
            </div>

            <!-- Countdown khuyến mại -->
            <?php if ($percent > 0): ?>
              <div style="margin-top:8px; font-size:13px; color:#e74c3c; font-weight:600; text-align:center;">
                ⏱ Khuyến mại còn: 
                <span class="countdown" data-endtime="<?php echo $row['ngay_ket_thuc']; ?>"></span>
              </div>
            <?php endif; ?>

            <!-- Nút mua -->
            <form action="./card/cart.php" method="POST" style="margin-top:12px; display:flex; gap:8px; align-items:center;">
              <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
              <input type="hidden" name="tensp" value="<?php echo htmlspecialchars($row['tensp']); ?>">
              <input type="hidden" name="gia" value="<?php echo (float)$gia_cuoi; ?>">
              <input type="number" name="soluong" value="1" min="1"
                     max="<?php echo (int)$row['soluong']; ?>"
                     style="width:64px; padding:6px; border:1px solid #ddd; border-radius:6px; text-align:center;">
              <button type="submit" name="action" value="buy_now"
                      style="flex:1; background:#e74c3c; color:#fff; padding:8px; 
                             border:none; border-radius:6px; cursor:pointer; font-weight:600;
                             transition:0.3s;">
                ⚡ Mua ngay
              </button>
              <button type="submit" name="action" value="add"
                      style="flex:1; background:#27ae60; color:#fff; padding:8px; 
                             border:none; border-radius:6px; cursor:pointer; font-weight:600;
                             transition:0.3s;">
                🛒 Thêm giỏ
              </button>
            </form>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>Chưa có sản phẩm khuyến mãi.</p>
      <?php endif; ?>
    </div>

    <!-- Phân trang -->
    <?php if ($totalPages > 1): ?>
      <div style="text-align:center; margin-top:20px;">
        <?php for ($i=1; $i <= $totalPages; $i++): ?>
          <a href="index.php?content=khuyenmai&page=<?php echo $i; ?>"
             style="margin:0 5px; padding:8px 12px; border-radius:6px; text-decoration:none;
                    <?php echo ($i==$page) ? 'background:#e74c3c;color:#fff;font-weight:600;' : 'background:#fff;border:1px solid #ddd;color:#333;'; ?>">
             <?php echo $i; ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>

  </div>
</li>

<script>
// Countdown từng sản phẩm
function updateCountdown() {
    const countdowns = document.querySelectorAll('.countdown');
    countdowns.forEach(el => {
        const endTime = new Date(el.dataset.endtime).getTime();
        const now = new Date().getTime();
        let diff = endTime - now;

        if(diff <= 0) {
            el.innerText = "⏰ Khuyến mại đã kết thúc";
            return;
        }

        const days = Math.floor(diff / (1000*60*60*24));
        diff %= (1000*60*60*24);
        const hours = Math.floor(diff / (1000*60*60));
        diff %= (1000*60*60);
        const minutes = Math.floor(diff / (1000*60));
        const seconds = Math.floor((diff % (1000*60))/1000);

        el.innerText = `${days}d ${hours}h ${minutes}m ${seconds}s`;
    });
}
setInterval(updateCountdown, 1000);
updateCountdown();
</script>

<?php $conn->close(); ?>
