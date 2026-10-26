<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'connect.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: dangnhap.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$token = $_SESSION['csrf_token'];

$messages = [
    'added'         => 'Đã thêm sản phẩm thành công.',
    'updated'       => 'Đã cập nhật sản phẩm thành công.',
    'deleted'       => 'Đã xóa sản phẩm thành công.',
    'notfound'      => 'Không tìm thấy sản phẩm cần thao tác.',
    'error'         => 'Không thể thực hiện thao tác. Vui lòng kiểm tra lại dữ liệu.',
    'csrf'          => 'Phiên làm việc hết hạn hoặc mã bảo vệ không hợp lệ. Hãy thử lại.',
    'order_updated' => 'Đã cập nhật trạng thái đơn hàng thành công.'
];
$msgKey = (string)($_GET['msg'] ?? '');
$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');

// --- XỬ LÝ CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_order'])) {
    $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $newStatus = trim((string)($_POST['new_status'] ?? ''));
    $csrf = (string)($_POST['csrf_token'] ?? '');

    if (hash_equals($token, $csrf) && $orderId && in_array($newStatus, ['Đã xác nhận', 'Đã hủy', 'Đang giao', 'Hoàn tất'], true)) {
        $stmtOrder = $conn->prepare('UPDATE donhang SET trang_thai = ? WHERE id = ?');
        if ($stmtOrder) {
            $stmtOrder->bind_param('si', $newStatus, $orderId);
            $stmtOrder->execute();
            $stmtOrder->close();
            header('Location: admin.php?tab=orders&msg=order_updated');
            exit;
        }
    } else {
        header('Location: admin.php?msg=csrf');
        exit;
    }
}

// Xác định tab hiện tại (mặc định là 'orders')
$currentTab = $_GET['tab'] ?? 'orders';
if (!in_array($currentTab, ['orders', 'products'], true)) {
    $currentTab = 'orders';
}

// Lấy danh sách sản phẩm
$result = $conn->query('SELECT id, name, gia_sp, image, danhmuc_id FROM sanpham ORDER BY id DESC');

