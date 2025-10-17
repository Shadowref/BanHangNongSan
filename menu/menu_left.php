<?php
$conn = new mysqli("localhost", "root", "", "banhangonline");
$conn->set_charset("utf8");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

// Lấy danh sách thương hiệu từ database (chỉnh tên cột đúng)
$sql = "SELECT id, tenthuonghieu FROM thuonghieu ORDER BY id ASC";
$result = $conn->query($sql);

if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        echo '<li><a href="index.php?content=hang&id='.$row['id'].'">'.htmlspecialchars($row['tenthuonghieu']).'</a></li>';
    }
}
?>
