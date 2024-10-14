<?php
// Bắt đầu session
session_start();
// Nhúng functions và kết nối đến cơ sở dữ liệu bằng PDO MySQL
include 'functions.php';
$pdo = pdo_connect_mysql();
// Trang được đặt thành trang chủ (home.php) theo mặc định, do đó khi khách truy cập, đó sẽ là trang họ thấy.
$page = isset($_GET['page']) && file_exists($_GET['page'] . '.php') ? $_GET['page'] : 'home';
// Nhúng và hiển thị trang được yêu cầu
include $page . '.php';
?>
