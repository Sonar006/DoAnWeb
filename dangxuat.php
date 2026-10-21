<?php
session_start();

// Xóa tất cả các biến session đã lưu trữ
$_SESSION = array();

// Hủy hoàn toàn phiên làm việc (session)
session_destroy();

// Chuyển hướng người dùng về trang chủ
header("Location: index.php");
exit();
?>