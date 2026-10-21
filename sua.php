<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'connect.php';
if (($_SESSION['role'] ?? '') !== 'admin') { header('Location: dangnhap.php'); exit; }
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$token = $_SESSION['csrf_token'];
$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$categories = [1 => 'Lập trình & IT', 2 => 'Kỹ năng sống', 3 => 'Văn học', 4 => 'Văn phòng phẩm'];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id && isset($_POST['id'])) $id = filter_var($_POST['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) { header('Location: admin.php?msg=notfound'); exit; }
$stmt = $conn->prepare('SELECT id, name, gia_sp, image, danhmuc_id FROM sanpham WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id); $stmt->execute(); $product = $stmt->get_result()->fetch_assoc();
if (!$product) { header('Location: admin.php?msg=notfound'); exit; }
$error = '';
$name = (string)$product['name'];
$priceText = (string)$product['gia_sp'];
$image = (string)($product['image'] ?? '');
$category = (int)($product['danhmuc_id'] ?? 1);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $priceText = trim((string)($_POST['gia_sp'] ?? ''));
    $image = trim(str_replace('\\', '/', (string)($_POST['image'] ?? '')));
    $category = (int)($_POST['danhmuc_id'] ?? 0);
    $postedToken = (string)($_POST['csrf_token'] ?? '');
    if (!hash_equals($token, $postedToken)) $error = 'Mã bảo vệ không hợp lệ. Vui lòng tải lại trang.';
    elseif ($name === '' || mb_strlen($name) > 255) $error = 'Tên sản phẩm không được để trống và tối đa 255 ký tự.';
    elseif ($priceText === '' || !is_numeric($priceText) || (float)$priceText < 0) $error = 'Giá sản phẩm phải là số không âm, ví dụ 150000.';
    elseif (!isset($categories[$category])) $error = 'Vui lòng chọn danh mục hợp lệ.';
    elseif (str_contains($image, '..') || preg_match('~^(?:[a-z][a-z0-9+.-]*:|//|/)~i', $image)) $error = 'Hình ảnh nên là đường dẫn tương đối, ví dụ images/sach.jpg.';
    else {
        $price = (float)$priceText;
        $update = $conn->prepare('UPDATE sanpham SET name = ?, gia_sp = ?, image = ?, danhmuc_id = ? WHERE id = ?');
        $update->bind_param('sdsii', $name, $price, $image, $category, $id);
        $update->execute();
        header('Location: admin.php?msg=updated'); exit;
    }
}
?>
<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Sửa sản phẩm - BookStore</title><link rel="stylesheet" href="style.css"></head><body>
<?php include 'header.php'; ?>
<main class="container admin-form-page"><a href="admin.php" class="back-link">← Quay lại quản trị</a><section class="admin-form-card"><span class="section-kicker">BOOKSTORE ADMIN</span><h1>Sửa sản phẩm #<?= (int)$id ?></h1>
<?php if ($error !== ''): ?><p class="form-message error"><?= $escape($error) ?></p><?php endif; ?>
<form method="post" action="sua.php?id=<?= (int)$id ?>" class="site-form">
<input type="hidden" name="id" value="<?= (int)$id ?>"><input type="hidden" name="csrf_token" value="<?= $escape($token) ?>">
<label for="name">Tên sản phẩm</label><input id="name" name="name" type="text" maxlength="255" required value="<?= $escape($name) ?>">
<label for="gia_sp">Giá (VNĐ)</label><input id="gia_sp" name="gia_sp" type="number" min="0" step="1" required value="<?= $escape($priceText) ?>">
<label for="image">Đường dẫn hình ảnh</label><input id="image" name="image" type="text" value="<?= $escape($image) ?>" placeholder="Ví dụ: images/sach.jpg"><small>Nhập đường dẫn tương đối từ thư mục website.</small>
<label for="danhmuc_id">Danh mục</label><select id="danhmuc_id" name="danhmuc_id" required><?php foreach ($categories as $catId => $label): ?><option value="<?= $catId ?>" <?= $category === $catId ? 'selected' : '' ?>><?= $escape($label) ?></option><?php endforeach; ?></select>
<button class="btn" type="submit">Lưu thay đổi</button>
</form></section></main></body></html>
