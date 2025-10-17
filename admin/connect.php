<?php
$host = "localhost";     
$user = "root";          
$pass = "";              
$db   = "banhangonline"; 
// Kết nối MySQLi
$conn = new mysqli($host, $user, $pass, $db);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Thiết lập charset UTF-8 để hỗ trợ tiếng Việt
$conn->set_charset("utf8");
?>
