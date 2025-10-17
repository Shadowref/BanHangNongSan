-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 17, 2025 lúc 07:32 AM
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
-- Cơ sở dữ liệu: `banhangonline`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danhgia`
--

CREATE TABLE `danhgia` (
  `id` int(11) NOT NULL,
  `id_sanpham` int(11) NOT NULL,
  `id_nguoidung` int(11) DEFAULT NULL,
  `user_name` varchar(100) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `ngaydat` datetime DEFAULT current_timestamp(),
  `anh_review` varchar(255) DEFAULT NULL,
  `video_review` varchar(255) DEFAULT NULL,
  `trangthai` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = Hiện, 0 = Ẩn',
  `likes` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `danhgia`
--

INSERT INTO `danhgia` (`id`, `id_sanpham`, `id_nguoidung`, `user_name`, `rating`, `comment`, `ngaydat`, `anh_review`, `video_review`, `trangthai`, `likes`) VALUES
(38, 40, 1, 'SUONGNIE22', 5, 'Khoai này ngon lắm mọi người à, ăn rất béo và ngon luôn ', '2025-09-08 14:06:11', 'review_img_1757315171.jpg', NULL, 1, 0),
(40, 34, 3, 'LEVIETDUNG', 5, 'ăn với gà ngon lắm lun á', '2025-09-08 22:20:23', 'review_img_1758122256.jpg', NULL, 1, 3),
(51, 38, 4, 'jackerEvalden', 2, 'Ko ngon lắm ', '2025-09-09 00:09:35', 'review_img_1757351375.jpg', NULL, 0, 0),
(55, 38, 4, 'jackerEvalden', 5, 'dssdsd', '2025-09-09 00:15:49', 'review_img_1757351749.jpg', NULL, 0, 0),
(58, 35, 1, 'SUONGNIE22', 5, 'Quá đẹp trái hihi', '2025-09-09 01:00:15', 'review_img_1757354415.jpeg', NULL, 0, 26),
(66, 35, 3, 'LEVIETDUNG', 5, 'Ngon', '2025-09-10 00:16:35', NULL, NULL, 0, 1),
(67, 40, 3, 'LEVIETDUNG', 1, 'Khoai ăn vưa ngon vừa béo', '2025-09-10 00:36:58', NULL, NULL, 0, 1),
(69, 37, 3, 'LEVIETDUNG', 1, 'sđs', '2025-09-10 00:48:56', NULL, NULL, 0, 0),
(71, 35, 1, 'SUONGNIE22', 5, 'dfsfsf', '2025-09-10 00:57:40', NULL, NULL, 0, 0),
(72, 35, 1, 'SUONGNIE22', 5, 'dsds', '2025-09-10 01:00:43', NULL, NULL, 0, 0),
(74, 39, 1, 'SUONGNIE22', 5, 'Ngon lắm', '2025-09-10 01:55:37', NULL, NULL, 0, 0),
(80, 53, 1, 'SUONGNIE22', 5, 'Ngon quá ', '2025-09-13 15:22:51', NULL, NULL, 0, 1),
(82, 34, 1, 'SUONGNIE22', 5, 'Mời mọi người thưởng thức món cải mới nhà em ', '2025-09-13 16:16:24', NULL, NULL, 0, 10),
(83, 34, 10, 'SangMLO', 5, 'Em ăn ngon quá chừng. Nay em mua gần 10 cân về ship cho mẹ ăn mà mẹ khen quá à', '2025-09-13 17:04:44', NULL, NULL, 0, 17),
(84, 51, 1, 'SUONGNIE', 4, 'Trái dưa hấu thơm ngon ngọt nước ăn phát là nhớ mãi ', '2025-09-15 15:47:48', NULL, NULL, 0, 0),
(86, 52, 1, 'SUONGNIE', 5, 'Bưởi đỏ vưa ngon vừa xinh ngại gì không mua thử ăn một miếng nhớ mãi về sau', '2025-09-17 14:06:38', NULL, NULL, 0, 6),
(87, 34, 1, 'SUONGNIE', 5, 'ngon', '2025-09-21 06:57:04', NULL, NULL, 0, 2),
(88, 57, 1, 'SUONGNIE', 5, 'Sau Rieng ngon qua ', '2025-09-22 13:02:40', NULL, NULL, 0, 4),
(89, 53, 10, 'SANGMLO', 5, 'ăn ngon lắm lun', '2025-09-27 16:23:11', NULL, NULL, 0, 0),
(91, 52, 1, 'SUONGNIE', 5, 'Mua 2 tặng 1 mn ơi', '2025-09-27 21:51:32', NULL, NULL, 0, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `diachi`
--

CREATE TABLE `diachi` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `tennguoinhan` varchar(100) NOT NULL,
  `sdt` varchar(20) NOT NULL,
  `diachi` varchar(255) NOT NULL,
  `macdinh` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `diachi`
--

INSERT INTO `diachi` (`id`, `username`, `tennguoinhan`, `sdt`, `diachi`, `macdinh`) VALUES
(1, 'LEVIETDUNG', 'Sương', '0325581015', 'Đắk Lắk', 1),
(2, 'halflife', 'le viet dung', '0787530310', '38 nai hien', 1),
(3, 'SUONGNIE', 'Sương', '0325581015', 'Đắk Lắk', 0),
(4, 'jackerEvalden', 'đ', '0231548755', 'ad', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `donhang`
--

CREATE TABLE `donhang` (
  `id` int(11) NOT NULL,
  `id_nguoidung` int(11) NOT NULL,
  `id_admin` int(11) DEFAULT NULL,
  `id_sanpham` int(11) NOT NULL,
  `tensanpham` varchar(255) DEFAULT NULL,
  `hinhanh` varchar(255) DEFAULT NULL,
  `loai_sanpham` varchar(255) DEFAULT NULL,
  `thuonghieu` varchar(255) DEFAULT NULL,
  `soluong` int(11) NOT NULL,
  `giaban` decimal(15,0) NOT NULL,
  `ngaydat` datetime DEFAULT current_timestamp(),
  `thanhtien` decimal(15,2) NOT NULL DEFAULT 0.00,
  `trangthai` enum('dang_cho','dang_giao','hoan_tat','da_huy') NOT NULL DEFAULT 'dang_cho',
  `diem_sudung` int(11) NOT NULL,
  `phuongthucthanhtoan` varchar(50) NOT NULL DEFAULT 'cod'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `donhang`
--

INSERT INTO `donhang` (`id`, `id_nguoidung`, `id_admin`, `id_sanpham`, `tensanpham`, `hinhanh`, `loai_sanpham`, `thuonghieu`, `soluong`, `giaban`, `ngaydat`, `thanhtien`, `trangthai`, `diem_sudung`, `phuongthucthanhtoan`) VALUES
(509, 1, NULL, 52, 'Bưởi da xanh', 'buoi.jpg', 'Nông sản sạch', 'Thế giới nông sản', 1, 95000, '2025-09-01 18:26:42', 95000.00, 'hoan_tat', 0, 'cod'),
(510, 1, NULL, 38, 'Chả cá thác lác', 'chaca.jpg', 'Thực phẩm đã chế biến', 'Rau Bác Tôm', 1, 95000, '2025-10-02 18:31:02', 95000.00, 'hoan_tat', 0, 'cod'),
(511, 1, NULL, 47, 'Cam sành Hà Giang', 'cam.jpg', 'Rau củ sạch', '3SachFood', 1, 45000, '2025-10-02 18:32:48', 45000.00, 'hoan_tat', 0, 'cod'),
(512, 1, 1, 39, 'Nem chua Thanh Hóa', 'nemchua.jpg', 'Thực phẩm đã chế biến', 'Việt Nam ', 1, 70000, '2025-10-02 18:48:08', 70000.00, 'hoan_tat', 0, 'cod'),
(513, 1, 1, 38, 'Chả cá thác lác', 'chaca.jpg', 'Thực phẩm đã chế biến', 'Rau Bác Tôm', 1, 95000, '2025-10-02 18:49:37', 95000.00, 'hoan_tat', 0, 'cod'),
(514, 1, NULL, 36, 'Gạo ST25', 'gao_st25.jpg', 'Gạo các loại', 'Organica', 1, 28000, '2025-10-02 18:50:31', 28000.00, 'hoan_tat', 0, 'cod'),
(515, 1, 1, 51, 'Dưa hấu không hạt', 'duahau.jpg', 'Nông sản sạch', 'Việt Nam ', 1, 40000, '2025-09-01 18:56:39', 40000.00, 'hoan_tat', 0, 'cod'),
(516, 1, 10, 36, 'Gạo ST25', 'gao_st25.jpg', 'Gạo các loại', 'Organica', 1, 28000, '2025-06-04 19:24:18', 28000.00, 'hoan_tat', 0, 'cod'),
(517, 1, 1, 50, 'Ổi lê Đài Loan', 'oi.jpg', 'Rau củ sạch', 'Rau Bác Tôm', 1, 38000, '2025-10-02 19:33:53', 38000.00, 'hoan_tat', 0, 'cod'),
(518, 10, 1, 57, 'Sầu Riêng Đắk Lắk', '1758094495_saurieng.jpg', 'Nông sản sạch', 'Thế giới nông sản', 1, 120000, '2025-10-02 21:04:03', 120000.00, 'hoan_tat', 0, 'cod'),
(519, 1, NULL, 53, 'Thanh long ruột đỏ ngon ngon ', 'thanhlong.jpg', 'Nông sản sạch', '3SachFood', 1, 20000, '2025-10-02 21:14:24', 20000.00, 'dang_giao', 0, 'cod'),
(520, 1, 1, 53, 'Thanh long ruột đỏ ngon ngon ', 'thanhlong.jpg', 'Nông sản sạch', '3SachFood', 1, 20000, '2025-10-07 13:27:37', 0.00, 'hoan_tat', 0, 'cod'),
(521, 1, NULL, 57, 'Sầu Riêng Đắk Lắk', '1758094495_saurieng.jpg', 'Nông sản sạch', 'Thế giới nông sản', 1, 120000, '2025-10-10 20:53:53', 120000.00, 'hoan_tat', 0, 'cod'),
(522, 1, 1, 53, 'Thanh long ruột đỏ ngon ngon ', 'thanhlong.jpg', 'Nông sản sạch', '3SachFood', 1, 20000, '2025-10-10 21:04:33', 20000.00, 'hoan_tat', 0, 'cod'),
(523, 1, 1, 36, 'Gạo ST25', 'gao_st25.jpg', 'Gạo các loại', 'Organica', 1, 28000, '2025-10-10 22:34:40', 28000.00, 'hoan_tat', 0, 'cod');

--
-- Bẫy `donhang`
--
DELIMITER $$
CREATE TRIGGER `cong_diem_khi_donhang_hoanthanh` AFTER UPDATE ON `donhang` FOR EACH ROW BEGIN
    DECLARE diemcong INT;

    -- Kiểm tra đơn hàng mới được cập nhật sang trạng thái 'daxuly'
    IF NEW.trangthai = 'daxuly' AND OLD.trangthai <> 'daxuly' THEN
        SET diemcong = FLOOR(NEW.thanhtien / 10000);

        -- Cộng điểm cho người dùng
        UPDATE taikhoan
        SET diem = diem + diemcong
        WHERE id = NEW.id_nguoidung;

        -- Lưu lịch sử điểm
        INSERT INTO lichsu_diem (id_nguoidung, diem, loai, mota)
        VALUES (NEW.id_nguoidung, diemcong, 'cong', CONCAT('Cộng điểm từ đơn hàng #', NEW.id));
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tru_diem_khi_dathang` BEFORE INSERT ON `donhang` FOR EACH ROW BEGIN
    IF NEW.diem_sudung > 0 THEN
        UPDATE taikhoan
        SET diem = diem - NEW.diem_sudung
        WHERE id = NEW.id_nguoidung;

        INSERT INTO lichsu_diem (id_nguoidung, diem, loai, mota)
        VALUES (NEW.id_nguoidung, NEW.diem_sudung, 'tru', CONCAT('Dùng điểm cho đơn hàng #', NEW.id));
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `giohang`
--

