-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 26, 2026 lúc 10:26 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `bookstore`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitiet_donhang`
--

CREATE TABLE `chitiet_donhang` (
  `id` int(11) NOT NULL,
  `donhang_id` int(11) NOT NULL,
  `sanpham_id` int(11) NOT NULL,
  `ten_san_pham` varchar(255) NOT NULL,
  `don_gia` decimal(12,2) NOT NULL,
  `so_luong` int(11) NOT NULL,
  `thanh_tien` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chitiet_donhang`
--

INSERT INTO `chitiet_donhang` (`id`, `donhang_id`, `sanpham_id`, `ten_san_pham`, `don_gia`, `so_luong`, `thanh_tien`) VALUES
(1, 1, 22, 'Rừng Na Uy', 115000.00, 1, 115000.00),
(2, 1, 19, 'Khéo ăn nói sẽ có được thiên hạ', 85000.00, 1, 85000.00),
(3, 2, 21, 'Cây cam ngọt của tôi', 85000.00, 2, 170000.00),
(4, 3, 21, 'Cây cam ngọt của tôi', 85000.00, 1, 85000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danhmuc`
--

CREATE TABLE `danhmuc` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `danhmuc`
--

INSERT INTO `danhmuc` (`id`, `name`) VALUES
(1, 'Lập trình & IT'),
(2, 'Kỹ năng sống'),
(3, 'Văn học'),
(4, 'Văn phòng phẩm');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `donhang`
--

CREATE TABLE `donhang` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ten_nguoi_nhan` varchar(120) NOT NULL,
  `email` varchar(190) DEFAULT NULL,
  `sdt` varchar(30) NOT NULL,
  `dia_chi` varchar(255) NOT NULL,
  `ghi_chu` text DEFAULT NULL,
  `tong_tien` decimal(12,2) NOT NULL,
  `phuong_thuc_thanh_toan` varchar(100) NOT NULL,
  `trang_thai` varchar(50) NOT NULL DEFAULT 'Chờ xác nhận',
  `ngay_dat` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `donhang`
--

INSERT INTO `donhang` (`id`, `user_id`, `ten_nguoi_nhan`, `email`, `sdt`, `dia_chi`, `ghi_chu`, `tong_tien`, `phuong_thuc_thanh_toan`, `trang_thai`, `ngay_dat`) VALUES
(1, NULL, 'Nguyễn Thế Đại', 'thedaiq27@gmail.com', '0346132165', 'Hà Nội', '', 200000.00, 'Thanh toán khi nhận hàng (COD)', 'Đã xác nhận', '2026-10-21 23:45:45'),
(2, NULL, 'Nguyễn Thế Đại', 'sonathytq1507@gmail.com', '0346132165', 'Hà Nội', '', 170000.00, 'Thanh toán khi nhận hàng (COD)', 'Đã xác nhận', '2026-10-21 23:49:52'),
(3, NULL, 'Nguyễn Thế Đại', 'thedaiq27@gmail.com', '0346132165', 'Hà Nội', '', 85000.00, 'Thanh toán khi nhận hàng (COD)', 'Đã hủy', '2026-10-22 00:14:41');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sanpham`
--

CREATE TABLE `sanpham` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `danhmuc_id` int(11) NOT NULL,
  `gia_sp` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `chitiet_sp` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `sanpham`
--

INSERT INTO `sanpham` (`id`, `name`, `danhmuc_id`, `gia_sp`, `image`, `chitiet_sp`) VALUES
(1, 'Clean Code', 1, 150000.00, 'image/sp/CleanCode.png', 'Sách kinh điển về viết code sạch cho lập trình viên.'),
(2, 'Đắc Nhân Tâm', 2, 85000.00, 'image/sp/DacNhanTam.png', 'Nghệ thuật thu phục lòng người và ứng xử.'),
(3, 'Nhà Giả Kim', 3, 75000.00, 'image/sp/NhaGiaKim.png', 'Cuốn tiểu thuyết thay đổi cuộc đời hàng triệu độc giả.'),
(4, 'Bút bi Thiên Long', 4, 5000.00, 'image/sp/ButBiTL.png', 'Bút bi viết êm, mực ra đều, không bị tắc mực.'),
(5, 'Bút chì 2B', 4, 6000.00, 'image/sp/ButChi2BTL.png', 'Bút chì gỗ ngòi mềm, viết êm, dễ gọt.'),
(6, 'Hộp đựng bút vải canvas', 4, 35000.00, 'image/sp/HopButVaiCanvas.png', 'Hộp đựng bút thiết kế rộng rãi, nhiều ngăn tiện lợi.'),
(7, 'Tẩy cao su dẻo', 4, 4000.00, 'image/sp/TayDeo.png', 'Tẩy sạch, mềm dẻo, ít để lại vụn bẩn trên giấy.'),
(10, 'Head First Design Patterns', 1, 180000.00, 'image/sp/DesignPatterns.png', 'Học thiết kế hướng đối tượng qua hình ảnh trực quan.'),
(11, 'Code Complete', 1, 200000.00, 'image/sp/CodeComplete.png', 'Cẩm nang toàn diện về nghệ thuật xây dựng phần mềm.'),
(12, 'JavaScript Nâng Cao', 1, 120000.00, 'image/sp/JSNangCao.png', 'Làm chủ cú pháp và các mô hình lập trình hiện đại.'),
(13, 'Nhập môn Trí tuệ nhân tạo', 1, 160000.00, 'image/sp/AI.png', 'Khám phá nền tảng cơ bản về AI và Machine Learning.'),
(14, 'Cấu trúc dữ liệu và giải thuật', 1, 95000.00, 'image/sp/GiaiThuat.png', 'Tài liệu gối đầu giường cho mọi sinh viên IT luyện code.'),
(15, 'Thói quen nguyên tử', 2, 110000.00, 'image/sp/AtomiHabit.png', 'Thay đổi tiểu tiết nhỏ để tạo ra kết quả phi thường.'),
(16, 'Tuổi trẻ đáng giá bao nhiêu', 2, 80000.00, 'image/sp/TuoiTre.png', 'Lời tâm tình chân thành dành cho người trẻ trước ngưỡng cửa cuộc đời.'),
(17, 'Quẳng gánh lo đi và vui sống', 2, 75000.00, 'image/sp/QuangGanhLo.jpg', 'Bí quyết giải tỏa căng thẳng và tận hưởng cuộc sống trọn vẹn.'),
(18, 'Sức mạnh của tiềm thức', 2, 90000.00, 'image/sp/TiemThuc.png', 'Khai phá nguồn năng lượng vô tận ẩn giấu bên trong tiềm thức con người.'),
(19, 'Khéo ăn nói sẽ có được thiên hạ', 2, 85000.00, 'image/sp/ThienHa.png', 'Nghệ thuật giao tiếp ứng xử đỉnh cao giúp bạn thành công trong cuộc sống.'),
(20, 'Tôi thấy hoa vàng trên cỏ xanh', 3, 95000.00, 'image/sp/HoaVangTrenCoXanh.png', 'Hồi ức tuổi thơ trong trẻo, bình dị và nhiều cảm xúc của Nguyễn Nhật Ánh.'),
(21, 'Cây cam ngọt của tôi', 3, 85000.00, 'image/sp/CayCamNgot.png', 'Câu chuyện cảm động về tình cảm gia đình và sự trưởng thành đầy nước mắt.'),
(22, 'Rừng Na Uy', 3, 115000.00, 'image/sp/RungNaUy.png', 'Tác phẩm nổi tiếng của Haruki Murakami về tình yêu và tuổi trẻ.');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '123456', 'admin'),
(2, 'user', '123456', 'user'),
(3, 'user1', '123', 'user');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `chitiet_donhang`
--
ALTER TABLE `chitiet_donhang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `donhang_id` (`donhang_id`),
  ADD KEY `fk_chitiet_sanpham` (`sanpham_id`);

--
-- Chỉ mục cho bảng `danhmuc`
--
ALTER TABLE `danhmuc`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `donhang`
--
ALTER TABLE `donhang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_donhang_user_id` (`user_id`);

--
-- Chỉ mục cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sanpham_danhmuc` (`danhmuc_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `chitiet_donhang`
--
ALTER TABLE `chitiet_donhang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `danhmuc`
--
ALTER TABLE `danhmuc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `donhang`
--
ALTER TABLE `donhang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `chitiet_donhang`
--
ALTER TABLE `chitiet_donhang`
  ADD CONSTRAINT `chitiet_donhang_ibfk_1` FOREIGN KEY (`donhang_id`) REFERENCES `donhang` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_chitiet_sanpham` FOREIGN KEY (`sanpham_id`) REFERENCES `sanpham` (`id`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `donhang`
--
ALTER TABLE `donhang`
  ADD CONSTRAINT `fk_donhang_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  ADD CONSTRAINT `fk_sanpham_danhmuc` FOREIGN KEY (`danhmuc_id`) REFERENCES `danhmuc` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
