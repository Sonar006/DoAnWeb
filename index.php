<?php
require_once 'connect.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ - BookStore</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>

<main>
    <section class="hero-carousel" aria-label="Banner cửa hàng">
        <div class="hero-track">
            <article class="hero-slide hero-slide-one">
                <img class="hero-image" src="images/banner1.jpg" alt="Sách và dụng cụ học tập" onerror="this.style.display='none'">
                <div class="hero-copy">
                    <span class="hero-eyebrow">CHÀO MỪNG ĐẾN BOOKSTORE</span>
                    <h1>Góc học tập của bạn<br><em>đầy cảm hứng mỗi ngày</em></h1>
                    <p>Sách hay và văn phòng phẩm cần thiết cho học tập, làm việc.</p>
                    <a href="sach.php" class="hero-cta">KHÁM PHÁ SÁCH <span>→</span></a>
                </div>
            </article>

            <article class="hero-slide hero-slide-two">
                <img class="hero-image" src="images/banner2.jpg" alt="Bộ sưu tập sách" onerror="this.style.display='none'">
                <div class="hero-copy">
                    <span class="hero-eyebrow">MỞ TRANG SÁCH MỚI</span>
                    <h1>Mỗi cuốn sách<br><em>một hành trình mới</em></h1>
                    <p>Khám phá sách lập trình, kỹ năng sống và văn học.</p>
                    <a href="sach.php" class="hero-cta">XEM DANH MỤC SÁCH <span>→</span></a>
                </div>
            </article>

            <article class="hero-slide hero-slide-three">
                <img class="hero-image" src="images/banner3.jpg" alt="Bút và dụng cụ học tập" onerror="this.style.display='none'">
                <div class="hero-copy">
                    <span class="hero-eyebrow">ĐỒ DÙNG HỌC TẬP</span>
                    <h1>Chọn đúng dụng cụ<br><em>học tập hiệu quả hơn</em></h1>
                    <p>Bút viết, giấy và nhiều vật dụng cho bàn học của bạn.</p>
                    <a href="vanphongpham.php" class="hero-cta">XEM VĂN PHÒNG PHẨM <span>→</span></a>
                </div>
            </article>

            <article class="hero-slide hero-slide-four">
                <img class="hero-image" src="images/banner4.jpg" alt="Văn phòng phẩm nhiều màu sắc" onerror="this.style.display='none'">
                <div class="hero-copy">
                    <span class="hero-eyebrow">SẮP XẾP GỌN GÀNG</span>
                    <h1>Hoàn thiện góc học tập<br><em>theo phong cách của bạn</em></h1>
                    <p>Chọn những món đồ hữu ích cho học tập và công việc.</p>
                    <a href="vanphongpham.php" class="hero-cta">MUA VĂN PHÒNG PHẨM <span>→</span></a>
                </div>
            </article>
        </div>

        <button class="hero-arrow hero-prev" type="button" aria-label="Banner trước">‹</button>
        <button class="hero-arrow hero-next" type="button" aria-label="Banner tiếp theo">›</button>
        <div class="hero-dots" aria-label="Chọn banner">
            <button class="hero-dot is-active" type="button" aria-label="Banner 1" aria-current="true"></button>
            <button class="hero-dot" type="button" aria-label="Banner 2" aria-current="false"></button>
            <button class="hero-dot" type="button" aria-label="Banner 3" aria-current="false"></button>
            <button class="hero-dot" type="button" aria-label="Banner 4" aria-current="false"></button>
        </div>
    </section>

    <section class="container featured-section" id="san-pham-noi-bat">
        <div class="section-heading">
            <div>
                <span class="section-kicker">GỢI Ý CHO BẠN</span>
                <h2>Sản phẩm mới nhất</h2>
            </div>
            <div class="section-links">
                <a href="sach.php">Xem sách →</a>
                <a href="vanphongpham.php">Xem văn phòng phẩm →</a>
            </div>
        </div>

        <div class="product-grid">
            <?php
            $sql = "SELECT * FROM sanpham ORDER BY id DESC LIMIT 4";
            $result = mysqli_query($conn, $sql);

            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $id = (int)($row['id'] ?? 0);
                    $name = htmlspecialchars((string)($row['name'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $imgRaw = str_replace('\\', '/', (string)($row['image'] ?? ''));
                    $img = htmlspecialchars(ltrim($imgRaw, '/'), ENT_QUOTES, 'UTF-8');
                    $price = number_format((float)($row['gia_sp'] ?? 0), 0, ',', '.');

                    echo '<article class="product-card">';
                    echo '<a class="product-image-link" href="chitiet.php?id=' . $id . '">';
                    echo '<img src="' . $img . '" alt="' . $name . '" loading="lazy">';
                    echo '</a>';
                    echo '<div class="product-card-body">';
                    echo '<h3>' . $name . '</h3>';
                    echo '<div class="price">' . $price . ' đ</div>';
                    echo '<a href="chitiet.php?id=' . $id . '" class="btn">Xem chi tiết</a>';
                    echo '</div></article>';
                }
            } else {
                echo '<p class="empty-products">Chưa có sản phẩm nào trong cơ sở dữ liệu.</p>';
            }
            ?>
        </div>
    </section>
</main>

<script>
(() => {
    const carousel = document.querySelector('.hero-carousel');
    if (!carousel) return;

    const track = carousel.querySelector('.hero-track');
    const slides = Array.from(carousel.querySelectorAll('.hero-slide'));
    const dots = Array.from(carousel.querySelectorAll('.hero-dot'));
    const prev = carousel.querySelector('.hero-prev');
    const next = carousel.querySelector('.hero-next');
    let activeIndex = 0;
    let timer;

    function showSlide(index) {
        activeIndex = (index + slides.length) % slides.length;
        track.style.transform = `translateX(-${activeIndex * 100}%)`;
        dots.forEach((dot, i) => {
            dot.classList.toggle('is-active', i === activeIndex);
            dot.setAttribute('aria-current', i === activeIndex ? 'true' : 'false');
        });
    }

    function stopAutoPlay() {
        if (timer) window.clearInterval(timer);
        timer = null;
    }

    function startAutoPlay() {
        stopAutoPlay();
        timer = window.setInterval(() => showSlide(activeIndex + 1), 5000);
    }

    prev.addEventListener('click', () => { showSlide(activeIndex - 1); startAutoPlay(); });
    next.addEventListener('click', () => { showSlide(activeIndex + 1); startAutoPlay(); });
    dots.forEach((dot, i) => dot.addEventListener('click', () => { showSlide(i); startAutoPlay(); }));
    carousel.addEventListener('mouseenter', stopAutoPlay);
    carousel.addEventListener('mouseleave', startAutoPlay);
    startAutoPlay();
})();
</script>
</body>
</html>
