<?php
session_start();

if (isset($_GET['index'])) {
    $index = $_GET['index'];
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
    }
}

// Quay về giỏ hàng
header("Location:  ../index.php?content=giohang");
exit;
?>
