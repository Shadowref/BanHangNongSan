<?php
session_start();
include '../banhangonline/admin/connect.php';
header('Content-Type: application/json; charset=utf-8');

if(!isset($_SESSION['user_id'])){
    echo json_encode(['error'=>'not_logged_in'], JSON_UNESCAPED_UNICODE);
    exit;
}

$me = (int)$_SESSION['user_id'];
$adminId = 1; // ID admin cố định hoặc lấy từ db

$stmt = $conn->prepare("
    SELECT t.id, t.nguoigui_id, t.nguoinhan_id, t.noidung, t.thoigian, t.trangthai
    FROM tinnhan t
    WHERE (t.nguoigui_id=? AND t.nguoinhan_id=?) 
       OR (t.nguoigui_id=? AND t.nguoinhan_id=?)
    ORDER BY t.thoigian ASC
");
$stmt->bind_param("iiii", $me, $adminId, $adminId, $me);
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// đánh dấu đã đọc
$upd = $conn->prepare("
    UPDATE tinnhan 
    SET trangthai='da_doc' 
    WHERE nguoinhan_id=? AND nguoigui_id=? AND trangthai='chua_doc'
");
if($upd){
    $upd->bind_param("ii", $me, $adminId);
    $upd->execute();
    $upd->close();
}

echo json_encode($rows, JSON_UNESCAPED_UNICODE);
