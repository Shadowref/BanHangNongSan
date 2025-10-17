<?php
$user_id = $_SESSION['user_id'] ?? 0;
if(!$user_id){
    echo "Vui lòng đăng nhập!";
    exit;
}

$conn->set_charset("utf8");
$stmt = $conn->prepare("SELECT * FROM thongbao WHERE user_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Thông báo của bạn</h2>
<ul>
<?php while($row = $result->fetch_assoc()){ ?>
    <li style="<?php echo $row['is_read']==0?'font-weight:bold;':''; ?>">
        <?php echo htmlspecialchars($row['message']); ?> 
        <small>(<?php echo $row['created_at']; ?>)</small>
    </li>
<?php } ?>
</ul>

<?php
// Đánh dấu đã đọc
$conn->query("UPDATE thongbao SET is_read=1 WHERE user_id=$user_id AND is_read=0");
?>
