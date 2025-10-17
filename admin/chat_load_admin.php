<?php
// admin/chat_load_admin.php
session_start();
include '../admin/connect.php';
header('Content-Type: application/json; charset=utf-8');

// ===== Kiểm tra quyền admin =====
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        'error' => 'Bạn không có quyền truy cập'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$me = (int)$_SESSION['user_id'];
$partner = isset($_GET['partner']) ? (int)$_GET['partner'] : 0;
if ($partner <= 0) {
    echo json_encode([
        'error' => 'Partner không hợp lệ'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ===== Lấy tin nhắn giữa admin và user =====
$stmt = $conn->prepare("
    SELECT t.id, t.nguoigui_id, t.nguoinhan_id, t.noidung, t.thoigian, t.trangthai, u.username
    FROM tinnhan t
    JOIN taikhoan u ON t.nguoigui_id = u.id
    WHERE (t.nguoigui_id=? AND t.nguoinhan_id=?) 
       OR (t.nguoigui_id=? AND t.nguoinhan_id=?)
    ORDER BY t.thoigian ASC
");
if (!$stmt) {
    echo json_encode(['error' => 'Prepare statement thất bại: ' . $conn->error], JSON_UNESCAPED_UNICODE);
    exit;
}
$stmt->bind_param("iiii", $me, $partner, $partner, $me);
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ===== Đánh dấu đã đọc =====
$upd = $conn->prepare("
    UPDATE tinnhan 
    SET trangthai='da_doc' 
    WHERE nguoinhan_id=? AND nguoigui_id=? AND trangthai='chua_doc'
");
if ($upd) {
    $upd->bind_param("ii", $me, $partner);
    $upd->execute();
    $upd->close();
}

// ===== Trả dữ liệu =====
echo json_encode($rows, JSON_UNESCAPED_UNICODE);
