<?php
function pdo_connect_mysql() {
    // Cập nhật các chi tiết bên dưới với MySQL của bạn
    $DATABASE_HOST = 'localhost';
    $DATABASE_USER = 'root';
    $DATABASE_PASS = '';
    $DATABASE_NAME = 'shoppingcart';
    try {
        return new PDO('mysql:host=' . $DATABASE_HOST . ';dbname=' . $DATABASE_NAME . ';charset=utf8', $DATABASE_USER, $DATABASE_PASS);
    } catch (PDOException $exception) {
        // Nếu có lỗi với kết nối, dừng kịch bản và hiển thị lỗi.
        exit('Thất bại khi kết nối đến cơ sở dữ liệu!');
    }
}

// Header template, bạn có thể tùy chỉnh theo ý của mình
function template_header($title, $num_items_in_cart) {
echo <<<EOT
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>$title</title>
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <header>
            <div class="content-wrapper">
                <h1>Hệ thống giỏ hàng</h1>
                <nav>
                    <a href="index.php">Trang chủ</a>
                    <a href="index.php?page=products">Sản phẩm</a>
                </nav>
                <div class="link-icons">
                    <a href="index.php?page=cart">
                        <i class="fas fa-shopping-cart"></i><span>$num_items_in_cart</span>
                    </a>
                </div>
            </div>
        </header>
        <main>
EOT;
}

// Template footer
function template_footer() {
    $year = date('Y');
    echo <<<EOT
        </main>
        <footer>
            <div class="content-wrapper">
                <p>&copy; $year, Hệ thống giỏ hàng</p>
            </div>
        </footer>
    </body>
</html>
EOT;
}

// Kiểm tra số lượng sản phẩm trong giỏ hàng
$num_items_in_cart = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
