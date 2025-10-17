<?php
// Lấy page hiện tại từ URL
$current = $_GET['admin'] ?? 'trangchu';
?>

<h3>Quản lý</h3>
<ul>
    <li><a href="?admin=trangchu" class="<?= $current=='trangchu' ? 'active' : '' ?>">Trang chủ</a></li>
    <li><a href="?admin=xacnhandonhang" class="<?= $current=='xacnhandonhang' ? 'active' : '' ?>">Xác nhận đơn hàng</a></li>
    <li><a href="?admin=hienthisp" class="<?= $current=='hienthisp' ? 'active' : '' ?>">Quản lý sản phẩm</a></li>
    <li><a href="?admin=hienthidm" class="<?= $current=='hienthidm' ? 'active' : '' ?>">Quản lý danh mục</a></li>
    <li><a href="?admin=hienthihd" class="<?= $current=='hienthihd' ? 'active' : '' ?>">Quản lý hóa đơn</a></li>
    <li><a href="?admin=doanhthu" class="<?= $current=='doanhthu' ? 'active' : '' ?>">Thống kê doanh thu</a></li>
    <li><a href="?admin=tonkho" class="<?= $current=='tonkho' ? 'active' : '' ?>">Tính tồn kho</a></li>
    <li><a href="?admin=hienthind" class="<?= $current=='hienthind' ? 'active' : '' ?>">Quản lý người dùng</a></li>
    <li><a href="?admin=hienthitt" class="<?= $current=='hienthitt' ? 'active' : '' ?>">Quản lý tin tức</a></li>
    <li><a href="?admin=hienthiht" class="<?= $current=='hienthiht' ? 'active' : '' ?>">Quản lý hỗ trợ</a></li>
    <li><a href="?admin=hienthihoidap" class="<?= $current=='hienthihoidap' ? 'active' : '' ?>">Quản lý hỏi đáp</a></li>
</ul>

<style>
/* CSS Sidebar */
ul { list-style:none; padding:0; }
li { margin:8px 0; }
a { text-decoration:none; color:#333; padding:6px 10px; display:block; border-radius:6px; }
a.active, a:hover { background:#27ae60; color:#fff; }
</style>
