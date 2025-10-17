<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Thống kê</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #3498db;
            --success: #2ecc71;
            --warning: #f1c40f;
            --danger: #e74c3c;
            --info: #9b59b6;
            --dark: #2c3e50;
            --light: #ecf0f1;
            --gray: #95a5a6;
            --card-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #34495e;
            padding: 20px;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .dashboard-title {
            color: var(--dark);
            font-size: 28px;
            font-weight: 700;
        }
        
        .date-filter {
            display: flex;
            gap: 10px;
            align-items: center;
            background: white;
            padding: 10px 15px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
        }
        
        .date-filter select, .date-filter button {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: white;
            color: var(--dark);
        }
        
        .date-filter button {
            background: var(--primary);
            color: white;
            border: none;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .date-filter button:hover {
            background: #2980b9;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
           
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
        }
        
        .stat-card.primary::before { background: var(--primary); }
        .stat-card.success::before { background: var(--success); }
        .stat-card.warning::before { background: var(--warning); }
        .stat-card.danger::before { background: var(--danger); }
        
        .stat-icon {
            font-size: 32px;
            margin-bottom: 15px;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            color: white;
        }
        
        .stat-card.primary .stat-icon { background: var(--primary); }
        .stat-card.success .stat-icon { background: var(--success); }
        .stat-card.warning .stat-icon { background: var(--warning); }
        .stat-card.danger .stat-icon { background: var(--danger); }
        
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--dark);
        }
        
        .stat-label {
            color: var(--gray);
            font-size: 14px;
            font-weight: 500;
        }
        
        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
        }
        
        .chart-title {
            margin-bottom: 20px;
            color: var(--dark);
            font-size: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .recent-orders {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: #f8f9fa;
            color: var(--dark);
            font-weight: 600;
        }
        
        tr:hover {
            background-color: #f9f9f9;
        }
        
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-success {
            background: #e7f7ef;
            color: var(--success);
        }
        
        .badge-warning {
            background: #fef5e6;
            color: var(--warning);
        }
        
        .badge-danger {
            background: #fceae9;
            color: var(--danger);
        }
        
        @media (max-width: 768px) {
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
<?php
$conn = mysqli_connect("localhost", "root", "", "banhangonline");
if (!$conn) die("Kết nối thất bại: " . mysqli_connect_error());
mysqli_set_charset($conn, "utf8");

// Thống kê
$tongsp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as tongsp FROM sanpham"))['tongsp'] ?? 0;
$tonghd = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as tonghd FROM lichsugiaodich"))['tonghd'] ?? 0;
$doanhthu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(tongtien) as doanhthu FROM lichsugiaodich"))['doanhthu'] ?? 0;
$tonguser = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as tonguser FROM taikhoan"))['tonguser'] ?? 0;

// Biểu đồ doanh thu theo tháng từ lichsugiaodich
$sql_chart = "SELECT MONTH(ngaygiaodich) as thang, SUM(tongtien) as tong
              FROM lichsugiaodich
              GROUP BY MONTH(ngaygiaodich)
              ORDER BY thang";
$result_chart = mysqli_query($conn, $sql_chart);
$data_chart = [];
while ($row = mysqli_fetch_assoc($result_chart)) $data_chart[] = $row;

// Đơn hàng gần đây từ lichsugiaodich
// Đơn hàng gần đây
$sql_recent_orders = "SELECT lsd.id as madon, tk.username as hoten, lsd.tongtien, lsd.ngaygiaodich, lsd.trangthai
                      FROM lichsugiaodich lsd
                      JOIN taikhoan tk ON lsd.id_nguoidung = tk.id
                      ORDER BY lsd.ngaygiaodich DESC
                      LIMIT 5";
$result_recent_orders = mysqli_query($conn, $sql_recent_orders);
$recent_orders = [];
while ($row = mysqli_fetch_assoc($result_recent_orders)) $recent_orders[] = $row;
?>

<div class="dashboard-header">
    <h1 class="dashboard-title"><i class="fas fa-chart-line"></i> Dashboard Thống kê</h1>
    <div class="date-filter">
        <select id="timeRange">
            <option value="7">7 ngày qua</option>
            <option value="30" selected>30 ngày qua</option>
            <option value="90">3 tháng qua</option>
            <option value="365">Năm nay</option>
        </select>
        <button onclick="applyFilter()">Áp dụng</button>
    </div>
</div>

<!-- Cards -->
<div class="stats-cards">
    <div class="stat-card primary">
        <div class="stat-icon"><i class="fas fa-box"></i></div>
        <div class="stat-value"><?= $tongsp; ?></div>
        <div class="stat-label">Sản phẩm</div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
        <div class="stat-value"><?= number_format($doanhthu,0,',','.'); ?> đ</div>
        <div class="stat-label">Doanh thu</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
        <div class="stat-value"><?= $tonghd; ?></div>
        <div class="stat-label">Đơn hàng</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-value"><?= $tonguser; ?></div>
        <div class="stat-label">Người dùng</div>
    </div>
</div>

<!-- Biểu đồ doanh thu -->
<div class="chart-container">
    <h3 class="chart-title"><i class="fas fa-chart-bar"></i> Doanh thu theo tháng</h3>
    <canvas id="doanhthuChart" height="100"></canvas>
</div>

<!-- Đơn hàng gần đây -->
<div class="recent-orders">
    <h3 class="chart-title"><i class="fas fa-history"></i> Đơn hàng gần đây</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Ngày đặt</th>
                    <th>Khách hàng</th>
                    <th>Thành tiền</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($recent_orders)): foreach($recent_orders as $order): ?>
                <tr>
                    <td>#<?= $order['madon'] ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($order['ngaygiaodich'])) ?>
</td>
                    <td><?= $order['hoten'] ?></td>
                    <td><?= number_format($order['tongtien'],0,',','.'); ?> đ</td>
                    <td>
                        <?php
                        $status_class = 'badge-danger'; $status_label='Đã hủy';
                        if($order['trangthai']=='hoan_tat'){ $status_class='badge-success'; $status_label='Hoàn thành'; }
                        elseif($order['trangthai']=='dang_cho'){ $status_class='badge-warning'; $status_label='Đang chờ'; }
                        elseif($order['trangthai']=='dang_giao'){ $status_class='badge-info'; $status_label='Đang giao'; }
                        ?>
                        <span class="badge <?= $status_class ?>"><?= $status_label ?></span>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="5" style="text-align:center;">Không có đơn hàng nào</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<!-- Thêm Font Awesome CDN nếu chưa có -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Nút Xuất Excel đẹp -->
<button 
    style="
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background-color: #217346;  /* màu xanh Excel */
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        font-size: 16px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        transition: background-color 0.3s, transform 0.2s;
    "
    onmouseover="this.style.backgroundColor='#1b5e30'; this.style.transform='scale(1.05)';"
    onmouseout="this.style.backgroundColor='#217346'; this.style.transform='scale(1)';"
    onclick="window.location.href='export_excel.php'">
    <i class="fa-solid fa-file-excel" style="font-size:20px;"></i> Xuất báo cáo Excel
</button>


</div>

<script>
let dataPHP = <?php echo json_encode($data_chart); ?>;
let labels = dataPHP.map(item => "Tháng " + item.thang);
let values = dataPHP.map(item => item.tong);

const ctx = document.getElementById('doanhthuChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: { labels: labels, datasets:[{label:'Doanh thu (VNĐ)',data:values,backgroundColor:'#2ecc71',borderRadius:6,hoverBackgroundColor:'#27ae60'}] },
    options:{
        responsive:true,
        plugins:{ legend:{ display:true, position:'top' }, tooltip:{ callbacks:{ label:function(ctx){ return ctx.dataset.label + ': ' + ctx.parsed.y.toLocaleString("vi-VN") + " đ"; }}} },
        scales:{ y:{ beginAtZero:true, ticks:{ callback:value => value.toLocaleString("vi-VN")+" đ" } } }
    }
});

function applyFilter(){
    const timeRange = document.getElementById('timeRange').value;
    alert('Đã chọn lọc dữ liệu cho '+timeRange+' ngày. Trong thực tế, bạn sẽ gửi AJAX request để cập nhật dữ liệu.');
}
</script>
</body>
</html>