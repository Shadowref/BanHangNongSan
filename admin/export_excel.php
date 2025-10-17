<?php
$conn = mysqli_connect("localhost", "root", "", "banhangonline");
mysqli_set_charset($conn, "utf8");

// Lấy dữ liệu chi tiết
$sql = "SELECT l.id as ma_don, l.ngaygiaodich, t.username as khachhang,
               l.tensanpham, l.soluong, l.giaban, l.tongtien, l.trangthai, l.phuongthucthanhtoan
        FROM lichsugiaodich l
        JOIN taikhoan t ON l.id_nguoidung = t.id
        ORDER BY l.ngaygiaodich ASC";
$result = mysqli_query($conn, $sql);

// Header để Excel nhận dạng là file Excel
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=bao_cao_doanh_thu.xls");
header("Cache-Control: max-age=0");

echo "<table border='1' style='border-collapse:collapse; width:100%; font-family:Arial;'>";
echo "<tr style='background-color:#3498db; color:#fff; text-align:center; font-weight:bold;'>";
echo "<th>STT</th>
      <th>Mã đơn</th>
      <th>Ngày giao dịch</th>
      <th>Khách hàng</th>
      <th>Sản phẩm</th>
      <th>Số lượng</th>
      <th>Giá bán (VNĐ)</th>
      <th>Tổng tiền (VNĐ)</th>
      <th>Trạng thái</th>
      <th>Phương thức thanh toán</th>";
echo "</tr>";

$rowNum = 1;
$totalQuantity = 0;
$totalRevenue = 0;

while($row = mysqli_fetch_assoc($result)){
    // Xử lý trạng thái
    switch($row['trangthai']){
        case 'hoan_tat': $status = 'Hoàn thành'; break;
        case 'dang_cho': $status = 'Đang chờ'; break;
        case 'dang_giao': $status = 'Đang giao'; break;
        default: $status = 'Đã hủy'; break;
    }

    // Xử lý phương thức thanh toán
    switch($row['phuongthucthanhtoan']){
        case 'cod': 
            $payment = 'Đã thanh toán khi nhận hàng'; 
            break;
        case 'momo': 
            $payment = 'Đã thanh toán Online bằng MoMo'; 
            break;
        case 'zalopay': 
            $payment = 'Đã thanh toán Online bằng ZaloPay'; 
            break;
        default: 
            $payment = 'Khác'; 
            break;
    }

    echo "<tr>";
    echo "<td style='text-align:center;'>$rowNum</td>";
    echo "<td style='text-align:center;'>".$row['ma_don']."</td>";
    echo "<td style='text-align:center;'>".date('d/m/Y H:i', strtotime($row['ngaygiaodich']))."</td>";
    echo "<td>".$row['khachhang']."</td>";
    echo "<td>".$row['tensanpham']."</td>";
    echo "<td style='text-align:right;'>".$row['soluong']."</td>";
    echo "<td style='text-align:right;'>".number_format($row['giaban'],0,',','.')."</td>";
    echo "<td style='text-align:right;'>".number_format($row['tongtien']*1000,0,',','.')."</td>";
    echo "<td style='text-align:center;'>$status</td>";
    echo "<td style='text-align:center;'>$payment</td>";
    echo "</tr>";

    $totalQuantity += $row['soluong'];
    $totalRevenue += $row['tongtien'];
    $rowNum++;
}

// Thêm tổng cộng
echo "<tr style='font-weight:bold; background-color:#f1c40f; text-align:center;'>";
echo "<td colspan='5'>Tổng cộng</td>";
echo "<td style='text-align:right;'>$totalQuantity</td>";
echo "<td></td>";
echo "<td style='text-align:right;'>".number_format($totalRevenue*1000,0,',','.')."</td>";
echo "<td colspan='2'></td>";
echo "</tr>";

echo "</table>";
exit;
?>
