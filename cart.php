<?php
// Nếu người dùng đã bấm nút "Thêm vào giỏ hàng" trên trang sản phẩm, chúng ta có thể kiểm tra các dữ liệu form
if (isset($_POST['product_id'], $_POST['quantity']) && is_numeric($_POST['product_id']) && is_numeric($_POST['quantity'])) {
    // Đặt các biến post để chúng ta dễ dàng nhận dạng, cũng đảm bảo chúng là số nguyên
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    // Chuẩn bị lệnh SQL, chúng ta sẽ kiểm tra xem sản phẩm có tồn tại trong database hay không
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$_POST['product_id']]);
    // Lấy sản phẩm từ database và trả về kết quả dưới dạng mảng
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    // Kiểm tra xem sản phẩm có tồn tại (mảng không trống) hay không
    if ($product && $quantity > 0) {
        // Sản phẩm tồn tại trong database, bây giờ chúng ta có thể tạo/ cập nhật biến session cho giỏ hàng
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            if (array_key_exists($product_id, $_SESSION['cart'])) {
                // Sản phẩm đã tồn tại trong giỏ hàng, chỉ cần cập nhật số lượng
                $_SESSION['cart'][$product_id] += $quantity;
            } else {
                // Sản phẩm không tồn tại trong giỏ hàng, thêm vào
                $_SESSION['cart'][$product_id] = $quantity;
            }
        } else {
            // Không có sản phẩm nào trong giỏ hàng, thêm sản phẩm đầu tiên vào
            $_SESSION['cart'] = array($product_id => $quantity);
        }
    }
    // Ngăn chặn resubmission...
    header('location: index.php?page=cart');
    exit;
}

// Xóa sản phẩm khỏi giỏ hàng, kiểm tra tham số URL "remove", tham số này là id sản phẩm, đảm bảo nó là số nguyên và kiểm tra xem có trong giỏ hàng hay không
if (isset($_GET['remove']) && is_numeric($_GET['remove']) && isset($_SESSION['cart']) && isset($_SESSION['cart'][$_GET['remove']])) {
    // Xóa sản phẩm khỏi giỏ hàng
    unset($_SESSION['cart'][$_GET['remove']]);
}

// Cập nhật số lượng sản phẩm trong giỏ hàng nếu người dùng bấm nút "Cập nhật" trên trang giỏ hàng
if (isset($_POST['update']) && isset($_SESSION['cart'])) {
    // Lặp qua các dữ liệu post để chúng ta có thể cập nhật số lượng cho mỗi sản phẩm trong giỏ hàng
    foreach ($_POST as $k => $v) {
        if (strpos($k, 'quantity') !== false && is_numeric($v)) {
            $id = str_replace('quantity-', '', $k);
            $quantity = (int)$v;
            // Luôn làm các kiểm tra và xác thực
            if (is_numeric($id) && isset($_SESSION['cart'][$id]) && $quantity > 0) {
                // Cập nhật số lượng mới
                $_SESSION['cart'][$id] = $quantity;
            }
        }
    }
    // Ngăn chặn resubmission...
    header('Location: index.php?page=cart');
    exit;
}

// Đưa người dùng đến trang đặt hàng nếu họ bấm nút "Đặt hàng" trên trang giỏ hàng, cũng đảm bảo giỏ hàng không trống
if (isset($_POST['placeorder']) && isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    header('Location: index.php?page=placeorder');
    exit;
}

// Kiểm tra biến session cho các sản phẩm trong giỏ hàng
$products_in_cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
$products = array();
$subtotal = 0.00;
// Nếu có sản phẩm trong giỏ hàng
if ($products_in_cart) {
    // Có sản phẩm trong giỏ hàng, chúng ta cần chọn các sản phẩm đó từ database
    // Mảng sản phẩm trong giỏ hàng sang chuỗi dấu hỏi, chúng ta cần lệnh SQL bao gồm IN (?,?,?,...etc)
    $array_to_question_marks = implode(',', array_fill(0, count($products_in_cart), '?'));
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id IN (' . $array_to_question_marks . ')');
    // Chúng ta chỉ cần các key của mảng, không cần giá trị, các key là id của các sản phẩm
    $stmt->execute(array_keys($products_in_cart));
    // Lấy các sản phẩm từ database và trả về kết quả dưới dạng mảng
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Tính tổng giá
    foreach ($products as $product) {
        $subtotal += (float)$product['price'] * (int)$products_in_cart[$product['id']];
    }
}
?>

<?=template_header('Giỏ hàng', $num_items_in_cart) ?>

<div class="cart content-wrapper">
    <h1>Giỏ hàng</h1>
    <form action="index.php?page=cart" method="post">
        <table>
            <thead>
                <tr>
                    <td colspan="2">Sản phẩm</td>
                    <td>Giá</td>
                    <td>Số lượng</td>
                    <td>Tổng</td>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="5" style="text-align:center;">Giỏ hàng trống</td>
                </tr>
                <?php else: ?>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td class="img">
                        <a href="index.php?page=product&id=<?=$product['id']?>">
                            <img src="img/<?=$product['img']?>" width="50" height="50" alt="<?=$product['title']?>">
                        </a>
                    </td>
                    <td>
                        <a href="index.php?page=product&id=<?=$product['id']?>"><?=$product['title']?></a>
                        <br>
                        <a href="index.php?page=cart&remove=<?=$product['id']?>" class="remove">Xóa</a>
                    </td>
                    <td class="price">&dollar;<?=$product['price']?></td>
                    <td class="quantity">
                        <input type="number" name="quantity-<?=$product['id']?>" value="<?=$products_in_cart[$product['id']]?>" min="1" max="<?=$product['quantity']?>" placeholder="Số lượng" required>
                    </td>
                    <td class="price">&dollar;<?=$product['price'] * $products_in_cart[$product['id']]?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="subtotal">
            <span class="text">Tổng</span>
            <span class="price">&dollar;<?=$subtotal?></span>
        </div>
        <div class="buttons">
            <input type="submit" value="Cập nhật" name="update">
            <input type="submit" value="Đặt hàng" name="placeorder">
        </div>
    </form>
</div>

<?=template_footer()?>
