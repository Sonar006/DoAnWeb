<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'connect.php';
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$token = $_SESSION['csrf_token'];
$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$error = '';
$orderComplete = false;
$orderId = filter_input(INPUT_GET, 'order', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($orderId && (int)($_SESSION['last_order_id'] ?? 0) === (int)$orderId) {
    $orderComplete = true;
}
$cart = (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) ? $_SESSION['cart'] : [];
$name = trim((string)($_POST['ten_nguoi_nhan'] ?? ($_SESSION['username'] ?? '')));
$email = trim((string)($_POST['email'] ?? ''));
$phone = trim((string)($_POST['sdt'] ?? ''));
$address = trim((string)($_POST['dia_chi'] ?? ''));
$note = trim((string)($_POST['ghi_chu'] ?? ''));
$orderItems = [];
$total = 0.0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$orderComplete) {
    if (empty($cart)) {
        $error = 'Giỏ hàng đang trống. Hãy thêm sản phẩm trước khi đặt hàng.';
    } elseif (!hash_equals($token, (string)($_POST['csrf_token'] ?? ''))) {
        $error = 'Phiên làm việc hết hạn. Vui lòng tải lại trang rồi thử lại.';
    } elseif ($name === '' || mb_strlen($name) > 120) {
        $error = 'Vui lòng nhập tên người nhận (tối đa 120 ký tự).';
    } elseif ($phone === '' || mb_strlen($phone) > 30) {
        $error = 'Vui lòng nhập số điện thoại hợp lệ.';
    } elseif ($address === '' || mb_strlen($address) > 255) {
        $error = 'Vui lòng nhập địa chỉ giao hàng (tối đa 255 ký tự).';
    } elseif ($email !== '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190)) {
        $error = 'Địa chỉ email không hợp lệ.';
    } else {
        try {
            foreach ($cart as $cartProductId => $cartItem) {
                $productId = filter_var((string)$cartProductId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                $quantity = filter_var($cartItem['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 999]]);
                if (!$productId || !$quantity) {
                    throw new RuntimeException('Số lượng hoặc sản phẩm trong giỏ hàng không hợp lệ.');
                }
                $productStmt = $conn->prepare('SELECT id, name, gia_sp FROM sanpham WHERE id = ? LIMIT 1');
                $productStmt->bind_param('i', $productId);
                $productStmt->execute();
                $dbProduct = $productStmt->get_result()->fetch_assoc();
                if (!$dbProduct) {
                    throw new RuntimeException('Có sản phẩm trong giỏ hàng không còn tồn tại.');
                }
                $unitPrice = (float)$dbProduct['gia_sp'];
                $subtotal = $unitPrice * $quantity;
                $total += $subtotal;
                $orderItems[] = [
                    'product_id' => (int)$dbProduct['id'],
                    'name' => (string)$dbProduct['name'],
                    'price' => $unitPrice,
                    'quantity' => (int)$quantity,
                    'subtotal' => $subtotal
                ];
            }
            if (!$orderItems || $total <= 0) {
                throw new RuntimeException('Không có sản phẩm hợp lệ trong giỏ hàng.');
            }

            $conn->begin_transaction();
            $paymentMethod = 'Thanh toán khi nhận hàng (COD)';
            $status = 'Chờ xác nhận';
            $insertOrder = $conn->prepare('INSERT INTO donhang (ten_nguoi_nhan, email, sdt, dia_chi, ghi_chu, tong_tien, phuong_thuc_thanh_toan, trang_thai) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $insertOrder->bind_param('sssssdss', $name, $email, $phone, $address, $note, $total, $paymentMethod, $status);
            $insertOrder->execute();
            $newOrderId = (int)$conn->insert_id;

            $insertLine = $conn->prepare('INSERT INTO chitiet_donhang (donhang_id, sanpham_id, ten_san_pham, don_gia, so_luong, thanh_tien) VALUES (?, ?, ?, ?, ?, ?)');
            foreach ($orderItems as $item) {
                $insertLine->bind_param('iisdid', $newOrderId, $item['product_id'], $item['name'], $item['price'], $item['quantity'], $item['subtotal']);
                $insertLine->execute();
            }
            $conn->commit();
            $_SESSION['last_order_id'] = $newOrderId;
            unset($_SESSION['cart']);
            header('Location: thanhtoan.php?order=' . $newOrderId);
            exit;
        } catch (Throwable $e) {
            try { $conn->rollback(); } catch (Throwable $ignored) {}
            $error = ($e instanceof RuntimeException) ? $e->getMessage() : 'Chưa thể lưu đơn hàng. Vui lòng thử lại sau.';
        }
    }
}

$displayItems = [];
$displayTotal = 0.0;
if (!$orderComplete && !empty($cart)) {
    foreach ($cart as $cartId => $cartItem) {
        $displayQty = max(1, min(999, (int)($cartItem['quantity'] ?? 1)));
        $displayName = (string)($cartItem['name'] ?? 'Sản phẩm');
        $displayPrice = max(0, (float)($cartItem['price'] ?? 0));
        $displaySubtotal = $displayQty * $displayPrice;
        $displayTotal += $displaySubtotal;
        $displayItems[] = ['name' => $displayName, 'quantity' => $displayQty, 'price' => $displayPrice, 'subtotal' => $displaySubtotal];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán đơn hàng - BookStore</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>

<main class="container checkout-page">
    <div class="checkout-breadcrumb">
        <a href="giohang.php" class="back-link">← Quay lại giỏ hàng</a>
    </div>

    <?php if ($orderComplete): ?>
        <section class="checkout-success-card">
            <div class="success-mark">✓</div>
            <h1>Đặt hàng thành công!</h1>
            <p>Mã đơn hàng của bạn: <strong>#<?= (int)$orderId ?></strong></p>
            <p>Hình thức: Thanh toán khi nhận hàng (COD). Cửa hàng sẽ liên hệ sớm để xác nhận giao hàng.</p>
            <a href="index.php" class="btn" style="margin-top: 20px; display: inline-block;">Tiếp tục mua sắm</a>
        </section>
    <?php elseif (empty($cart)): ?>
        <section class="checkout-success-card">
            <h1>Giỏ hàng đang trống</h1>
            <p>Bạn chưa có sản phẩm nào trong giỏ hàng để thanh toán.</p>
            <a href="index.php" class="btn" style="margin-top: 20px; display: inline-block;">Quay về trang chủ</a>
        </section>
    <?php else: ?>
        <div class="checkout-heading">
            <span class="section-kicker">BOOKSTORE</span>
            <h1>Thanh Toán Đơn Hàng</h1>
            <p>Vui lòng điền đầy đủ thông tin nhận hàng bên dưới. Hỗ trợ thanh toán khi nhận hàng (COD).</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="form-message error" style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px;"><?= $escape($error) ?></div>
        <?php endif; ?>

        <form class="checkout-layout" method="post" action="thanhtoan.php">
            <input type="hidden" name="csrf_token" value="<?= $escape($token) ?>">
            
            <!-- Cột trái: Form thông tin người nhận -->
            <section class="checkout-card form-section">
                <h2>Thông tin người nhận</h2>
                
                <div class="form-group-row">
                    <div class="form-field">
                        <label for="ten_nguoi_nhan">Họ và tên <span class="required">*</span></label>
                        <input id="ten_nguoi_nhan" name="ten_nguoi_nhan" type="text" maxlength="120" required value="<?= $escape($name) ?>" placeholder="Nhập họ và tên đầy đủ">
                    </div>
                    <div class="form-field">
                        <label for="sdt">Số điện thoại <span class="required">*</span></label>
                        <input id="sdt" name="sdt" type="tel" maxlength="30" required value="<?= $escape($phone) ?>" placeholder="Nhập số điện thoại liên hệ">
                    </div>
                </div>

                <div class="form-field">
                    <label for="email">Email (không bắt buộc)</label>
                    <input id="email" name="email" type="email" maxlength="190" value="<?= $escape($email) ?>" placeholder="Nhập email nhận thông báo đơn hàng">
                </div>

                <div class="form-field">
                    <label for="dia_chi">Địa chỉ giao hàng <span class="required">*</span></label>
                    <input id="dia_chi" name="dia_chi" type="text" maxlength="255" required value="<?= $escape($address) ?>" placeholder="Số nhà, tên đường, phường/xã, quận/huyện...">
                </div>

                <div class="form-field">
                    <label for="ghi_chu">Ghi chú đơn hàng (tuỳ chọn)</label>
                    <textarea id="ghi_chu" name="ghi_chu" rows="3" maxlength="2000" placeholder="Ví dụ: Giao hàng giờ hành chính..."><?= $escape($note) ?></textarea>
                </div>

                <div class="payment-method-box">
                    <strong>Phương thức thanh toán</strong>
                    <label class="payment-option">
                        <input type="radio" checked disabled> Thanh toán khi nhận hàng (COD)
                    </label>
                </div>
            </section>

            <!-- Cột phải: Tóm tắt đơn hàng -->
            <aside class="checkout-card order-summary-section">
                <h2>Đơn hàng của bạn (<?= count($displayItems) ?> sản phẩm)</h2>
                <div class="summary-items-list">
                    <?php foreach ($displayItems as $item): ?>
                        <div class="summary-line">
                            <div class="summary-item-meta">
                                <strong><?= $escape($item['name']) ?></strong>
                                <span>Số lượng: <?= $item['quantity'] ?> × <?= number_format($item['price'], 0, ',', '.') ?> đ</span>
                            </div>
                            <span class="summary-subtotal"><?= number_format($item['subtotal'], 0, ',', '.') ?> đ</span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-total-box">
                    <span>Tổng cộng tạm tính</span>
                    <strong class="total-price"><?= number_format($displayTotal, 0, ',', '.') ?> đ</strong>
                </div>

                <button class="btn checkout-submit-btn" type="submit">Xác nhận đặt hàng</button>
                <p class="summary-note">Giá chính xác sẽ được tính theo hệ thống cơ sở dữ liệu khi đơn hàng được tạo thành công.</p>
            </aside>
        </form>
    <?php endif; ?>
</main>
</body>
</html>