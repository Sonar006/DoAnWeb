<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kết nối cơ sở dữ liệu
require_once 'connect.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$product = null;

if ($id && isset($conn) && $conn instanceof mysqli) {
    $stmt = $conn->prepare('SELECT * FROM sanpham WHERE id = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result ? $result->fetch_assoc() : null;
        $stmt->close();
    }
}

$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$name = (string)($product['name'] ?? 'Không tìm thấy sản phẩm');
$image = str_replace('\\', '/', (string)($product['image'] ?? ''));
$price = (float)($product['gia_sp'] ?? $product['sale_price'] ?? 0);
$description = (string)($product['description'] ?? $product['chitiet_sp'] ?? '');

// Xử lý khi người dùng bấm "Thêm vào giỏ hàng"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart']) && $product) {
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    
    // Khởi tạo giỏ hàng nếu chưa có
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Nếu sản phẩm đã có trong giỏ hàng thì cộng dồn số lượng
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$id] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $price,
            'image' => $image,
            'quantity' => $quantity
        ];
    }
    
    // Chuyển hướng đến trang giỏ hàng
    header('Location: giohang.php');
    exit();
}

if (!$product) {
    http_response_code(404);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $escape($name) ?> - BookStore</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
// Gọi file header giao diện
if (is_file(__DIR__ . '/header.php')) {
    include __DIR__ . '/header.php';
}
?>

<div class="container">
    <div style="margin: 20px 0;">
        <a href="index.php" class="btn" style="background: #7f8c8d; font-size: 14px; padding: 6px 12px;">← Quay lại trang chủ</a>
    </div>

    <?php if (!$product): ?>
        <div class="form-box" style="text-align: center; max-width: 500px; margin: 50px auto;">
            <h2>Không tìm thấy sản phẩm</h2>
            <p style="margin: 15px 0; color: #666;">Sản phẩm có thể đã bị xóa hoặc đường dẫn không chính xác.</p>
            <a href="index.php" class="btn">Về trang chủ</a>
        </div>
    <?php else: ?>
        <div class="product-detail-container">
            <!-- Khung hình ảnh sản phẩm -->
            <div class="product-detail-image">
                <?php if ($image !== ''): ?>
                    <img src="<?= $escape($image) ?>" alt="<?= $escape($name) ?>">
                <?php else: ?>
                    <div style="padding: 50px; text-align: center; color: #999;">Sản phẩm chưa có hình ảnh</div>
                <?php endif; ?>
            </div>

            <!-- Khung thông tin chi tiết -->
            <div class="product-detail-info">
                <span style="background: #3498db; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">BOOKSTORE</span>
                <h1 style="font-size: 26px; margin: 12px 0; color: #333;"><?= $escape($name) ?></h1>
                
                <div class="price" style="font-size: 24px; margin: 15px 0; color: #e74c3c; font-weight: bold;"><?= number_format($price, 0, ',', '.') ?> đ</div>

                <!-- Form chọn số lượng và thêm vào giỏ hàng -->
                <form method="POST" action="">
                    <div style="margin: 20px 0; display: flex; align-items: center; gap: 15px;">
                        <label for="quantity" style="font-weight: bold; color: #555;">Số lượng:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" style="width: 80px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; text-align: center; font-size: 16px;">
                    </div>

                    <button type="submit" name="add_to_cart" class="btn" style="padding: 12px 25px; font-size: 16px; cursor: pointer; border: none; width: 100%; text-align: center; background: #27ae60;">
                        🛒 Thêm vào giỏ hàng
                    </button>
                </form>

                <h3 style="margin-top: 30px; font-size: 18px; border-bottom: 2px solid #eee; padding-bottom: 8px; color: #333;">Mô tả sản phẩm</h3>
                <div style="margin-top: 15px; line-height: 1.8; color: #555;">
                    <?= trim($description) !== '' ? nl2br($escape($description)) : 'Chưa có mô tả chi tiết cho sản phẩm này.' ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>