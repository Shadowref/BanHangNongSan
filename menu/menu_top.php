<?php
$conn = new mysqli("localhost", "root", "", "banhangonline");
$conn->set_charset("utf8");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

// Lấy danh sách loại sản phẩm từ database (chỉnh tên bảng và cột đúng)
$sql = "SELECT id, tenloai FROM loaisanpham ORDER BY id ASC"; 
$result = $conn->query($sql);

if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        echo '<li><a href="index.php?content=loai&id='.$row['id'].'">'.htmlspecialchars($row['tenloai']).'</a></li>';
    }
}
?>
