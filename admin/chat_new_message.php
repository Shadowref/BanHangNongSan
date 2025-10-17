<?php
include 'connect.php';
session_start();

if(!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin'){
    echo json_encode(['success'=>false,'error'=>'Unauthorized']);
    exit;
}

$adminId = (int)$_SESSION['user_id'];

// Lấy tất cả user có tin nhắn chưa đọc với admin
$stmt = $conn->prepare("
    SELECT t.nguoigui_id, COUNT(*) as cnt, u.username 
    FROM tinnhan t
    JOIN taikhoan u ON u.id = t.nguoigui_id
    WHERE t.nguoinhan_id=? AND t.trangthai='chua_doc'
    GROUP BY t.nguoigui_id
");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$res = $stmt->get_result();
$users = [];
while($row = $res->fetch_assoc()){
    $users[] = [
        'user_id' => $row['nguoigui_id'],
        'username' => $row['username'],
        'count' => (int)$row['cnt']
    ];
}
echo json_encode(['success'=>true,'users'=>$users]);
