<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$conn = new mysqli("localhost", "root", "", "banhangonline");
$conn->set_charset("utf8");

$user_id = $_SESSION['user_id'] ?? 0;

if(!$user_id) {
    echo json_encode(['success'=>false, 'msg'=>'Chưa đăng nhập']);
    exit;
}

// Lấy 5 thông báo gần nhất
$stmt = $conn->prepare("SELECT * FROM thongbao WHERE user_id=? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while($row = $result->fetch_assoc()){
    $notifications[] = $row;
}

// Đếm số thông báo chưa đọc
$stmt2 = $conn->prepare("SELECT COUNT(*) AS total FROM thongbao WHERE user_id=? AND is_read=0");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$count_result = $stmt2->get_result()->fetch_assoc();
$noti_count = $count_result['total'] ?? 0;

echo json_encode(['success'=>true, 'notifications'=>$notifications, 'count'=>$noti_count]);

$stmt->close();
$stmt2->close();
$conn->close();
?>