CREATE TABLE `giohang` (
  `id` int(11) NOT NULL,
  `taikhoan_id` int(11) NOT NULL,
  `sanpham_id` int(11) NOT NULL,
  `soluong` int(11) NOT NULL DEFAULT 1,
  `gia` float NOT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khuyenmai`
--

CREATE TABLE `khuyenmai` (
  `id` int(11) NOT NULL,
  `sanpham_id` int(11) NOT NULL,
  `giakhuyenmai` decimal(15,2) DEFAULT NULL,
  `giamgia` int(11) DEFAULT NULL,
  `ngay_bat_dau` date DEFAULT NULL,
  `ngay_ket_thuc` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `khuyenmai`
--

INSERT INTO `khuyenmai` (`id`, `sanpham_id`, `giakhuyenmai`, `giamgia`, `ngay_bat_dau`, `ngay_ket_thuc`) VALUES
(27, 34, 40000.00, 11, '2025-08-28', '2025-09-30'),
(28, 36, 26000.00, 7, '2025-08-28', '2025-09-30'),
(29, 38, 90000.00, 5, '2025-08-28', '2025-09-20'),
(30, 40, 50000.00, 9, '2025-08-28', '2025-10-01'),
(31, 44, 110000.00, 10, '2025-08-28', '2025-09-30'),
(32, 50, 13000.00, 70, '2025-09-07', '2025-09-22'),
(33, 41, 26000.00, 19, '2025-09-17', '2025-09-18'),
(34, 57, 50000.00, 50, '2025-10-02', '2025-10-31');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lichsugiaodich`
--

CREATE TABLE `lichsugiaodich` (
  `id` int(11) NOT NULL,
  `id_donhang` int(11) NOT NULL,
  `id_nguoidung` int(11) NOT NULL,
  `id_sanpham` int(11) NOT NULL,
  `tensanpham` varchar(255) NOT NULL,
  `hinhanh` varchar(255) DEFAULT NULL,
  `loai_sanpham` varchar(255) DEFAULT NULL,
  `thuonghieu` varchar(255) DEFAULT NULL,
  `soluong` int(11) NOT NULL,
  `giaban` decimal(15,0) NOT NULL,
  `tongtien` decimal(15,0) NOT NULL,
  `ngaygiaodich` datetime DEFAULT current_timestamp(),
  `trangthai` enum('dang_cho','dang_giao','hoan_tat') NOT NULL DEFAULT 'dang_cho',
  `ngaydat` datetime DEFAULT NULL,
  `diem_sudung` int(11) NOT NULL,
  `anh_nhanhang` varchar(255) DEFAULT NULL,
  `phuongthucthanhtoan` varchar(50) NOT NULL DEFAULT 'cod'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `lichsugiaodich`
--

INSERT INTO `lichsugiaodich` (`id`, `id_donhang`, `id_nguoidung`, `id_sanpham`, `tensanpham`, `hinhanh`, `loai_sanpham`, `thuonghieu`, `soluong`, `giaban`, `tongtien`, `ngaygiaodich`, `trangthai`, `ngaydat`, `diem_sudung`, `anh_nhanhang`, `phuongthucthanhtoan`) VALUES
(220, 509, 1, 52, 'Bưởi da xanh', 'buoi.jpg', 'Nông sản sạch', 'Thế giới nông sản', 1, 95000, 95000, '2025-10-02 18:26:57', 'hoan_tat', '2025-10-02 18:26:42', 0, 'nhanhang_509_1759404417.jpg', 'cod'),
(221, 510, 1, 38, 'Chả cá thác lác', 'chaca.jpg', 'Thực phẩm đã chế biến', 'Rau Bác Tôm', 1, 95000, 95000, '2025-10-02 18:31:20', 'hoan_tat', '2025-10-02 18:31:02', 0, 'nhanhang_510_1759404680.jpg', 'cod'),
(222, 511, 1, 47, 'Cam sành Hà Giang', 'cam.jpg', 'Rau củ sạch', '3SachFood', 1, 45000, 45000, '2025-10-02 18:33:06', 'hoan_tat', '2025-10-02 18:32:48', 0, 'nhanhang_511_1759404786.jpg', 'cod'),
(223, 514, 1, 36, 'Gạo ST25', 'gao_st25.jpg', 'Gạo các loại', 'Organica', 1, 28000, 28000, '2025-10-02 18:50:38', 'hoan_tat', '2025-10-02 18:50:31', 0, 'nhanhang_514_1759405838.jpg', 'cod'),
(224, 515, 1, 51, 'Dưa hấu không hạt', 'duahau.jpg', 'Nông sản sạch', 'Việt Nam ', 1, 40000, 40000, '2025-10-02 18:56:51', 'hoan_tat', '2025-10-02 18:56:39', 0, 'nhanhang_515_1759406211.jpg', 'cod'),
(225, 516, 1, 36, 'Gạo ST25', 'gao_st25.jpg', 'Gạo các loại', 'Organica', 1, 28000, 28000, '2025-10-02 19:24:29', 'hoan_tat', '2025-10-02 19:24:18', 0, 'nhanhang_516_1759407869.jpg', 'cod'),
(226, 517, 1, 50, 'Ổi lê Đài Loan', 'oi.jpg', 'Rau củ sạch', 'Rau Bác Tôm', 1, 38000, 38000, '2025-08-03 19:34:14', 'hoan_tat', '2025-08-13 19:33:53', 0, 'nhanhang_517_1759408454.jpg', 'cod'),
(227, 518, 10, 57, 'Sầu Riêng Đắk Lắk', '1758094495_saurieng.jpg', 'Nông sản sạch', 'Thế giới nông sản', 1, 120000, 120000, '2025-10-02 21:04:16', 'hoan_tat', '2025-10-02 21:04:03', 0, 'nhanhang_518_1759413856.jpg', 'cod'),
(228, 520, 1, 53, 'Thanh long ruột đỏ ngon ngon ', 'thanhlong.jpg', 'Nông sản sạch', '3SachFood', 1, 20000, 0, '2025-10-07 13:28:02', 'hoan_tat', '2025-10-07 13:27:37', 20000, 'nhanhang_520_1759818482.jpg', 'cod'),
(229, 521, 1, 57, 'Sầu Riêng Đắk Lắk', '1758094495_saurieng.jpg', 'Nông sản sạch', 'Thế giới nông sản', 1, 120000, 120000, '2025-10-10 21:02:59', 'hoan_tat', '2025-10-10 20:53:53', 0, 'nhanhang_521_1760104979.jpg', 'cod'),
(230, 522, 1, 53, 'Thanh long ruột đỏ ngon ngon ', 'thanhlong.jpg', 'Nông sản sạch', '3SachFood', 1, 20000, 20000, '2025-10-10 21:04:42', 'hoan_tat', '2025-10-10 21:04:33', 0, 'nhanhang_522_1760105082.jpg', 'cod'),
(231, 523, 1, 36, 'Gạo ST25', 'gao_st25.jpg', 'Gạo các loại', 'Organica', 1, 28000, 28000, '2025-10-10 22:37:01', 'hoan_tat', '2025-10-10 22:34:40', 0, 'nhanhang_523_1760110621.jpg', 'cod');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lichsu_diem`
--

CREATE TABLE `lichsu_diem` (
  `id` int(11) NOT NULL,
  `taikhoan_id` int(11) DEFAULT NULL,
  `diem` int(11) DEFAULT NULL,
  `loai` enum('cong','tieu') NOT NULL,
  `id_donhang` int(11) DEFAULT NULL,
  `mota` varchar(255) DEFAULT NULL,
  `ngay` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `lichsu_diem`
--

INSERT INTO `lichsu_diem` (`id`, `taikhoan_id`, `diem`, `loai`, `id_donhang`, `mota`, `ngay`) VALUES
(172, 1, 40000, 'tieu', NULL, '0', '2025-09-15 10:39:16'),
(173, 1, 14000, 'tieu', NULL, '0', '2025-09-15 10:43:09'),
(174, 1, 10000, 'tieu', NULL, '0', '2025-09-15 10:57:15'),
(175, 1, 4999, 'tieu', NULL, '0', '2025-09-15 11:02:08'),
(176, 1, 4999, 'tieu', NULL, '0', '2025-09-15 11:08:33'),
(177, 1, 5000, 'tieu', NULL, '0', '2025-09-15 11:12:55'),
(178, 1, 5000, 'tieu', NULL, '0', '2025-09-15 11:16:14'),
(179, 1, 5000, 'tieu', NULL, '0', '2025-09-15 11:16:51'),
(180, 1, 5000, 'tieu', NULL, '0', '2025-09-15 11:25:17'),
(181, 1, 5000, 'tieu', NULL, '0', '2025-09-15 13:06:21'),
(182, 1, 4999, 'tieu', NULL, '0', '2025-09-15 13:12:40'),
(183, 1, 5000, 'tieu', NULL, '0', '2025-09-15 13:13:33'),
(184, 10, 249, 'tieu', NULL, '0', '2025-09-17 14:15:51'),
(185, 1, 29, 'tieu', NULL, '0', '2025-09-21 06:07:24'),
(186, 1, 699, 'tieu', NULL, '0', '2025-09-21 07:08:06'),
(187, 1, 200000, 'tieu', NULL, '0', '2025-09-22 13:08:55'),
(188, 1, 200, 'tieu', NULL, '0', '2025-10-02 18:00:49'),
(189, 1, 20000, 'tieu', 520, '0', '2025-10-07 13:27:37');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lienhe`
--

CREATE TABLE `lienhe` (
  `id` int(11) NOT NULL,
  `hoten` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `noidung` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `lienhe`
--

INSERT INTO `lienhe` (`id`, `hoten`, `email`, `noidung`, `created_at`) VALUES
(2, 'halflife', 'htcatadc@gmail.com', 'toi muon trang web duoc mo rong san pham hon', '2025-08-27 12:41:22'),
(5, 'Suongk5', 'suongnie2k5@gmail.com', 'Hi', '2025-09-15 14:36:13'),
(6, 'Suongk5', 'suongnie2k5@gmail.com', 'Hi', '2025-09-15 14:39:08'),
(7, 'Ông Hoàng Nhạc pop', 'htcatadc@gmail.com', 'Cậu có muốn bán trang wed cho chúng tôi ko', '2025-09-15 14:40:10'),
(8, 'Suongk5', 'suongnie2k5@gmail.com', 'Tôi thích wed của bạn bán cho tôi đi', '2025-09-15 14:47:29'),
(9, 'Suongk5', 'htcatadc@gmail.com', 'Hello', '2025-09-15 14:50:38'),
(10, 'sang', 'htcatadc@gmail.com', 'hello fen', '2025-09-15 14:52:32'),
(11, 'Suongk5', 'htcatadc@gmail.com', 'Hello', '2025-09-15 14:55:14'),
(12, 'Suongk5', 'suongnie2k5@gmail.com', 'Báo Trung Quốc nhận định về cuộc biểu tình lên tới 150.000 người ở Anh', '2025-09-15 15:06:08'),
(14, 'Suongk5', 'suongnie2k5@gmail.com', 'sdsds', '2025-09-15 15:14:45'),
(15, 'jacker', 'htcatadc@gmail.com', ' trả 2 tỷ điểm tôi bạn ơi', '2025-09-15 15:20:25'),
(16, 'Thay Tha', 'dinhtha@yahoo.com', 'Thay muon mua trang cuar em', '2025-09-22 13:05:40'),
(17, 'Suongk5', 'suongnie2k5@gmail.com', 'sdad', '2025-10-01 22:45:49'),
(18, 'ssdsds', 'suongnie2k5@gmail.com', 'dfsfsd', '2025-10-07 13:36:16'),
(19, 'YSuongNie', 'suongnie2k5@gmail.com', 'Hello admin', '2025-10-10 20:10:23');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `id_item` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `likes`
--

INSERT INTO `likes` (`id`, `id_item`, `user_id`, `created_at`) VALUES
(15, 34, 3, '2025-09-17 14:37:33'),
(16, 34, 10, '2025-09-17 14:37:44');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `loaisanpham`
--

CREATE TABLE `loaisanpham` (
  `id` int(11) NOT NULL,
  `tenloai` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `loaisanpham`
--

INSERT INTO `loaisanpham` (`id`, `tenloai`) VALUES
(1, 'Rau củ sạch'),
(2, 'Gạo các loại'),
(3, 'Thực phẩm đã chế biến'),
(4, 'Nông sản sạch'),
(5, 'Bún, miến'),
(6, 'Đặc sản đồ khô');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phanhoi_review`
--

CREATE TABLE `phanhoi_review` (
  `id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `id_nguoidung` int(11) DEFAULT NULL,
  `user_name` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `ngaydat` datetime DEFAULT current_timestamp(),
  `reply_to_user_id` int(11) DEFAULT NULL,
  `reply_to_user_name` varchar(255) DEFAULT NULL,
  `likes` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `phanhoi_review`
--

INSERT INTO `phanhoi_review` (`id`, `review_id`, `id_nguoidung`, `user_name`, `comment`, `ngaydat`, `reply_to_user_id`, `reply_to_user_name`, `likes`) VALUES
(1, 55, 1, 'SUONGNIE22', 'xạo quá ', '2025-09-09 00:17:28', NULL, NULL, 0),
(9, 40, 4, 'jackerEvalden', 'Thiệc hả bạn ơi hay để tui mua thử', '2025-09-17 15:11:41', NULL, NULL, 1),
(10, 40, 1, 'SUONGNIE22', 'Ngon lắm á', '2025-09-09 00:45:02', NULL, NULL, 3),
(37, 67, 1, 'SUONGNIE22', 'quá ngon luôn hị hị', '2025-09-10 00:38:59', NULL, NULL, 0),
(40, 72, 3, 'LEVIETDUNG', 'sdsds', '2025-09-10 01:04:34', NULL, NULL, 0),
(45, 72, 1, 'SUONGNIE22', 'ds', '2025-09-10 01:24:13', NULL, NULL, 0),
(46, 74, 3, 'LEVIETDUNG', 'Thiệc hả bạn ', '2025-09-10 01:56:20', NULL, NULL, 0),
(47, 74, 1, 'SUONGNIE22', 'ừ', '2025-09-10 01:56:42', NULL, NULL, 0),
(48, 40, 1, 'SUONGNIE22', 'Thử đi bạn, cải này dính lắm ', '2025-09-10 10:05:49', NULL, NULL, 2),
(58, 84, 3, 'LEVIETDUNG', 'Mình mua ăn thử rồi nó rất ngon ạ ', '2025-09-15 15:52:07', NULL, NULL, 0),
(59, 84, 1, 'SUONGNIE', 'hh Cảm ơn bạn ', '2025-09-15 15:55:35', NULL, NULL, 0),
(62, 83, 1, 'SUONGNIE', 'Cảm ơn em đã ủng hộ Thanks', '2025-09-17 22:16:28', NULL, NULL, 6),
(63, 40, 3, 'LEVIETDUNG', 'Haha ', '2025-09-17 22:18:49', NULL, NULL, 0),
(64, 83, 1, 'SUONGNIE', 'Mong em sẽ giới thiệu nhiều sản phẩm hơn từ của anh cho mọi người được biết nhiều hơn nha', '2025-09-17 22:26:34', NULL, NULL, 1),
(65, 40, 1, 'SUONGNIE', 'hay quá', '2025-09-21 06:22:55', NULL, NULL, 1),
(67, 87, 10, 'SANGMLO', 'vui quá ha', '2025-09-21 07:29:23', NULL, NULL, 0),
(68, 87, 1, 'SUONGNIE', 'Vui qua', '2025-09-22 13:14:42', NULL, NULL, 0),
(69, 80, 10, 'SANGMLO', 'em muốn ăn nữa\r\n', '2025-09-27 16:21:55', NULL, NULL, 0),
(70, 91, 3, 'LEVIETDUNG', 'Chắc để em mua ăn thử', '2025-09-28 00:07:55', NULL, NULL, 0),
(71, 88, 3, 'LEVIETDUNG', 'Thiệc ko bạn ', '2025-09-28 20:07:18', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sanpham`
--

CREATE TABLE `sanpham` (
  `id` int(11) NOT NULL,
  `tensp` varchar(255) NOT NULL,
  `gia` decimal(15,0) NOT NULL,
  `giakhuyenmai` decimal(15,2) DEFAULT NULL,
  `giamgia` int(11) DEFAULT NULL,
  `hinhanh` varchar(255) NOT NULL,
  `mota` text DEFAULT NULL,
  `soluong` int(11) DEFAULT 0,
  `id_loai` int(11) DEFAULT NULL,
  `id_thuonghieu` int(11) DEFAULT NULL,
  `likes` int(11) DEFAULT 0,
  `ngaythem` datetime NOT NULL DEFAULT current_timestamp(),
  `trangthai` enum('an','hien') NOT NULL DEFAULT 'hien'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `sanpham`
--

INSERT INTO `sanpham` (`id`, `tensp`, `gia`, `giakhuyenmai`, `giamgia`, `hinhanh`, `mota`, `soluong`, `id_loai`, `id_thuonghieu`, `likes`, `ngaythem`, `trangthai`) VALUES
(34, 'Cải bó xôi hữu cơ', 45000, 40000.00, 11, 'caiboi.jpg', 'Cải bó xôi hữu cơ được trồng theo phương pháp không hóa chất, đảm bảo sạch và an toàn cho sức khỏe. Lá rau xanh mướt, vị ngọt thanh mát, giòn mềm. Đây là loại rau giàu sắt, canxi, vitamin A, C, K, cùng các chất chống oxy hóa. Cải bó xôi đặc biệt tốt cho máu, xương khớp, mắt và da. Có thể chế biến thành salad, xào, nấu canh, luộc hoặc làm sinh tố xanh.', 154, 1, 1, 16, '2025-09-17 22:56:08', 'hien'),
(35, 'Cà rốt Đà Lạt', 38000, 35000.00, 8, 'carot.jpg', 'Cà rốt Đà Lạt nổi tiếng nhờ khí hậu mát mẻ quanh năm, cho củ to đều, vỏ nhẵn, màu cam tươi đẹp mắt. Ruột cà rốt giòn ngọt, nhiều nước, giàu beta-carotene, vitamin A, C, K và kali. Ăn cà rốt thường xuyên giúp sáng mắt, đẹp da, tăng cường miễn dịch. Cà rốt có thể ăn sống, làm nước ép, salad, nấu canh, xào, hầm hoặc làm bánh ngọt.', 106, 1, 2, 1, '2025-09-17 22:56:08', 'hien'),
(36, 'Gạo ST25', 28000, 26000.00, 7, 'gao_st25.jpg', 'Được mệnh danh là “gạo ngon nhất thế giới” năm 2019, gạo ST25 có hạt dài, trắng trong, khi nấu cho cơm dẻo, thơm ngọt, để nguội vẫn ngon. Giống gạo này được lai tạo tại Sóc Trăng, có hương thơm đặc trưng của lá dứa, cơm chín dẻo mềm nhưng không dính. Ngoài ra, gạo ST25 chứa hàm lượng dinh dưỡng cao, nhiều protein, vitamin và khoáng chất. Rất phù hợp cho bữa cơm gia đình, từ cơm trắng, cơm chiên, cơm tấm đến sushi.', 81, 2, 3, 2, '2025-09-17 22:56:08', 'hien'),
(37, 'Gạo lứt huyết rồng', 40000, 37000.00, 8, 'gao_lut.jpg', 'Loại gạo đặc biệt có màu đỏ sẫm tự nhiên, nhờ giàu anthocyanin – chất chống oxy hóa mạnh. Gạo lứt huyết rồng giữ nguyên lớp cám và mầm gạo, giàu chất xơ, vitamin nhóm B, sắt, kẽm và magie. Ăn gạo lứt huyết rồng thường xuyên giúp thanh lọc cơ thể, kiểm soát cân nặng, ổn định đường huyết và tốt cho người tiểu đường. Khi nấu, hạt gạo dẻo vừa, có vị bùi và mùi thơm nhẹ. Có thể dùng để nấu cơm, cháo, làm sữa gạo lứt hoặc bún gạo lứt.', 135, 2, 4, 0, '2025-09-17 22:56:08', 'hien'),
(38, 'Chả cá thác lác', 95000, 90000.00, 5, 'chaca.jpg', 'Chả cá thác lác được làm từ thịt cá thác lác tươi ngon, quết nhuyễn cho đến khi dẻo dai, nêm nếm gia vị vừa vặn. Khi chiên hoặc nấu canh, chả cá có độ dai giòn, vị ngọt tự nhiên, thơm lừng đặc trưng. Đây là nguyên liệu tuyệt vời để làm món lẩu cá, canh khổ qua nhồi, bún chả cá, hoặc chiên vàng giòn ăn kèm cơm. Thịt cá giàu protein, ít béo, nhiều omega-3, rất tốt cho sức khỏe.', 63, 3, 5, 1, '2025-09-17 22:56:08', 'hien'),
(39, 'Nem chua Thanh Hóa', 70000, 65000.00, 7, 'nemchua.jpg', 'Nem chua Thanh Hóa là đặc sản nổi tiếng của xứ Thanh, được làm từ thịt heo nạc tươi, bì heo thái sợi, thính gạo rang và gia vị đặc trưng, gói trong lá chuối. Sau quá trình lên men tự nhiên, nem có vị chua dịu, dai giòn sần sật, thơm mùi tỏi ớt. Đây là món ăn chơi hấp dẫn, thường xuất hiện trong các bữa nhậu, liên hoan, hay làm quà biếu. Nem chua có thể ăn kèm rau thơm, tương ớt để tăng hương vị.', 55, 3, 6, 0, '2025-09-17 22:56:08', 'hien'),
(40, 'Khoai lang mật', 55000, 50000.00, 9, 'khoailang.jpg', 'Khoai lang mật có đặc điểm phần ruột vàng cam, nhiều mật ngọt, khi nướng hoặc luộc sẽ tiết ra lớp mật sánh vàng óng, vị thơm ngọt tự nhiên. Khoai lang mật chứa nhiều chất xơ, vitamin A, C, nhóm B, mangan và chất chống oxy hóa, giúp hỗ trợ tiêu hóa, đẹp da, tốt cho tim mạch và kiểm soát cân nặng. Có thể chế biến khoai lang mật bằng nhiều cách: nướng, hấp, luộc, làm chè, súp, bánh hoặc nghiền ăn kèm sữa chua.', 81, 4, 1, 0, '2025-09-17 22:56:08', 'hien'),
(41, 'Bí đỏ hồ lô', 32000, 30000.00, 6, 'bido.jpg', 'Bí đỏ hồ lô có hình dáng nhỏ gọn, thon dài như quả hồ lô, lớp vỏ cứng bảo quản được lâu. Ruột bí vàng cam đậm, vị ngọt bùi, mùi thơm dịu. Bí đỏ chứa nhiều beta-carotene (tiền vitamin A), vitamin C, kali và chất xơ – rất tốt cho mắt, da, tim mạch và hệ miễn dịch. Bí đỏ hồ lô có thể chế biến thành cháo, súp, chè, nấu canh hoặc làm bánh, vừa bổ dưỡng vừa dễ ăn. Đặc biệt, đây là thực phẩm lý tưởng cho trẻ em và người già.', 106, 4, 2, 0, '2025-09-17 22:56:08', 'hien'),
(42, 'Bún gạo lứt', 50000, 47000.00, 6, 'bun_lut.jpg', 'Bún gạo lứt được chế biến từ hạt gạo lứt nguyên cám, giữ trọn lớp vỏ cám giàu dinh dưỡng. Sợi bún có màu nâu đỏ tự nhiên, khi nấu có độ dai vừa phải, mùi thơm nhẹ đặc trưng của gạo lứt. Đây là thực phẩm giàu chất xơ, vitamin nhóm B, cùng các khoáng chất như magie, sắt, giúp hỗ trợ tiêu hóa, ổn định đường huyết và kiểm soát cân nặng. Bún gạo lứt có thể dùng để nấu bún nước, bún xào chay, hoặc ăn kèm với rau củ và thịt cá cho bữa ăn đủ chất.', 94, 5, 3, 0, '2025-09-17 22:56:08', 'hien'),
(43, 'Miến dong Cao Bằng', 65000, 60000.00, 8, 'mien_dong.jpg', 'Miến dong Cao Bằng, sợi dai ngon, Miến dong Cao Bằng được làm hoàn toàn từ củ dong riềng tươi, trải qua quá trình ngâm, lọc tinh bột và phơi khô tự nhiên trên những dàn phơi ngoài trời trong khí hậu mát mẻ của miền núi phía Bắc. Sợi miến có màu trong, dai giòn tự nhiên, không chứa hóa chất hay chất bảo quản. Khi nấu, sợi miến không bị nát, giữ được hương vị thanh mát đặc trưng. Miến dong Cao Bằng rất phù hợp để chế biến các món ăn như lẩu, xào, nấu canh hoặc làm nộm. Đặc biệt, đây là loại thực phẩm lành mạnh, ít calo, tốt cho người ăn kiêng hoặc người cần chế độ ăn uống nhẹ nhàng.', 118, 5, 4, 0, '2025-09-17 22:56:08', 'hien'),
(44, 'Nấm hương khô', 120000, 110000.00, 8, '1757757686_namhuong.jpg', 'Nấm hương rừng khô, thơm ngon. Nấm hương khô (nấm đông cô) được thu hái và sấy khô tự nhiên, giữ nguyên hương thơm đậm đà và vị ngọt umami đặc trưng. Khi ngâm nước, nấm nở đều, thịt dày, dai ngon. Đây là nguyên liệu quý trong ẩm thực Á Đông, dùng để nấu canh, xào, hầm, kho, nấu lẩu. Nấm hương chứa nhiều protein, chất xơ, vitamin B và D, giúp tăng sức đề kháng và hỗ trợ giảm cholesterol.', 56, 6, 5, 0, '2025-09-17 22:56:08', 'hien'),
(45, 'Măng khô Tây Bắc', 100000, 95000.00, 5, 'mang_kho.jpg', 'Măng khô tự nhiên Tây Bắc, đặc sản truyền thống. Măng khô Tây Bắc được làm từ măng tre tươi hái trên rừng, luộc sơ rồi phơi nắng nhiều ngày cho khô tự nhiên. Măng có màu vàng sậm, thơm đặc trưng, khi nấu nở mềm, vị ngọt bùi. Đây là nguyên liệu quen thuộc trong các món canh măng, măng hầm giò heo, xào hoặc nấu lẩu. Măng khô giàu chất xơ, vitamin và khoáng chất, giúp thanh lọc cơ thể, tốt cho tiêu hóa.', 58, 6, 6, 0, '2025-09-17 22:56:08', 'hien'),
(46, 'Táo hữu cơ New Zealand', 95000, 90000.00, 5, 'tao.jpg', 'Táo nhập khẩu, giòn ngọt. Táo hữu cơ New Zealand được trồng theo tiêu chuẩn hữu cơ nghiêm ngặt, không dùng thuốc trừ sâu hóa học, đảm bảo an toàn tuyệt đối. Quả táo có màu đỏ tươi hoặc xanh vàng, ruột giòn ngọt, thơm mát. Táo giàu vitamin C, kali, chất xơ và các hợp chất chống oxy hóa, giúp giảm cholesterol, tốt cho tim mạch và duy trì vóc dáng. Có thể ăn trực tiếp, làm nước ép, salad, bánh táo hoặc mứt.', 130, 1, 1, 0, '2025-09-17 22:56:08', 'hien'),
(47, 'Cam sành Hà Giang', 45000, 42000.00, 7, 'cam.jpg', 'Cam sành tươi ngon, nhiều vitamin C. Cam sành Hà Giang có vỏ xanh sẫm, hơi sần, khi chín ruột vàng cam mọng nước, vị ngọt thanh mát xen chút chua nhẹ. Loại cam này nổi tiếng nhờ khí hậu vùng núi Hà Giang tạo nên hương vị đậm đà đặc trưng. Cam giàu vitamin C, chất xơ và khoáng chất, giúp tăng cường miễn dịch, đẹp da và giải nhiệt. Phù hợp để ăn trực tiếp hoặc vắt lấy nước ép.', 134, 1, 2, 0, '2025-09-17 22:56:08', 'hien'),
(48, 'Xoài cát Hòa Lộc', 70000, 65000.00, 7, 'xoai.jpg', 'Xoài cát nổi tiếng miền Tây. Xoài cát Hòa Lộc nổi tiếng là giống xoài ngon nhất miền Tây. Quả to, vỏ vàng óng, ruột vàng cam, hương thơm nồng nàn, vị ngọt lịm và thịt mịn không xơ. Xoài chứa nhiều vitamin A, C, E, chất xơ và chất chống oxy hóa, tốt cho mắt, da và hệ miễn dịch. Có thể ăn tươi, làm sinh tố, kem, salad, nước ép hoặc chế biến món ăn mặn – ngọt đều phù hợp.', 50, 4, 3, 1, '2025-09-17 22:56:08', 'hien'),
(49, 'Chuối tiêu hồng', 35000, 32000.00, 9, 'chuoi.jpg', 'Chuối tiêu hồng giàu kali, tốt cho tiêu hóa.Chuối tiêu hồng có vỏ mỏng, khi chín vàng đều, ruột mềm dẻo, vị ngọt thơm đặc trưng. Đây là loại chuối giàu kali, vitamin B6, C và chất xơ, rất tốt cho tim mạch, tiêu hóa và bổ sung năng lượng. Chuối có thể ăn trực tiếp, làm sinh tố, bánh chuối, chuối chiên, hoặc sấy khô làm snack.', 112, 1, 4, 0, '2025-09-17 22:56:08', 'hien'),
(50, 'Ổi lê Đài Loan', 38000, 35000.00, 8, 'oi.jpg', 'Ổi lê Đài Loan giòn ngọt. Ổi lê Đài Loan quả to, hình tròn, lớp vỏ xanh bóng, ruột trắng giòn ngọt, ít hạt. Khi ăn, ổi cho vị thanh mát, giòn rụm và mùi thơm nhẹ. Đây là loại trái cây giàu vitamin C, chất xơ, giúp tăng cường sức đề kháng, tốt cho tiêu hóa và làm đẹp da. Có thể ăn trực tiếp, chấm muối ớt, làm nước ép hoặc salad trái cây.', 122, 1, 5, 1, '2025-09-17 22:56:08', 'hien'),
(51, 'Dưa hấu không hạt', 40000, 37000.00, 8, 'duahau.jpg', 'Dưa hấu ruột đỏ, không hạt. Dưa hấu không hạt có ruột đỏ tươi mọng nước, vỏ mỏng, vị ngọt mát tự nhiên. Loại dưa này đặc biệt được ưa chuộng nhờ ăn giòn ngọt mà không vướng hạt. Dưa hấu giàu vitamin A, C, kali và lycopene – chất chống oxy hóa có lợi cho tim mạch, giảm nguy cơ ung thư. Thích hợp ăn giải khát trong mùa hè, làm sinh tố, nước ép hoặc tráng miệng.', 193, 4, 6, 0, '2025-09-17 22:56:08', 'hien'),
(52, 'Bưởi da xanh', 95000, 90000.00, 5, 'buoi.jpg', 'Bưởi da xanh ruột hồng, ngọt thanh. Bưởi da xanh là đặc sản nổi tiếng của miền Nam, quả to, vỏ mỏng, dễ bóc. Múi bưởi mọng nước, vị ngọt thanh pha chút chua nhẹ, không đắng, không the. Bưởi chứa nhiều vitamin C, kali, chất xơ và chất chống oxy hóa, giúp giảm mỡ máu, đẹp da, hỗ trợ giảm cân. Có thể ăn trực tiếp, làm gỏi bưởi, nước ép hoặc tráng miệng sau bữa ăn.', 80, 4, 1, 0, '2025-09-17 22:56:08', 'hien'),
(53, 'Thanh long ruột đỏ ngon ngon ', 20000, 55000.00, 8, 'thanhlong.jpg', 'Thanh long ruột đỏ nhiều vitamin C, Thanh long ruột đỏ có lớp vỏ hồng đỏ rực rỡ, tai xanh tươi, ruột đỏ tím bắt mắt. Thịt quả mềm mọng nước, vị ngọt thanh mát, mùi thơm nhẹ đặc trưng. Thanh long ruột đỏ giàu chất xơ, vitamin C, betacyanin và chất chống oxy hóa mạnh mẽ, giúp tăng cường miễn dịch, đẹp da và hỗ trợ tiêu hóa. Đây là loại trái cây lý tưởng để ăn tươi, làm sinh tố, nước ép, salad trái cây hoặc chế biến món tráng miệng.', 90, 4, 2, 0, '2025-09-01 22:56:08', 'hien'),
(57, 'Sầu Riêng Đắk Lắk', 120000, NULL, NULL, '1758094495_saurieng.jpg', 'Sầu riêng Đắk Lắk nổi tiếng với hương vị ngọt béo tự nhiên, múi dày, hạt lép. Các giống sầu phổ biến bao gồm Ri 6 (ngọt thanh, hạt lép) và Dona (Thái Monthong) (cơm dày, vàng óng, tan trong miệng). Vùng đất đỏ bazan và khí hậu thuận lợi giúp sầu riêng nơi đây đạt chất lượng tốt, được xuất khẩu sang nhiều thị trường khó tính như Trung Quốc, Nhật Bản, Hoa Kỳ', 75, 4, 1, 1, '2025-09-17 22:56:08', 'hien');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `taikhoan`
--

CREATE TABLE `taikhoan` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `diachi` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `diem` int(11) DEFAULT 0,
  `avatar` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `taikhoan`
--

INSERT INTO `taikhoan` (`id`, `username`, `email`, `diachi`, `password`, `phone`, `created_at`, `role`, `diem`, `avatar`) VALUES
(1, 'SUONGNIE', 'suongnie2k5@gmail.com', 'Thị Trấn Pơng Drang, Krông Buk, Đắk Lắk', '$2y$10$GiLXY6uLHk4O5KvDbLtJ5eKH8PN12JGPuqVgnpjhqf9P3ISGUE2rC', '0325581015', '2025-08-12 06:15:50', 'admin', 9198, 'uploads/1757321513_logo.jpg'),
(3, 'LEVIETDUNG', 'DungLe1234@gmail.com', 'Đà Nẵng', '$2y$10$2KjvWrj6WgRBdtlvpyXM5OZDGMhqKXeR/lbmmLi8AlOtwKvXjLgFa', '0325581016', '2025-08-18 07:54:16', 'user', 6950, 'uploads/1757756607_avata2.jpg'),
(4, 'jackerEvalden', 'bruhlmaoexe@gmail.com', '', '$2y$10$5vSq4zsu.tf9mlC/rpl3J.ZUso.t0rmB1OLQuEvE8T4hNH4t6DxlS', '099393939', '2025-08-25 06:38:03', 'user', 0, 'uploads/1757756607_avata2.jpg'),
(6, 'JACKER', 'htcatadc@gmail.com', '38 nai hien', '$2y$10$C/yaRXspxhmTPJ1S2I.6NuUl1969Vq6rXQF1v/ACcI1vEKlKHUIy.', '0325581017', '2025-08-26 16:28:22', 'user', 2400, 'uploads/1756880603_Screenshot 2025-08-29 221747.png'),
(10, 'SANGMLO', 'Sangnie@gmail.com', 'Thị Trấn Pơng Drang', '$2y$10$ZEMTe70OUvPRg5cSrq7c5OCdKDn6vflCgOvbOou9ug2ZUG5SuJMo6', '0324578962', '2025-09-13 10:02:15', 'admin', 1401, 'uploads/1757757782_avatar.jpg'),
(11, 'quangtrung', 'trunghocit@gmail.com', 'mmmm', '$2y$10$MzxvjltBcReoLhXa/qFVae3HhccJE1arh/4N2DvisAZp2DCHT5dJe', '123', '2025-09-16 09:34:00', 'user', 1000, 'uploads/1758015275_White.png'),
(13, 'Dungden', 'Nam@gmail.com', '', '$2y$10$IMLp8.UXELyVrRlIxa1Sz./ZuIbLzGOBH90MJ9M4pwjyuHvMYXR.K', '123', '2025-09-17 17:20:07', 'user', 0, 'uploads/1758466605_Jackson.jpg'),
(16, 'Phân Cức', 'cucphan11665@gmail.com', '', '', '', '2025-09-29 07:56:27', 'user', 0, '');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thongbao`
--

CREATE TABLE `thongbao` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `thongbao`
--

INSERT INTO `thongbao` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(60, 1, 'Cập nhật sản phẩm: Sầu riêng Đắk Lắk | Giá: 99,999đ | SL: 100', 0, '2025-09-17 07:41:52'),
(61, 1, 'Cập nhật loại sản phẩm (ID=6): Đặc sản đồ kho', 0, '2025-09-17 07:44:56'),
(62, 1, 'Đã sửa tên loại sản phẩm từ \'Đặc sản đồ kho\' thành \'Đặc sản đồ khô\'', 0, '2025-09-17 07:47:02'),
(63, 1, 'Thêm loại sản phẩm mới: \'Nước\'', 0, '2025-09-17 07:47:17'),
(64, 1, 'Đã xóa loại sản phẩm: \'Nước\'', 0, '2025-09-17 07:47:40'),
(65, 1, 'Thêm thương hiệu mới: \'SYN Nông sản việt\'', 0, '2025-09-17 07:49:52'),
(66, 1, 'Đã đổi tên thương hiệu từ \'SYN Nông sản việt\' thành \'SYN\'', 0, '2025-09-17 07:50:08'),
(67, 1, 'Đã xóa thương hiệu: \'SYN\'', 0, '2025-09-17 07:50:18'),
(68, 1, 'Cập nhật khuyến mại sản phẩm \'Ổi lê Đài Loan\' | Giá KM: 13,000đ | Giảm: 70% | Từ 2025-09-07 đến 2025-09-30', 0, '2025-09-17 07:54:34'),
(69, 1, 'Thêm khuyến mại cho sản phẩm \'Bí đỏ hồ lô\' | Giá KM: 26,000đ | Giảm: 30% | Từ 2025-09-17 đến 2025-09-30', 0, '2025-09-17 07:55:39'),
(70, 1, 'Cập nhật khuyến mại sản phẩm \'Bí đỏ hồ lô\' | Giá KM: 26,000đ | Giảm: 19% | Từ 2025-09-17 đến 2025-09-30', 0, '2025-09-17 07:56:01'),
(71, 1, 'Cập nhật khuyến mại sản phẩm \'Bí đỏ hồ lô\' | Giá KM: 26,000đ | Giảm: 19% | Từ 2025-09-17 đến 2025-09-30', 0, '2025-09-17 08:01:45'),
(72, 1, 'Cập nhật khuyến mại sản phẩm \'Bí đỏ hồ lô\' | Giá KM: 26,000đ | Giảm: 19% | Từ 2025-09-17 đến 2025-09-17', 0, '2025-09-17 08:01:53'),
(73, 1, 'Khuyến mại sản phẩm \'Bí đỏ hồ lô\' đã kết thúc vào 2025-09-17', 0, '2025-09-17 08:02:03'),
(74, 1, 'Cập nhật khuyến mại sản phẩm \'Bí đỏ hồ lô\' | Giá KM: 26,000đ | Giảm: 19% | Từ 2025-09-17 đến 2025-09-18', 0, '2025-09-17 08:02:23'),
(75, 1, 'Người dùng SUONGNIE vừa trả lời đánh giá của SUONGNIE22 trong sản phẩm Cải bó xôi hữu cơ', 0, '2025-09-17 14:15:48'),
(76, 1, 'Người dùng SUONGNIE vừa trả lời đánh giá của SUONGNIE22 trong sản phẩm Cải bó xôi hữu cơ', 0, '2025-09-17 15:15:59'),
(77, 1, 'Người dùng SUONGNIE vừa trả lời đánh giá của SangMLO trong sản phẩm Cải bó xôi hữu cơ', 0, '2025-09-17 15:16:28'),
(78, 3, 'Người dùng LEVIETDUNG vừa trả lời đánh giá của LEVIETDUNG trong sản phẩm Cải bó xôi hữu cơ', 0, '2025-09-17 15:18:49'),
(79, 1, 'Người dùng SUONGNIE vừa trả lời đánh giá của SangMLO trong sản phẩm Cải bó xôi hữu cơ', 0, '2025-09-17 15:26:34'),
(80, 1, 'Khuyến mại sản phẩm \'Bí đỏ hồ lô\' đã kết thúc vào 2025-09-18', 0, '2025-09-17 17:15:48'),
(81, 1, 'Khuyến mại sản phẩm \'Bí đỏ hồ lô\' đã kết thúc vào 2025-09-18', 0, '2025-09-17 17:29:07'),
(82, 1, 'Cập nhật sản phẩm: Sầu Riêng Đắk Lắk | Giá: 99,999đ | SL: 100', 0, '2025-09-17 17:29:51'),
(83, 1, 'Cập nhật sản phẩm: Sầu Riêng Đắk Lắk | Giá: 99,999đ | SL: 100', 0, '2025-09-17 17:38:49'),
(84, 1, 'Người dùng SUONGNIE vừa trả lời đánh giá của LEVIETDUNG trong sản phẩm Cải bó xôi hữu cơ', 0, '2025-09-20 23:22:55'),
(85, 1, 'Người dùng SUONGNIE vừa bình luận về Cải bó xôi hữu cơ', 0, '2025-09-20 23:57:04'),
(86, 1, 'Khuyến mại sản phẩm \'Chả cá thác lác\' đã kết thúc vào 2025-09-20', 0, '2025-09-21 00:12:46'),
(87, 1, 'Khuyến mại sản phẩm \'Bí đỏ hồ lô\' đã kết thúc vào 2025-09-18', 0, '2025-09-21 00:12:46'),
(88, 10, 'Người dùng SANGMLO vừa trả lời đánh giá của SUONGNIE trong sản phẩm Cải bó xôi hữu cơ', 0, '2025-09-21 00:28:44'),
(89, 10, 'Người dùng SANGMLO vừa trả lời đánh giá của SUONGNIE trong sản phẩm Cải bó xôi hữu cơ', 0, '2025-09-21 00:29:23'),
(90, 1, 'Cập nhật sản phẩm: Sầu Riêng Đắk Lắk | Giá: 120,000đ | SL: 100', 0, '2025-09-21 14:01:39'),
(91, 1, 'Khuyến mại sản phẩm \'Chả cá thác lác\' đã kết thúc vào 2025-09-20', 0, '2025-09-21 14:53:55'),
(92, 1, 'Khuyến mại sản phẩm \'Bí đỏ hồ lô\' đã kết thúc vào 2025-09-18', 0, '2025-09-21 14:53:55'),
(93, 1, 'Người dùng SUONGNIE vừa bình luận về Sầu Riêng Đắk Lắk', 0, '2025-09-22 06:02:40'),
(94, 1, 'Khuyến mại sản phẩm \'Chả cá thác lác\' đã kết thúc vào 2025-09-20', 0, '2025-09-22 06:04:11'),
(95, 1, 'Khuyến mại sản phẩm \'Bí đỏ hồ lô\' đã kết thúc vào 2025-09-18', 0, '2025-09-22 06:04:12'),
(96, 1, 'Khuyến mại sản phẩm \'Chả cá thác lác\' đã kết thúc vào 2025-09-20', 0, '2025-09-22 06:11:27'),
(97, 1, 'Khuyến mại sản phẩm \'Bí đỏ hồ lô\' đã kết thúc vào 2025-09-18', 0, '2025-09-22 06:11:27'),
(98, 1, 'Cập nhật sản phẩm: Thanh long ruột đỏ ngon ngon  | Giá: 20,000đ | SL: 100', 0, '2025-09-22 06:14:16'),
(99, 1, 'Người dùng SUONGNIE vừa trả lời đánh giá của SUONGNIE trong sản phẩm Cải bó xôi hữu cơ', 0, '2025-09-22 06:14:42'),
(100, 1, 'Khuyến mại sản phẩm \'Chả cá thác lác\' đã kết thúc vào 2025-09-20', 0, '2025-09-22 06:17:35'),
(101, 1, 'Khuyến mại sản phẩm \'Bí đỏ hồ lô\' đã kết thúc vào 2025-09-18', 0, '2025-09-22 06:17:35'),
(102, 1, 'Cập nhật khuyến mại sản phẩm \'Ổi lê Đài Loan\' | Giá KM: 13,000đ | Giảm: 70% | Từ 2025-09-07 đến 2025-09-22', 0, '2025-09-22 06:17:47'),
(103, 1, 'Khuyến mại sản phẩm \'Chả cá thác lác\' đã kết thúc vào 2025-09-20', 0, '2025-09-22 06:17:50'),
(104, 1, 'Khuyến mại sản phẩm \'Ổi lê Đài Loan\' đã kết thúc vào 2025-09-22', 0, '2025-09-22 06:17:50'),
(105, 1, 'Khuyến mại sản phẩm \'Bí đỏ hồ lô\' đã kết thúc vào 2025-09-18', 0, '2025-09-22 06:17:50'),
(106, 1, 'Khuyến mại sản phẩm \'Chả cá thác lác\' đã kết thúc vào 2025-09-20', 0, '2025-09-27 04:46:52'),
(107, 1, 'Khuyến mại sản phẩm \'Ổi lê Đài Loan\' đã kết thúc vào 2025-09-22', 0, '2025-09-27 04:46:52'),
(108, 1, 'Khuyến mại sản phẩm \'Bí đỏ hồ lô\' đã kết thúc vào 2025-09-18', 0, '2025-09-27 04:46:52'),
(109, 1, 'Khuyến mại sản phẩm \'Chả cá thác lác\' đã kết thúc vào 2025-09-20', 0, '2025-09-27 04:48:45'),
(110, 1, 'Khuyến mại sản phẩm \'Ổi lê Đài Loan\' đã kết thúc vào 2025-09-22', 0, '2025-09-27 04:48:45'),
(111, 1, 'Khuyến mại sản phẩm \'Bí đỏ hồ lô\' đã kết thúc vào 2025-09-18', 0, '2025-09-27 04:48:45'),
(112, 10, 'Người dùng SANGMLO vừa trả lời đánh giá của SUONGNIE22 trong sản phẩm Thanh long ruột đỏ ngon ngon ', 0, '2025-09-27 09:21:55'),
(113, 10, 'Người dùng SANGMLO vừa bình luận về Thanh long ruột đỏ ngon ngon ', 0, '2025-09-27 09:23:11'),
(114, 1, 'Người dùng SUONGNIE vừa bình luận về Ổi lê Đài Loan', 0, '2025-09-27 09:26:21'),
(115, 1, 'Người dùng SUONGNIE vừa bình luận về Bưởi da xanh', 0, '2025-09-27 14:51:32'),
(116, 3, 'Người dùng LEVIETDUNG vừa trả lời đánh giá của SUONGNIE trong sản phẩm Bưởi da xanh', 0, '2025-09-27 17:07:55'),
(117, 3, 'Người dùng LEVIETDUNG vừa trả lời đánh giá của SUONGNIE trong sản phẩm Sầu Riêng Đắk Lắk', 0, '2025-09-28 13:07:18'),
(118, 1, 'Sản phẩm ID 57 đã chuyển sang trạng thái: an', 0, '2025-10-02 09:32:30'),
(119, 1, 'Sản phẩm ID 57 đã chuyển sang trạng thái: hien', 0, '2025-10-02 09:32:54'),
(120, 1, 'Sản phẩm \'Sầu Riêng Đắk Lắk\' đã được ẩn (vì có lịch sử giao dịch).', 0, '2025-10-02 09:39:25'),
(121, 1, 'Sản phẩm ID 57 đã chuyển sang trạng thái: hien', 0, '2025-10-02 09:39:34'),
(122, 1, '⚠️ Không thể xóa sản phẩm Sầu Riêng Đắk Lắk vì đã có trong lịch sử mua hàng. \r\n                    Vui lòng ẨN sản phẩm nếu không còn kinh doanh.', 0, '2025-10-02 09:41:39'),
(123, 1, '⚠️ Không thể xóa sản phẩm Sầu Riêng Đắk Lắk vì đã có trong lịch sử mua hàng. \r\n                    Vui lòng ẨN sản phẩm nếu không còn kinh doanh.', 0, '2025-10-02 09:41:49'),
(124, 1, 'Sản phẩm ID 57 đã chuyển sang trạng thái: an', 0, '2025-10-02 09:49:03'),
(125, 1, 'Sản phẩm ID 57 đã chuyển sang trạng thái: hien', 0, '2025-10-02 09:49:18'),
(126, 1, 'Sản phẩm ID 53 đã chuyển sang trạng thái: an', 0, '2025-10-02 09:50:34'),
(127, 1, 'Sản phẩm ID 53 đã chuyển sang trạng thái: hien', 0, '2025-10-02 10:01:45'),
(128, 1, 'Khuyến mại sản phẩm \'Cải bó xôi hữu cơ\' đã kết thúc vào 2025-09-30', 0, '2025-10-02 10:02:07'),
(129, 1, 'Khuyến mại sản phẩm \'Gạo ST25\' đã kết thúc vào 2025-09-30', 0, '2025-10-02 10:02:07'),
(130, 1, 'Khuyến mại sản phẩm \'Chả cá thác lác\' đã kết thúc vào 2025-09-20', 0, '2025-10-02 10:02:07'),
(131, 1, 'Khuyến mại sản phẩm \'Khoai lang mật\' đã kết thúc vào 2025-10-01', 0, '2025-10-02 10:02:07'),
(132, 1, 'Khuyến mại sản phẩm \'Nấm hương khô\' đã kết thúc vào 2025-09-30', 0, '2025-10-02 10:02:07'),
(133, 1, 'Khuyến mại sản phẩm \'Ổi lê Đài Loan\' đã kết thúc vào 2025-09-22', 0, '2025-10-02 10:02:07'),
(134, 1, 'Khuyến mại sản phẩm \'Bí đỏ hồ lô\' đã kết thúc vào 2025-09-18', 0, '2025-10-02 10:02:07'),
(135, 1, 'Thêm khuyến mại cho sản phẩm \'Sầu Riêng Đắk Lắk\' | Giá KM: 50,000đ | Giảm: 50% | Từ 2025-10-02 đến 2025-10-31', 0, '2025-10-02 10:04:28'),
(136, 1, 'Khuyến mại sản phẩm \'Cải bó xôi hữu cơ\' đã kết thúc vào 2025-09-30', 0, '2025-10-02 10:04:33'),
(137, 1, 'Khuyến mại sản phẩm \'Gạo ST25\' đã kết thúc vào 2025-09-30', 0, '2025-10-02 10:04:33'),
(138, 1, 'Khuyến mại sản phẩm \'Chả cá thác lác\' đã kết thúc vào 2025-09-20', 0, '2025-10-02 10:04:33'),
(139, 1, 'Khuyến mại sản phẩm \'Khoai lang mật\' đã kết thúc vào 2025-10-01', 0, '2025-10-02 10:04:33'),
(140, 1, 'Khuyến mại sản phẩm \'Nấm hương khô\' đã kết thúc vào 2025-09-30', 0, '2025-10-02 10:04:33'),
(141, 1, 'Khuyến mại sản phẩm \'Ổi lê Đài Loan\' đã kết thúc vào 2025-09-22', 0, '2025-10-02 10:04:33'),
(142, 1, 'Khuyến mại sản phẩm \'Bí đỏ hồ lô\' đã kết thúc vào 2025-09-18', 0, '2025-10-02 10:04:33'),
(143, 1, 'Sản phẩm ID 57 đã chuyển sang trạng thái: an', 0, '2025-10-02 10:08:14'),
(144, 1, '✅ Sản phẩm Sầu Riêng Đắk Lắk đã được chuyển sang trạng thái: HIỆN (Đang kinh doanh)', 0, '2025-10-02 10:38:30'),
(145, 1, '❌ Sản phẩm Sầu Riêng Đắk Lắk đã Ngừng kinh doanh)', 0, '2025-10-02 10:39:47'),
(146, 1, '✅ Sản phẩm Sầu Riêng Đắk Lắk đã Được kinh doanh)', 0, '2025-10-02 12:32:30'),
(147, 1, '❌ Sản phẩm Sầu Riêng Đắk Lắk đã Ngừng kinh doanh)', 0, '2025-10-02 12:32:33'),
(148, 1, '✅ Sản phẩm Sầu Riêng Đắk Lắk đã Được kinh doanh)', 0, '2025-10-02 12:33:09'),
(149, 1, 'Khuyến mại sản phẩm \'Cải bó xôi hữu cơ\' đã kết thúc vào 2025-09-30', 0, '2025-10-10 12:48:20'),
(150, 1, 'Khuyến mại sản phẩm \'Gạo ST25\' đã kết thúc vào 2025-09-30', 0, '2025-10-10 12:48:20'),
(151, 1, 'Khuyến mại sản phẩm \'Chả cá thác lác\' đã kết thúc vào 2025-09-20', 0, '2025-10-10 12:48:20'),
(152, 1, 'Khuyến mại sản phẩm \'Khoai lang mật\' đã kết thúc vào 2025-10-01', 0, '2025-10-10 12:48:20'),
(153, 1, 'Khuyến mại sản phẩm \'Nấm hương khô\' đã kết thúc vào 2025-09-30', 0, '2025-10-10 12:48:20'),
(154, 1, 'Khuyến mại sản phẩm \'Ổi lê Đài Loan\' đã kết thúc vào 2025-09-22', 0, '2025-10-10 12:48:20'),
(155, 1, 'Khuyến mại sản phẩm \'Bí đỏ hồ lô\' đã kết thúc vào 2025-09-18', 0, '2025-10-10 12:48:20'),
(156, 1, 'Khuyến mại sản phẩm \'Cải bó xôi hữu cơ\' đã kết thúc vào 2025-09-30', 0, '2025-10-10 12:55:28'),
(157, 1, 'Khuyến mại sản phẩm \'Gạo ST25\' đã kết thúc vào 2025-09-30', 0, '2025-10-10 12:55:28'),
(158, 1, 'Khuyến mại sản phẩm \'Chả cá thác lác\' đã kết thúc vào 2025-09-20', 0, '2025-10-10 12:55:28'),
(159, 1, 'Khuyến mại sản phẩm \'Khoai lang mật\' đã kết thúc vào 2025-10-01', 0, '2025-10-10 12:55:28'),
(160, 1, 'Khuyến mại sản phẩm \'Nấm hương khô\' đã kết thúc vào 2025-09-30', 0, '2025-10-10 12:55:28'),
(161, 1, 'Khuyến mại sản phẩm \'Ổi lê Đài Loan\' đã kết thúc vào 2025-09-22', 0, '2025-10-10 12:55:28'),
(162, 1, 'Khuyến mại sản phẩm \'Bí đỏ hồ lô\' đã kết thúc vào 2025-09-18', 0, '2025-10-10 12:55:28'),
(163, 1, 'Khuyến mại sản phẩm \'Cải bó xôi hữu cơ\' đã kết thúc vào 2025-09-30', 0, '2025-10-10 12:56:45'),
(164, 1, 'Khuyến mại sản phẩm \'Gạo ST25\' đã kết thúc vào 2025-09-30', 0, '2025-10-10 12:56:45'),
(165, 1, 'Khuyến mại sản phẩm \'Chả cá thác lác\' đã kết thúc vào 2025-09-20', 0, '2025-10-10 12:56:45'),
(166, 1, 'Khuyến mại sản phẩm \'Khoai lang mật\' đã kết thúc vào 2025-10-01', 0, '2025-10-10 12:56:45'),
(167, 1, 'Khuyến mại sản phẩm \'Nấm hương khô\' đã kết thúc vào 2025-09-30', 0, '2025-10-10 12:56:45'),
(168, 1, 'Khuyến mại sản phẩm \'Ổi lê Đài Loan\' đã kết thúc vào 2025-09-22', 0, '2025-10-10 12:56:45'),
(169, 1, 'Khuyến mại sản phẩm \'Bí đỏ hồ lô\' đã kết thúc vào 2025-09-18', 0, '2025-10-10 12:56:45'),
(170, 1, '❌ Sản phẩm Sầu Riêng Đắk Lắk đã Ngừng kinh doanh)', 0, '2025-10-10 15:49:14'),
(171, 1, '✅ Sản phẩm Sầu Riêng Đắk Lắk đã Được kinh doanh)', 0, '2025-10-10 15:49:25'),
(172, 1, '⚠️ Bạn không thể xóa sản phẩm Thanh long ruột đỏ ngon ngon  vì sản phẩm đang có thông tin đơn hàng của khách hàng. \r\n                    Nếu không muốn hiển thị sản phẩm này, hãy thay đổi Trạng thái để sản phẩm sẽ ngừng kinh doanh.', 0, '2025-10-10 15:50:50'),
(173, 1, '❌ Sản phẩm Sầu Riêng Đắk Lắk đã Ngừng kinh doanh)', 0, '2025-10-10 15:53:51'),
(174, 1, '⚠️ Bạn không thể xóa sản phẩm Thanh long ruột đỏ ngon ngon  vì sản phẩm đang có thông tin đơn hàng của khách hàng. \r\n                    Nếu không muốn hiển thị sản phẩm này, hãy thay đổi Trạng thái để sản phẩm sẽ ngừng kinh doanh.', 0, '2025-10-10 15:55:39'),
(175, 1, '✅ Sản phẩm Sầu Riêng Đắk Lắk đã Được kinh doanh)', 0, '2025-10-10 16:00:00'),
(176, 1, '❌ Sản phẩm Sầu Riêng Đắk Lắk đã Ngừng kinh doanh)', 0, '2025-10-10 16:00:02'),
(177, 1, 'Thêm loại sản phẩm mới: \'Bún\'', 0, '2025-10-10 16:10:42'),
(178, 1, 'Đã xóa loại sản phẩm: \'Bún\'', 0, '2025-10-10 16:10:48'),
(179, 1, '✅ Sản phẩm Sầu Riêng Đắk Lắk đã Được kinh doanh)', 0, '2025-10-14 14:44:24'),
(180, 1, 'Khuyến mại sản phẩm \'Cải bó xôi hữu cơ\' đã kết thúc vào 2025-09-30', 0, '2025-10-14 14:44:47'),
(181, 1, 'Khuyến mại sản phẩm \'Gạo ST25\' đã kết thúc vào 2025-09-30', 0, '2025-10-14 14:44:47'),
(182, 1, 'Khuyến mại sản phẩm \'Chả cá thác lác\' đã kết thúc vào 2025-09-20', 0, '2025-10-14 14:44:47'),
(183, 1, 'Khuyến mại sản phẩm \'Khoai lang mật\' đã kết thúc vào 2025-10-01', 0, '2025-10-14 14:44:47'),
(184, 1, 'Khuyến mại sản phẩm \'Nấm hương khô\' đã kết thúc vào 2025-09-30', 0, '2025-10-14 14:44:47'),
(185, 1, 'Khuyến mại sản phẩm \'Ổi lê Đài Loan\' đã kết thúc vào 2025-09-22', 0, '2025-10-14 14:44:47'),
(186, 1, 'Khuyến mại sản phẩm \'Bí đỏ hồ lô\' đã kết thúc vào 2025-09-18', 0, '2025-10-14 14:44:47'),
(187, 1, 'Khuyến mại sản phẩm \'Cải bó xôi hữu cơ\' đã kết thúc vào 2025-09-30', 0, '2025-10-14 14:44:52'),
(188, 1, 'Khuyến mại sản phẩm \'Gạo ST25\' đã kết thúc vào 2025-09-30', 0, '2025-10-14 14:44:52'),
(189, 1, 'Khuyến mại sản phẩm \'Chả cá thác lác\' đã kết thúc vào 2025-09-20', 0, '2025-10-14 14:44:52'),
(190, 1, 'Khuyến mại sản phẩm \'Khoai lang mật\' đã kết thúc vào 2025-10-01', 0, '2025-10-14 14:44:52'),
(191, 1, 'Khuyến mại sản phẩm \'Nấm hương khô\' đã kết thúc vào 2025-09-30', 0, '2025-10-14 14:44:52'),
(192, 1, 'Khuyến mại sản phẩm \'Ổi lê Đài Loan\' đã kết thúc vào 2025-09-22', 0, '2025-10-14 14:44:52'),
(193, 1, 'Khuyến mại sản phẩm \'Bí đỏ hồ lô\' đã kết thúc vào 2025-09-18', 0, '2025-10-14 14:44:52'),
(194, 1, 'Khuyến mại sản phẩm \'Cải bó xôi hữu cơ\' đã kết thúc vào 2025-09-30', 0, '2025-10-14 15:00:09'),
(195, 1, 'Khuyến mại sản phẩm \'Gạo ST25\' đã kết thúc vào 2025-09-30', 0, '2025-10-14 15:00:09'),
(196, 1, 'Khuyến mại sản phẩm \'Chả cá thác lác\' đã kết thúc vào 2025-09-20', 0, '2025-10-14 15:00:09'),
(197, 1, 'Khuyến mại sản phẩm \'Khoai lang mật\' đã kết thúc vào 2025-10-01', 0, '2025-10-14 15:00:09'),
(198, 1, 'Khuyến mại sản phẩm \'Nấm hương khô\' đã kết thúc vào 2025-09-30', 0, '2025-10-14 15:00:09'),
(199, 1, 'Khuyến mại sản phẩm \'Ổi lê Đài Loan\' đã kết thúc vào 2025-09-22', 0, '2025-10-14 15:00:09'),
(200, 1, 'Khuyến mại sản phẩm \'Bí đỏ hồ lô\' đã kết thúc vào 2025-09-18', 0, '2025-10-14 15:00:09'),
(201, 1, 'Khuyến mại sản phẩm \'Cải bó xôi hữu cơ\' đã kết thúc vào 2025-09-30', 0, '2025-10-15 06:38:15'),
(202, 1, 'Khuyến mại sản phẩm \'Gạo ST25\' đã kết thúc vào 2025-09-30', 0, '2025-10-15 06:38:15'),
(203, 1, 'Khuyến mại sản phẩm \'Chả cá thác lác\' đã kết thúc vào 2025-09-20', 0, '2025-10-15 06:38:15'),
(204, 1, 'Khuyến mại sản phẩm \'Khoai lang mật\' đã kết thúc vào 2025-10-01', 0, '2025-10-15 06:38:15'),
(205, 1, 'Khuyến mại sản phẩm \'Nấm hương khô\' đã kết thúc vào 2025-09-30', 0, '2025-10-15 06:38:15'),
(206, 1, 'Khuyến mại sản phẩm \'Ổi lê Đài Loan\' đã kết thúc vào 2025-09-22', 0, '2025-10-15 06:38:15'),
(207, 1, 'Khuyến mại sản phẩm \'Bí đỏ hồ lô\' đã kết thúc vào 2025-09-18', 0, '2025-10-15 06:38:15');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thongbao_read`
--

CREATE TABLE `thongbao_read` (
  `id` int(11) NOT NULL,
  `thongbao_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `thongbao_read`
--

INSERT INTO `thongbao_read` (`id`, `thongbao_id`, `user_id`, `is_read`, `created_at`) VALUES
(296, 60, 1, 1, '2025-09-17 07:41:55'),
(297, 61, 1, 1, '2025-09-17 07:44:59'),
(298, 62, 1, 1, '2025-09-17 07:47:04'),
(299, 63, 1, 1, '2025-09-17 07:47:20'),
(300, 64, 1, 1, '2025-09-17 07:47:47'),
(301, 65, 1, 1, '2025-09-17 07:50:12'),
(302, 66, 1, 1, '2025-09-17 07:50:12'),
(304, 67, 1, 1, '2025-09-17 07:50:21'),
(305, 68, 1, 1, '2025-09-17 07:54:40'),
(306, 69, 1, 1, '2025-09-17 07:55:43'),
(307, 70, 1, 1, '2025-09-17 07:56:04'),
(308, 71, 1, 1, '2025-09-17 08:01:57'),
(309, 72, 1, 1, '2025-09-17 08:01:57'),
(311, 73, 1, 1, '2025-09-17 08:02:05'),
(312, 74, 1, 1, '2025-09-17 08:03:28'),
(313, 75, 1, 1, '2025-09-17 14:15:55'),
(314, 76, 1, 1, '2025-09-17 15:19:33'),
(315, 77, 1, 1, '2025-09-17 15:19:33'),
(316, 78, 1, 1, '2025-09-17 15:19:33'),
(317, 79, 1, 1, '2025-09-17 16:03:47'),
(318, 80, 1, 1, '2025-09-17 17:28:55'),
(319, 81, 1, 1, '2025-09-17 17:29:56'),
(320, 82, 1, 1, '2025-09-17 17:29:56'),
(321, 83, 1, 1, '2025-09-18 06:01:11'),
(322, 84, 1, 1, '2025-09-20 23:57:05'),
(323, 85, 1, 1, '2025-09-20 23:57:05'),
(325, 60, 10, 1, '2025-09-21 00:29:33'),
(326, 61, 10, 1, '2025-09-21 00:29:33'),
(327, 62, 10, 1, '2025-09-21 00:29:33'),
(328, 63, 10, 1, '2025-09-21 00:29:33'),
(329, 64, 10, 1, '2025-09-21 00:29:33'),
(330, 65, 10, 1, '2025-09-21 00:29:33'),
(331, 66, 10, 1, '2025-09-21 00:29:33'),
(332, 67, 10, 1, '2025-09-21 00:29:33'),
(333, 68, 10, 1, '2025-09-21 00:29:33'),
(334, 69, 10, 1, '2025-09-21 00:29:33'),
(335, 70, 10, 1, '2025-09-21 00:29:33'),
(336, 71, 10, 1, '2025-09-21 00:29:33'),
(337, 72, 10, 1, '2025-09-21 00:29:33'),
(338, 73, 10, 1, '2025-09-21 00:29:33'),
(339, 74, 10, 1, '2025-09-21 00:29:33'),
(340, 75, 10, 1, '2025-09-21 00:29:33'),
(341, 76, 10, 1, '2025-09-21 00:29:33'),
(342, 77, 10, 1, '2025-09-21 00:29:33'),
(343, 78, 10, 1, '2025-09-21 00:29:33'),
(344, 79, 10, 1, '2025-09-21 00:29:33'),
(345, 80, 10, 1, '2025-09-21 00:29:33'),
(346, 81, 10, 1, '2025-09-21 00:29:33'),
(347, 82, 10, 1, '2025-09-21 00:29:33'),
(348, 83, 10, 1, '2025-09-21 00:29:33'),
(349, 84, 10, 1, '2025-09-21 00:29:33'),
(350, 85, 10, 1, '2025-09-21 00:29:33'),
(351, 86, 10, 1, '2025-09-21 00:29:33'),
(352, 87, 10, 1, '2025-09-21 00:29:33'),
(353, 88, 10, 1, '2025-09-21 00:29:33'),
(354, 89, 10, 1, '2025-09-21 00:29:33'),
(355, 86, 1, 1, '2025-09-21 14:01:59'),
(356, 87, 1, 1, '2025-09-21 14:01:59'),
(357, 88, 1, 1, '2025-09-21 14:01:59'),
(358, 89, 1, 1, '2025-09-21 14:01:59'),
(359, 90, 1, 1, '2025-09-21 14:01:59'),
(360, 91, 1, 1, '2025-09-22 06:13:56'),
(361, 92, 1, 1, '2025-09-22 06:13:56'),
(362, 93, 1, 1, '2025-09-22 06:13:56'),
(363, 94, 1, 1, '2025-09-22 06:13:56'),
(364, 95, 1, 1, '2025-09-22 06:13:56'),
(365, 96, 1, 1, '2025-09-22 06:13:56'),
(366, 97, 1, 1, '2025-09-22 06:13:56'),
(367, 98, 1, 1, '2025-09-22 06:14:44'),
(368, 99, 1, 1, '2025-09-22 06:14:44'),
(369, 100, 1, 1, '2025-09-27 04:34:10'),
(370, 101, 1, 1, '2025-09-27 04:34:10'),
(371, 102, 1, 1, '2025-09-27 04:34:10'),
(372, 103, 1, 1, '2025-09-27 04:34:10'),
(373, 104, 1, 1, '2025-09-27 04:34:10'),
(374, 105, 1, 1, '2025-09-27 04:34:10'),
(376, 106, 1, 1, '2025-09-27 04:47:02'),
(377, 107, 1, 1, '2025-09-27 04:47:02'),
(378, 108, 1, 1, '2025-09-27 04:47:02'),
(379, 109, 1, 1, '2025-09-27 04:48:50'),
(380, 110, 1, 1, '2025-09-27 04:48:50'),
(381, 111, 1, 1, '2025-09-27 04:48:50'),
(382, 90, 10, 1, '2025-09-27 08:35:03'),
(383, 91, 10, 1, '2025-09-27 08:35:03'),
(384, 92, 10, 1, '2025-09-27 08:35:03'),
(385, 93, 10, 1, '2025-09-27 08:35:03'),
(386, 94, 10, 1, '2025-09-27 08:35:03'),
(387, 95, 10, 1, '2025-09-27 08:35:03'),
(388, 96, 10, 1, '2025-09-27 08:35:03'),
(389, 97, 10, 1, '2025-09-27 08:35:03'),
(390, 98, 10, 1, '2025-09-27 08:35:03'),
(391, 99, 10, 1, '2025-09-27 08:35:03'),
(392, 100, 10, 1, '2025-09-27 08:35:03'),
(393, 101, 10, 1, '2025-09-27 08:35:03'),
(394, 102, 10, 1, '2025-09-27 08:35:03'),
(395, 103, 10, 1, '2025-09-27 08:35:03'),
(396, 104, 10, 1, '2025-09-27 08:35:03'),
(397, 105, 10, 1, '2025-09-27 08:35:03'),
(398, 106, 10, 1, '2025-09-27 08:35:03'),
(399, 107, 10, 1, '2025-09-27 08:35:03'),
(400, 108, 10, 1, '2025-09-27 08:35:03'),
(401, 109, 10, 1, '2025-09-27 08:35:03'),
(402, 110, 10, 1, '2025-09-27 08:35:03'),
(403, 111, 10, 1, '2025-09-27 08:35:03'),
(413, 112, 1, 1, '2025-09-27 09:26:33'),
(414, 113, 1, 1, '2025-09-27 09:26:33'),
(415, 114, 1, 1, '2025-09-27 09:26:33'),
(416, 60, 3, 1, '2025-09-28 13:07:56'),
(417, 61, 3, 1, '2025-09-28 13:07:56'),
(418, 62, 3, 1, '2025-09-28 13:07:56'),
(419, 63, 3, 1, '2025-09-28 13:07:56'),
(420, 64, 3, 1, '2025-09-28 13:07:56'),
(421, 65, 3, 1, '2025-09-28 13:07:56'),
(422, 66, 3, 1, '2025-09-28 13:07:56'),
(423, 67, 3, 1, '2025-09-28 13:07:56'),
(424, 68, 3, 1, '2025-09-28 13:07:56'),
(425, 69, 3, 1, '2025-09-28 13:07:56'),
(426, 70, 3, 1, '2025-09-28 13:07:56'),
(427, 71, 3, 1, '2025-09-28 13:07:56'),
(428, 72, 3, 1, '2025-09-28 13:07:56'),
(429, 73, 3, 1, '2025-09-28 13:07:56'),
(430, 74, 3, 1, '2025-09-28 13:07:56'),
(431, 75, 3, 1, '2025-09-28 13:07:56'),
(432, 76, 3, 1, '2025-09-28 13:07:56'),
(433, 77, 3, 1, '2025-09-28 13:07:56'),
(434, 78, 3, 1, '2025-09-28 13:07:56'),
(435, 79, 3, 1, '2025-09-28 13:07:56'),
(436, 80, 3, 1, '2025-09-28 13:07:56'),
(437, 81, 3, 1, '2025-09-28 13:07:56'),
(438, 82, 3, 1, '2025-09-28 13:07:56'),
(439, 83, 3, 1, '2025-09-28 13:07:56'),
(440, 84, 3, 1, '2025-09-28 13:07:56'),
(441, 85, 3, 1, '2025-09-28 13:07:56'),
(442, 86, 3, 1, '2025-09-28 13:07:56'),
(443, 87, 3, 1, '2025-09-28 13:07:56'),
(444, 88, 3, 1, '2025-09-28 13:07:56'),
(445, 89, 3, 1, '2025-09-28 13:07:56'),
(446, 90, 3, 1, '2025-09-28 13:07:56'),
(447, 91, 3, 1, '2025-09-28 13:07:56'),
(448, 92, 3, 1, '2025-09-28 13:07:56'),
(449, 93, 3, 1, '2025-09-28 13:07:56'),
(450, 94, 3, 1, '2025-09-28 13:07:56'),
(451, 95, 3, 1, '2025-09-28 13:07:56'),
(452, 96, 3, 1, '2025-09-28 13:07:56'),
(453, 97, 3, 1, '2025-09-28 13:07:56'),
(454, 98, 3, 1, '2025-09-28 13:07:56'),
(455, 99, 3, 1, '2025-09-28 13:07:56'),
(456, 100, 3, 1, '2025-09-28 13:07:56'),
(457, 101, 3, 1, '2025-09-28 13:07:56'),
(458, 102, 3, 1, '2025-09-28 13:07:56'),
(459, 103, 3, 1, '2025-09-28 13:07:56'),
(460, 104, 3, 1, '2025-09-28 13:07:56'),
(461, 105, 3, 1, '2025-09-28 13:07:56'),
(462, 106, 3, 1, '2025-09-28 13:07:56'),
(463, 107, 3, 1, '2025-09-28 13:07:56'),
(464, 108, 3, 1, '2025-09-28 13:07:56'),
(465, 109, 3, 1, '2025-09-28 13:07:56'),
(466, 110, 3, 1, '2025-09-28 13:07:56'),
(467, 111, 3, 1, '2025-09-28 13:07:56'),
(468, 112, 3, 1, '2025-09-28 13:07:56'),
(469, 113, 3, 1, '2025-09-28 13:07:56'),
(470, 114, 3, 1, '2025-09-28 13:07:56'),
(471, 115, 3, 1, '2025-09-28 13:07:56'),
(472, 116, 3, 1, '2025-09-28 13:07:56'),
(473, 117, 3, 1, '2025-09-28 13:07:56'),
(474, 115, 1, 1, '2025-09-29 17:14:02'),
(475, 116, 1, 1, '2025-09-29 17:14:02'),
(476, 117, 1, 1, '2025-09-29 17:14:02'),
(477, 118, 1, 1, '2025-10-02 10:24:50'),
(478, 119, 1, 1, '2025-10-02 10:24:50'),
(479, 120, 1, 1, '2025-10-02 10:24:50'),
(480, 121, 1, 1, '2025-10-02 10:24:50'),
(481, 122, 1, 1, '2025-10-02 10:24:50'),
(482, 123, 1, 1, '2025-10-02 10:24:50'),
(483, 124, 1, 1, '2025-10-02 10:24:50'),
(484, 125, 1, 1, '2025-10-02 10:24:50'),
(485, 126, 1, 1, '2025-10-02 10:24:50'),
(486, 127, 1, 1, '2025-10-02 10:24:50'),
(487, 128, 1, 1, '2025-10-02 10:24:50'),
(488, 129, 1, 1, '2025-10-02 10:24:50'),
(489, 130, 1, 1, '2025-10-02 10:24:50'),
(490, 131, 1, 1, '2025-10-02 10:24:50'),
(491, 132, 1, 1, '2025-10-02 10:24:50'),
(492, 133, 1, 1, '2025-10-02 10:24:50'),
(493, 134, 1, 1, '2025-10-02 10:24:50'),
(494, 135, 1, 1, '2025-10-02 10:24:50'),
(495, 136, 1, 1, '2025-10-02 10:24:50'),
(496, 137, 1, 1, '2025-10-02 10:24:50'),
(497, 138, 1, 1, '2025-10-02 10:24:50'),
(498, 139, 1, 1, '2025-10-02 10:24:50'),
(499, 140, 1, 1, '2025-10-02 10:24:50'),
(500, 141, 1, 1, '2025-10-02 10:24:50'),
(501, 142, 1, 1, '2025-10-02 10:24:50'),
(502, 143, 1, 1, '2025-10-02 10:24:50'),
(508, 144, 1, 1, '2025-10-02 10:38:35'),
(509, 145, 1, 1, '2025-10-02 12:33:02'),
(510, 146, 1, 1, '2025-10-02 12:33:02'),
(511, 147, 1, 1, '2025-10-02 12:33:02'),
(512, 112, 10, 1, '2025-10-02 14:08:59'),
(513, 113, 10, 1, '2025-10-02 14:08:59'),
(514, 114, 10, 1, '2025-10-02 14:08:59'),
(515, 115, 10, 1, '2025-10-02 14:08:59'),
(516, 116, 10, 1, '2025-10-02 14:08:59'),
(517, 117, 10, 1, '2025-10-02 14:08:59'),
(518, 118, 10, 1, '2025-10-02 14:08:59'),
(519, 119, 10, 1, '2025-10-02 14:08:59'),
(520, 120, 10, 1, '2025-10-02 14:08:59'),
(521, 121, 10, 1, '2025-10-02 14:08:59'),
(522, 122, 10, 1, '2025-10-02 14:08:59'),
(523, 123, 10, 1, '2025-10-02 14:08:59'),
(524, 124, 10, 1, '2025-10-02 14:08:59'),
(525, 125, 10, 1, '2025-10-02 14:08:59'),
(526, 126, 10, 1, '2025-10-02 14:08:59'),
(527, 127, 10, 1, '2025-10-02 14:08:59'),
(528, 128, 10, 1, '2025-10-02 14:08:59'),
(529, 129, 10, 1, '2025-10-02 14:08:59'),
(530, 130, 10, 1, '2025-10-02 14:08:59'),
(531, 131, 10, 1, '2025-10-02 14:08:59'),
(532, 132, 10, 1, '2025-10-02 14:08:59'),
(533, 133, 10, 1, '2025-10-02 14:08:59'),
(534, 134, 10, 1, '2025-10-02 14:08:59'),
(535, 135, 10, 1, '2025-10-02 14:08:59'),
(536, 136, 10, 1, '2025-10-02 14:08:59'),
(537, 137, 10, 1, '2025-10-02 14:08:59'),
(538, 138, 10, 1, '2025-10-02 14:08:59'),
(539, 139, 10, 1, '2025-10-02 14:08:59'),
(540, 140, 10, 1, '2025-10-02 14:08:59'),
(541, 141, 10, 1, '2025-10-02 14:08:59'),
(542, 142, 10, 1, '2025-10-02 14:08:59'),
(543, 143, 10, 1, '2025-10-02 14:08:59'),
(544, 144, 10, 1, '2025-10-02 14:08:59'),
(545, 145, 10, 1, '2025-10-02 14:08:59'),
(546, 146, 10, 1, '2025-10-02 14:08:59'),
(547, 147, 10, 1, '2025-10-02 14:08:59'),
(548, 148, 10, 1, '2025-10-02 14:08:59'),
(549, 148, 1, 1, '2025-10-10 13:25:50'),
(550, 149, 1, 1, '2025-10-10 13:25:50'),
(551, 150, 1, 1, '2025-10-10 13:25:50'),
(552, 151, 1, 1, '2025-10-10 13:25:50'),
(553, 152, 1, 1, '2025-10-10 13:25:50'),
(554, 153, 1, 1, '2025-10-10 13:25:50'),
(555, 154, 1, 1, '2025-10-10 13:25:50'),
(556, 155, 1, 1, '2025-10-10 13:25:50'),
(557, 156, 1, 1, '2025-10-10 13:25:50'),
(558, 157, 1, 1, '2025-10-10 13:25:50'),
(559, 158, 1, 1, '2025-10-10 13:25:50'),
(560, 159, 1, 1, '2025-10-10 13:25:50'),
(561, 160, 1, 1, '2025-10-10 13:25:50'),
(562, 161, 1, 1, '2025-10-10 13:25:50'),
(563, 162, 1, 1, '2025-10-10 13:25:50'),
(564, 163, 1, 1, '2025-10-10 13:25:50'),
(565, 164, 1, 1, '2025-10-10 13:25:50'),
(566, 165, 1, 1, '2025-10-10 13:25:50'),
(567, 166, 1, 1, '2025-10-10 13:25:50'),
(568, 167, 1, 1, '2025-10-10 13:25:50'),
(569, 168, 1, 1, '2025-10-10 13:25:50'),
(570, 169, 1, 1, '2025-10-10 13:25:50'),
(580, 170, 1, 1, '2025-10-10 15:54:54'),
(581, 171, 1, 1, '2025-10-10 15:54:54'),
(582, 172, 1, 1, '2025-10-10 15:54:54'),
(583, 173, 1, 1, '2025-10-10 15:54:54'),
(587, 174, 1, 1, '2025-10-10 15:55:45'),
(588, 175, 1, 1, '2025-10-10 16:00:06'),
(589, 176, 1, 1, '2025-10-10 16:00:06'),
(590, 177, 1, 1, '2025-10-14 14:44:33'),
(591, 178, 1, 1, '2025-10-14 14:44:33'),
(592, 179, 1, 1, '2025-10-14 14:44:33'),
(593, 180, 1, 1, '2025-10-14 15:00:04'),
(594, 181, 1, 1, '2025-10-14 15:00:04'),
(595, 182, 1, 1, '2025-10-14 15:00:04'),
(596, 183, 1, 1, '2025-10-14 15:00:04'),
(597, 184, 1, 1, '2025-10-14 15:00:04'),
(598, 185, 1, 1, '2025-10-14 15:00:04'),
(599, 186, 1, 1, '2025-10-14 15:00:04'),
(600, 187, 1, 1, '2025-10-14 15:00:04'),
(601, 188, 1, 1, '2025-10-14 15:00:04'),
(602, 189, 1, 1, '2025-10-14 15:00:04'),
(603, 190, 1, 1, '2025-10-14 15:00:04'),
(604, 191, 1, 1, '2025-10-14 15:00:04'),
(605, 192, 1, 1, '2025-10-14 15:00:04'),
(606, 193, 1, 1, '2025-10-14 15:00:04');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thuonghieu`
--

CREATE TABLE `thuonghieu` (
  `id` int(11) NOT NULL,
  `tenthuonghieu` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `thuonghieu`
--

INSERT INTO `thuonghieu` (`id`, `tenthuonghieu`) VALUES
(1, 'Thế giới nông sản'),
(2, '3SachFood'),
(3, 'Organica'),
(4, 'BigGreen'),
(5, 'Rau Bác Tôm'),
(6, 'Việt Nam ');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tinnhan`
--

CREATE TABLE `tinnhan` (
  `id` int(11) NOT NULL,
  `nguoigui_id` int(11) NOT NULL,
  `nguoinhan_id` int(11) NOT NULL,
  `noidung` text NOT NULL,
  `thoigian` datetime DEFAULT current_timestamp(),
  `trangthai` enum('chua_doc','da_doc') DEFAULT 'chua_doc'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tinnhan`
--

INSERT INTO `tinnhan` (`id`, `nguoigui_id`, `nguoinhan_id`, `noidung`, `thoigian`, `trangthai`) VALUES
(106, 3, 1, 'Hi', '2025-09-28 20:05:37', 'da_doc'),
(107, 1, 3, 'Chúng tôi có thể giúp gì cho bạn', '2025-09-28 20:06:29', 'da_doc'),
(108, 3, 1, 'tôi đói qua bạn', '2025-09-28 20:07:42', 'da_doc'),
(109, 10, 1, 'alo admin', '2025-10-02 19:34:43', 'chua_doc'),
(110, 10, 1, 'Tại sao tôi chưa nhận đc hàng mà đã thanh toán rồi hả', '2025-10-02 19:35:01', 'chua_doc'),
(111, 3, 1, 'alo admin', '2025-10-02 19:36:00', 'da_doc'),
(112, 3, 1, 'Sao tôi chưa nhận đc hàng mà đã hoàn tất rồi', '2025-10-02 19:36:15', 'da_doc'),
(113, 1, 3, 'chắc có sơ xuất trong việc gửi hàng rồi', '2025-10-02 19:36:52', 'da_doc'),
(114, 1, 3, 'chúng tôi sẽ kiểm tra và cho bạn kết quả sớm nhất', '2025-10-02 19:37:13', 'da_doc'),
(115, 3, 1, 'Allooo', '2025-10-07 13:34:52', 'da_doc'),
(116, 1, 3, 'hello', '2025-10-07 13:35:05', 'da_doc'),
(117, 3, 1, 'toi dói quá bạn', '2025-10-07 13:35:31', 'da_doc'),
(118, 3, 1, 'Hell admin ơi', '2025-10-10 20:17:35', 'da_doc'),
(119, 1, 3, 'Dạ bên mình có thể giúp dc gì cho bạn ạ', '2025-10-10 20:20:04', 'da_doc'),
(120, 4, 1, 'Hi', '2025-10-15 13:23:48', 'chua_doc');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `danhgia`
--
ALTER TABLE `danhgia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_sanpham` (`id_sanpham`),
  ADD KEY `fk_danhgia_user` (`id_nguoidung`);

--
-- Chỉ mục cho bảng `diachi`
--
ALTER TABLE `diachi`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `donhang`
--
ALTER TABLE `donhang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_nguoidung` (`id_nguoidung`),
  ADD KEY `id_sanpham` (`id_sanpham`);

--
-- Chỉ mục cho bảng `giohang`
--
ALTER TABLE `giohang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `taikhoan_id` (`taikhoan_id`),
  ADD KEY `sanpham_id` (`sanpham_id`);

--
-- Chỉ mục cho bảng `khuyenmai`
--
ALTER TABLE `khuyenmai`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sanpham_id` (`sanpham_id`);

--
-- Chỉ mục cho bảng `lichsugiaodich`
--
ALTER TABLE `lichsugiaodich`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_donhang` (`id_donhang`),
  ADD KEY `id_nguoidung` (`id_nguoidung`),
  ADD KEY `id_sanpham` (`id_sanpham`);

--
-- Chỉ mục cho bảng `lichsu_diem`
--
ALTER TABLE `lichsu_diem`
  ADD PRIMARY KEY (`id`),
  ADD KEY `taikhoan_id` (`taikhoan_id`),
  ADD KEY `fk_lichsu_diem_donhang` (`id_donhang`);

--
-- Chỉ mục cho bảng `lienhe`
--
ALTER TABLE `lienhe`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`id_item`,`user_id`);

--
-- Chỉ mục cho bảng `loaisanpham`
--
ALTER TABLE `loaisanpham`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `phanhoi_review`
--
ALTER TABLE `phanhoi_review`
  ADD PRIMARY KEY (`id`),
  ADD KEY `review_id` (`review_id`);

--
-- Chỉ mục cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_loai` (`id_loai`),
  ADD KEY `id_thuonghieu` (`id_thuonghieu`);

--
-- Chỉ mục cho bảng `taikhoan`
--
ALTER TABLE `taikhoan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `thongbao`
--
ALTER TABLE `thongbao`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `thongbao_read`
--
ALTER TABLE `thongbao_read`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `thongbao_id` (`thongbao_id`,`user_id`);

--
-- Chỉ mục cho bảng `thuonghieu`
--
ALTER TABLE `thuonghieu`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `tinnhan`
--
ALTER TABLE `tinnhan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nguoigui_id` (`nguoigui_id`),
  ADD KEY `nguoinhan_id` (`nguoinhan_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `danhgia`
--
ALTER TABLE `danhgia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT cho bảng `diachi`
--
ALTER TABLE `diachi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `donhang`
--
ALTER TABLE `donhang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=524;

--
-- AUTO_INCREMENT cho bảng `giohang`
--
ALTER TABLE `giohang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=380;

--
-- AUTO_INCREMENT cho bảng `khuyenmai`
--
ALTER TABLE `khuyenmai`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT cho bảng `lichsugiaodich`
--
ALTER TABLE `lichsugiaodich`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=232;

--
-- AUTO_INCREMENT cho bảng `lichsu_diem`
--
ALTER TABLE `lichsu_diem`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=190;

--
-- AUTO_INCREMENT cho bảng `lienhe`
--
ALTER TABLE `lienhe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT cho bảng `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT cho bảng `loaisanpham`
--
ALTER TABLE `loaisanpham`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `phanhoi_review`
--
ALTER TABLE `phanhoi_review`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT cho bảng `taikhoan`
--
ALTER TABLE `taikhoan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `thongbao`
--
ALTER TABLE `thongbao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=208;

--
-- AUTO_INCREMENT cho bảng `thongbao_read`
--
ALTER TABLE `thongbao_read`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=607;

--
-- AUTO_INCREMENT cho bảng `thuonghieu`
--
ALTER TABLE `thuonghieu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `tinnhan`
--
ALTER TABLE `tinnhan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `danhgia`
--
ALTER TABLE `danhgia`
  ADD CONSTRAINT `danhgia_ibfk_1` FOREIGN KEY (`id_sanpham`) REFERENCES `sanpham` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_danhgia_user` FOREIGN KEY (`id_nguoidung`) REFERENCES `taikhoan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `donhang`
--
ALTER TABLE `donhang`
  ADD CONSTRAINT `donhang_ibfk_1` FOREIGN KEY (`id_nguoidung`) REFERENCES `taikhoan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `donhang_ibfk_2` FOREIGN KEY (`id_sanpham`) REFERENCES `sanpham` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `giohang`
--
ALTER TABLE `giohang`
  ADD CONSTRAINT `giohang_ibfk_1` FOREIGN KEY (`taikhoan_id`) REFERENCES `taikhoan` (`id`),
  ADD CONSTRAINT `giohang_ibfk_2` FOREIGN KEY (`sanpham_id`) REFERENCES `sanpham` (`id`);

--
-- Các ràng buộc cho bảng `khuyenmai`
--
ALTER TABLE `khuyenmai`
  ADD CONSTRAINT `khuyenmai_ibfk_1` FOREIGN KEY (`sanpham_id`) REFERENCES `sanpham` (`id`);

--
-- Các ràng buộc cho bảng `lichsugiaodich`
--
ALTER TABLE `lichsugiaodich`
  ADD CONSTRAINT `lichsugiaodich_ibfk_1` FOREIGN KEY (`id_donhang`) REFERENCES `donhang` (`id`),
  ADD CONSTRAINT `lichsugiaodich_ibfk_2` FOREIGN KEY (`id_nguoidung`) REFERENCES `taikhoan` (`id`),
  ADD CONSTRAINT `lichsugiaodich_ibfk_3` FOREIGN KEY (`id_sanpham`) REFERENCES `sanpham` (`id`);

--
-- Các ràng buộc cho bảng `lichsu_diem`
--
ALTER TABLE `lichsu_diem`
  ADD CONSTRAINT `fk_lichsu_diem_donhang` FOREIGN KEY (`id_donhang`) REFERENCES `donhang` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `lichsu_diem_ibfk_1` FOREIGN KEY (`taikhoan_id`) REFERENCES `taikhoan` (`id`);

--
-- Các ràng buộc cho bảng `phanhoi_review`
--
ALTER TABLE `phanhoi_review`
  ADD CONSTRAINT `phanhoi_review_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `danhgia` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  ADD CONSTRAINT `sanpham_ibfk_1` FOREIGN KEY (`id_loai`) REFERENCES `loaisanpham` (`id`),
  ADD CONSTRAINT `sanpham_ibfk_2` FOREIGN KEY (`id_thuonghieu`) REFERENCES `thuonghieu` (`id`);

--
-- Các ràng buộc cho bảng `tinnhan`
--
ALTER TABLE `tinnhan`
  ADD CONSTRAINT `fk_tinnhan_nguoigui` FOREIGN KEY (`nguoigui_id`) REFERENCES `taikhoan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tinnhan_nguoinhan` FOREIGN KEY (`nguoinhan_id`) REFERENCES `taikhoan` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
