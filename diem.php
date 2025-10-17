<?php
session_start();
include 'config.php'; // file kết nối DB

// Lấy id người dùng từ session (sau khi login)
$id_nguoidung = $_SESSION['user_id'];

// Lấy thông tin điểm hiện tại từ bảng taikhoan
$sql_user = "SELECT username, diem FROM taikhoan WHERE id = $id_nguoidung";
$result_user = mysqli_query($conn, $sql_user);
$user = mysqli_fetch_assoc($result_user);

// Lấy lịch sử điểm từ bảng lichsu_diem
$sql_history = "SELECT * FROM lichsu_diem WHERE id_nguoidung = $id_nguoidung ORDER BY ngaycapnhat DESC";
$result_history = mysqli_query($conn, $sql_history);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Điểm tích lũy</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f9f9f9; margin: 20px; }
    .container { width: 80%; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);}
    h2 { color: #333; }
    .point-box { padding: 15px; background: #eaf7ff; border-radius: 8px; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
    th { background: #007bff; color: white; }
    tr:nth-child(even) { background: #f2f2f2; }
    .cong { color: green; font-weight: bold; }
    .tru { color: red; font-weight: bold; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Xin chào, <?php echo $user['username']; ?> 👋</h2>
    <div class="point-box">
      <h3>🎁 Điểm tích lũy hiện tại: <span style="color:blue;"><?php echo $user['diem']; ?></span> điểm</h3>
    </div>

    <h3>Lịch sử điểm</h3>
    <table>
      <tr>
        <th>Ngày cập nhật</th>
        <th>Số điểm</th>
        <th>Loại</th>
        <th>Mô tả</th>
      </tr>
      <?php while($row = mysqli_fetch_assoc($result_history)) { ?>
        <tr>
          <td><?php echo $row['ngaycapnhat']; ?></td>
          <td><?php echo $row['diem']; ?></td>
          <td class="<?php echo $row['loai']; ?>">
            <?php echo ($row['loai'] == 'cong') ? '+ Điểm' : '- Điểm'; ?>
          </td>
          <td><?php echo $row['mota']; ?></td>
        </tr>
      <?php } ?>
    </table>
  </div>
</body>
</html>
