<?php
require_once 'connect.php';

$allowedCategories = [1, 2, 3]; // Theo mã nguồn hiện tại: 1=IT, 2=Kỹ năng sống, 3=Văn học
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$keyword = trim((string)($_GET['tu_khoa'] ?? ''));

$where = in_array($category, $allowedCategories, true)
    ? 'danhmuc_id = ' . $category
    : 'danhmuc_id IN (1, 2, 3)';

$sql = "SELECT * FROM sanpham WHERE $where";
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

$categoryNames = [1 => 'Lập trình & IT', 2 => 'Kỹ năng sống', 3 => 'Văn học'];
$pageHeading = in_array($category, $allowedCategories, true)
    ? $categoryNames[$category]
    : 'Tất cả sách';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageHeading, ENT_QUOTES, 'UTF-8') ?> - BookStore</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>
<main class="container listing-page">
    <div class="page-title">
        <span class="section-kicker">BOOKSTORE</span>
        <h1><?= htmlspecialchars($pageHeading, ENT_QUOTES, 'UTF-8') ?></h1>
        <p>Khám phá các đầu sách phù hợp với nhu cầu học tập và giải trí.</p>
    </div>

    <nav class="filter-links" aria-label="Lọc theo thể loại sách">
        <a class="filter-link <?= $category === 0 ? 'active' : '' ?>" href="sach.php">Tất cả sách</a>
        <a class="filter-link <?= $category === 1 ? 'active' : '' ?>" href="sach.php?category=1">Lập trình &amp; IT</a>
        <a class="filter-link <?= $category === 2 ? 'active' : '' ?>" href="sach.php?category=2">Kỹ năng sống</a>
        <a class="filter-link <?= $category === 3 ? 'active' : '' ?>" href="sach.php?category=3">Văn học</a>
    </nav>

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
            <p class="empty-products">Không tìm thấy sách phù hợp. Hãy thử thể loại hoặc từ khóa khác.</p>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
