<?php
session_start();

if (isset($_POST['soluong'])) {
    foreach ($_POST['soluong'] as $index => $sl) {
        $_SESSION['cart'][$index]['soluong'] = max(1, intval($sl));
    }
}

header("Location:  ../index.php?content=giohang");
exit();
