<?php
session_start();
$conn = new mysqli("localhost", "root", "", "banhangonline");
$conn->set_charset("utf8");

$review_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$type = $_POST['type'] ?? 'review';
$table = ($type === 'review') ? 'danhgia' : 'phanhoi_review';

if(!isset($_SESSION['liked'][$type])) $_SESSION['liked'][$type] = [];

// Toggle like
if(isset($_SESSION['liked'][$type][$review_id]) && $_SESSION['liked'][$type][$review_id]){
    $conn->query("UPDATE $table SET likes = CASE WHEN likes>0 THEN likes-1 ELSE 0 END WHERE id=$review_id");
    $_SESSION['liked'][$type][$review_id] = false;
    $liked = false;
} else {
    $conn->query("UPDATE $table SET likes = likes+1 WHERE id=$review_id");
    $_SESSION['liked'][$type][$review_id] = true;
    $liked = true;
}

$res = $conn->query("SELECT likes FROM $table WHERE id=$review_id");
$likes = $res ? $res->fetch_assoc()['likes'] : 0;

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'likes' => $likes,
    'user_liked' => $liked
]);
exit;
