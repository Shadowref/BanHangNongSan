<?php
session_start();
$conn = new mysqli("localhost","root","","banhangonline");
$conn->set_charset("utf8");
if($conn->connect_error) die("Kết nối thất bại: ".$conn->connect_error);

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
if(!$user_id) {
    echo json_encode(['success'=>false,'msg'=>'Bạn cần đăng nhập']);
    exit;
}

$type = $_POST['type'] ?? ''; // 'review' hoặc 'reply'
$id = intval($_POST['id']);

if($type==='review'){
    $conn->query("UPDATE danhgia SET likes = likes + 1 WHERE id=$id");
    $likes = $conn->query("SELECT likes FROM danhgia WHERE id=$id")->fetch_assoc()['likes'];
    echo json_encode(['success'=>true,'likes'=>$likes]);
}
elseif($type==='reply'){
    $conn->query("UPDATE phanhoi_review SET likes = likes + 1 WHERE id=$id");
    $likes = $conn->query("SELECT likes FROM phanhoi_review WHERE id=$id")->fetch_assoc()['likes'];
    echo json_encode(['success'=>true,'likes'=>$likes]);
}
else{
    echo json_encode(['success'=>false,'msg'=>'Loại không hợp lệ']);
}
