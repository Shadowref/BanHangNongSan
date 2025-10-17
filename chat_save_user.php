<?php
session_start();
include '../banhangonline/admin/connect.php';
header('Content-Type: application/json; charset=utf-8');

if(!isset($_SESSION['user_id'])){
    echo json_encode(['success'=>false,'error'=>'not_logged_in']);
    exit;
}

$me = (int)$_SESSION['user_id'];
$adminId = 1; // ID admin cố định
$msg = trim($_POST['message'] ?? '');
if($msg === ''){
    echo json_encode(['success'=>false,'error'=>'empty_message']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO tinnhan (nguoigui_id, nguoinhan_id, noidung) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $me, $adminId, $msg);
$ok = $stmt->execute();
$stmt->close();

echo json_encode(['success'=>$ok]);
