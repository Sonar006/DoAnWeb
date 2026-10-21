<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cartCount = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $cartItem) {
        $cartCount += max(0, (int)($cartItem['quantity'] ?? 1));
    }
}
?>
<header class="main-header">
    <div class="header-container">
        <div class="logo">
            <a href="index.php" aria-label="BookStore - Trang chủ">📚 BookStore</a>
        </div>

        <form class="search-bar" action="sach.php" method="GET" role="search">
            <input type="search" name="tu_khoa" placeholder="Tìm sách theo tên..."
                   value="<?= htmlspecialchars((string)($_GET['tu_khoa'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" aria-label="Tìm kiếm">🔍</button>
        </form>

        <div class="header-actions">
            <a href="giohang.php" class="cart-btn">🛒 Giỏ hàng <span class="badge"><?= $cartCount ?></span></a>

            <?php if (isset($_SESSION['username'])): ?>
                <div class="user-logged">
                    <span>Xin chào, <strong><?= htmlspecialchars((string)$_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></strong></span>
                    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                        <a href="admin.php" class="admin-link">Quản lý sản phẩm</a>
                    <?php endif; ?>
                    <a href="dangxuat.php" class="logout-link">Đăng xuất</a>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="dangnhap.php" class="btn-login">Đăng nhập</a>
                    <a href="dangky.php" class="btn-register">Đăng ký</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <nav class="category-nav" aria-label="Danh mục chính">
        <a href="sach.php" class="category-link">📚 Sách</a>
        <a href="vanphongpham.php" class="category-link">✏️ Văn phòng phẩm</a>
    </nav>
</header>