// Lấy danh sách đơn hàng kèm chi tiết sản phẩm
$ordersResult = $conn->query(
    'SELECT d.*, u.username AS tai_khoan
     FROM donhang AS d
     LEFT JOIN users AS u ON u.id = d.user_id
     ORDER BY d.id DESC'
);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Trang quản trị Admin - BookStore</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>
<main class="container admin-page">

    <?php if (isset($messages[$msgKey])): ?>
        <p class="form-message <?= in_array($msgKey, ['error','csrf'], true) ? 'error' : 'success' ?>">
            <?= $escape($messages[$msgKey]) ?>
        </p>
    <?php endif; ?>

    <div class="admin-top-header">
        <span class="section-kicker">BOOKSTORE ADMIN</span>
        <h1>Bảng điều khiển quản trị</h1>
    </div>

    <!-- 2 Ô CHỨC NĂNG PHÍA TRÊN -->
    <div class="admin-dashboard-cards">
        <div class="admin-card-box <?= $currentTab === 'orders' ? 'active' : '' ?>" onclick="switchTab('orders')">
            <div class="card-icon">📦</div>
            <div class="card-info">
                <h3>Quản lý đơn hàng</h3>
                <p>Xem và cập nhật trạng thái đơn hàng của khách hàng</p>
            </div>
            <div class="card-arrow">➔</div>
        </div>

        <div class="admin-card-box <?= $currentTab === 'products' ? 'active' : '' ?>" onclick="switchTab('products')">
            <div class="card-icon">📚</div>
            <div class="card-info">
                <h3>Quản lý sản phẩm</h3>
                <p>Thêm, sửa, xóa các sản phẩm sách và văn phòng phẩm</p>
            </div>
            <div class="card-arrow">➔</div>
        </div>
    </div>

    <!-- ================= PHẦN 1: QUẢN LÝ ĐƠN HÀNG ================= -->
    <section id="section-orders" class="admin-section" style="display: <?= $currentTab === 'orders' ? 'block' : 'none' ?>;">
        <div class="admin-heading">
            <div>
                <h2>Danh sách đơn hàng</h2>
            </div>
        </div>

        <div class="table-scroll">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Mã ĐH</th>
                        <th>Tài khoản</th>
                        <th>Khách hàng & SĐT</th>
                        <th>Địa chỉ giao hàng</th>
                        <th>Sản phẩm đặt mua</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($ordersResult && $ordersResult->num_rows > 0): 
                    while ($order = $ordersResult->fetch_assoc()): 
                        $itemsStmt = $conn->prepare('SELECT ten_san_pham, so_luong, don_gia, thanh_tien FROM chitiet_donhang WHERE donhang_id = ?');
                        $itemsStmt->bind_param('i', $order['id']);
                        $itemsStmt->execute();
                        $itemsResult = $itemsStmt->get_result();
                ?>
                    <tr>
                        <td><strong>#<?= (int)$order['id'] ?></strong><br><small><?= $escape(date('d/m/Y H:i', strtotime($order['ngay_dat']))) ?></small></td>
                        <td><?= !empty($order['tai_khoan']) ? $escape($order['tai_khoan']) : 'Khách vãng lai / đơn cũ' ?></td>
                        <td>
                            <strong><?= $escape($order['ten_nguoi_nhan']) ?></strong><br>
                            📞 <?= $escape($order['sdt']) ?><br>
                            <?= $order['email'] ? '<small>✉ ' . $escape($order['email']) . '</small>' : '' ?>
                        </td>
                        <td><?= $escape($order['dia_chi']) ?><?= $order['ghi_chu'] ? '<br><em>Ghi chú: ' . $escape($order['ghi_chu']) . '</em>' : '' ?></td>
                        <td>
                            <ul class="order-item-list">
                                <?php while ($item = $itemsResult->fetch_assoc()): ?>
                                    <li>• <?= $escape($item['ten_san_pham']) ?> × <strong><?= (int)$item['so_luong'] ?></strong> (<?= number_format((float)$item['don_gia'], 0, ',', '.') ?>đ)</li>
                                <?php endwhile; $itemsStmt->close(); ?>
                            </ul>
                        </td>
                        <td><strong class="order-price"><?= number_format((float)$order['tong_tien'], 0, ',', '.') ?> đ</strong></td>
                        <td>
                            <?php 
                                $status = $order['trang_thai'];
                                $badgeClass = match($status) {
                                    'Đã xác nhận' => 'badge-success',
                                    'Đã hủy' => 'badge-danger',
                                    'Đang giao' => 'badge-info',
                                    default => 'badge-warning'
                                };
                            ?>
                            <span class="status-badge <?= $badgeClass ?>"><?= $escape($status) ?></span>
                        </td>
                        <td class="admin-actions">
                            <?php if ($status === 'Chờ xác nhận'): ?>
                                <form method="post" action="admin.php?tab=orders" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                                    <input type="hidden" name="new_status" value="Đã xác nhận">
                                    <input type="hidden" name="action_order" value="1">
                                    <input type="hidden" name="csrf_token" value="<?= $escape($token) ?>">
                                    <button class="btn btn-small btn-success" type="submit">✓ Xác nhận</button>
                                </form>
                                <form method="post" action="admin.php?tab=orders" style="display:inline;" onsubmit="return confirm('Bạn chắc chắn muốn hủy đơn hàng này?');">
                                    <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                                    <input type="hidden" name="new_status" value="Đã hủy">
                                    <input type="hidden" name="action_order" value="1">
                                    <input type="hidden" name="csrf_token" value="<?= $escape($token) ?>">
                                    <button class="btn btn-small btn-danger" type="submit">✕ Hủy</button>
                                </form>
                            <?php else: ?>
                                <small style="color: #777;">Đã xử lý</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="8">Chưa có đơn hàng nào được đặt.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- ================= PHẦN 2: QUẢN LÝ SẢN PHẨM ================= -->
    <section id="section-products" class="admin-section" style="display: <?= $currentTab === 'products' ? 'block' : 'none' ?>;">
        <div class="admin-heading">
            <div>
                <h2>Danh sách sản phẩm</h2>
            </div>
            <a class="btn" href="them.php">＋ Thêm sản phẩm</a>
        </div>

        <div class="table-scroll">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hình ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows > 0): while ($row = $result->fetch_assoc()):
                    $image = ltrim(str_replace('\\', '/', (string)($row['image'] ?? '')), '/');
                    $categoryNames = [1 => 'Lập trình & IT', 2 => 'Kỹ năng sống', 3 => 'Văn học', 4 => 'Văn phòng phẩm'];
                    ?>
                    <tr>
                        <td><?= (int)$row['id'] ?></td>
                        <td><?php if ($image !== ''): ?><img class="admin-thumb" src="<?= $escape($image) ?>" alt="<?= $escape($row['name']) ?>"><?php else: ?>—<?php endif; ?></td>
                        <td><?= $escape($row['name']) ?></td>
                        <td><?= $escape($categoryNames[(int)$row['danhmuc_id']] ?? ('ID ' . (int)$row['danhmuc_id'])) ?></td>
                        <td><?= number_format((float)$row['gia_sp'], 0, ',', '.') ?> đ</td>
                        <td class="admin-actions">
                            <a class="btn btn-small" href="sua.php?id=<?= (int)$row['id'] ?>">Sửa</a>
                            <form method="post" action="xoa.php" onsubmit="return confirm('Bạn chắc chắn muốn xóa sản phẩm này?');" style="display:inline;">
                                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= $escape($token) ?>">
                                <button class="btn btn-small btn-danger" type="submit">Xóa</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="6">Chưa có sản phẩm nào.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<script>
function switchTab(tabName) {
    // Ẩn/hiện section tương ứng
    document.getElementById('section-orders').style.display = (tabName === 'orders') ? 'block' : 'none';
    document.getElementById('section-products').style.display = (tabName === 'products') ? 'block' : 'none';
    
    // Cập nhật trạng thái active cho các thẻ card phía trên
    const cards = document.querySelectorAll('.admin-card-box');
    cards.forEach((card, index) => {
        if ((tabName === 'orders' && index === 0) || (tabName === 'products' && index === 1)) {
            card.classList.add('active');
        } else {
            card.classList.remove('active');
        }
    });

    // Cập nhật URL history không cần reload trang
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.pushState({}, '', url);
}
</script>
</body>
</html>