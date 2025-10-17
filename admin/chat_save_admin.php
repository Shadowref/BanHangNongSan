<?php
// admin/chat_save_admin.php
session_start();
include '../connetion/connet.php';
header('Content-Type: application/json; charset=utf-8');

// chỉ cho admin gửi
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(['success'=>false,'error'=>'not_admin']);
    exit;
}

$adminId = (int)$_SESSION['user_id'];
$nguoinhan = isset($_POST['partner']) ? (int)$_POST['partner'] : 0;
$noidung = trim($_POST['message'] ?? '');

if ($nguoinhan <= 0 || $noidung === '') {
    echo json_encode(['success'=>false,'error'=>'invalid_data']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO tinnhan (nguoigui_id, nguoinhan_id, noidung) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $adminId, $nguoinhan, $noidung);
$ok = $stmt->execute();
$stmt->close();

echo json_encode(['success'=>$ok]);
