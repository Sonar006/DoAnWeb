<?php
require_once 'connect.php';

$keyword = trim((string)($_GET['tu_khoa'] ?? ''));
$sql = 'SELECT * FROM sanpham WHERE danhmuc_id = 4'; // Theo mã nguồn hiện tại: danhmuc_id=4 là văn phòng phẩm
if ($keyword !== '') {
    $sql .= ' AND name LIKE ?';
}
$sql .= ' ORDER BY id DESC';

if ($keyword !== '') {
    $stmt = $conn->prepare($sql);
    $like = '%' . $keyword . '%';
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Văn phòng phẩm - BookStore</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>
<main class="container listing-page">
    <div class="page-title">
        <span class="section-kicker">BOOKSTORE</span>
        <h1>Văn phòng phẩm</h1>
        <p>Bút viết, giấy và các dụng cụ hữu ích cho học tập, làm việc.</p>
    </div>

    <?php if ($keyword !== ''): ?>
        <p class="search-result-note">Kết quả tìm kiếm cho: <strong><?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?></strong></p>
    <?php endif; ?>

    <div class="product-grid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                $id = (int)($row['id'] ?? 0);
                $name = htmlspecialchars((string)($row['name'] ?? ''), ENT_QUOTES, 'UTF-8');
                $img = htmlspecialchars(ltrim(str_replace('\\', '/', (string)($row['image'] ?? '')), '/'), ENT_QUOTES, 'UTF-8');
                $price = number_format((float)($row['gia_sp'] ?? 0), 0, ',', '.');
                ?>
                <article class="product-card">
                    <a class="product-image-link" href="chitiet.php?id=<?= $id ?>"><img src="<?= $img ?>" alt="<?= $name ?>" loading="lazy"></a>
                    <div class="product-card-body">
                        <h3><?= $name ?></h3>
                        <div class="price"><?= $price ?> đ</div>
                        <a href="chitiet.php?id=<?= $id ?>" class="btn">Xem chi tiết</a>
                    </div>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-products">Chưa có văn phòng phẩm nào phù hợp trong danh mục này.</p>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
