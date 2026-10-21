<?php
session_start();
include 'connect.php';

// 1. Xử lý thêm sản phẩm vào giỏ hàng
if (isset($_GET['action']) && $_GET['action'] == 'add') {
    $id = intval($_GET['id']);
    
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity'] += 1;
    } else {
        // Sửa từ products thành sanpham
        $sql = "SELECT * FROM sanpham WHERE id = $id";
        $result = mysqli_query($conn, $sql);
        if ($row = mysqli_fetch_assoc($result)) {
            $_SESSION['cart'][$id] = [
                'name' => $row['name'],
                'price' => $row['gia_sp'], // Sửa từ price thành gia_sp
                'image' => $row['image'],
                'quantity' => 1
            ];
        }
    }
    header("Location: giohang.php");
    exit();
}

// 2. Xử lý xóa 1 sản phẩm khỏi giỏ hàng
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = intval($_GET['id']);
    unset($_SESSION['cart'][$id]);
    header("Location: giohang.php");
    exit();
}

// 3. Xử lý cập nhật số lượng sản phẩm
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    foreach ($_POST['quantity'] as $id => $qty) {
        $qty = intval($qty);
        if ($qty <= 0) {
            unset($_SESSION['cart'][$id]);
        } else {
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id]['quantity'] = $qty;
            }
        }
    }
    header("Location: giohang.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giỏ hàng của bạn</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h2>🛒 Giỏ Hàng Của Bạn</h2>
        
        <?php if (!empty($_SESSION['cart'])): ?>
            <form action="giohang.php" method="POST">
                <table>
                    <thead>
                        <tr>
                            <th>Hình ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_price = 0;
                        foreach ($_SESSION['cart'] as $id => $item): 
                            $subtotal = $item['price'] * $item['quantity'];
                            $total_price += $subtotal;
                            $img = ltrim(str_replace('\\', '/', $item['image']), '/');
                        ?>
                        <tr>
                            <td>
                                <img src="<?php echo $img; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                            </td>
                            <td><?php echo $item['name']; ?></td>
                            <td><?php echo number_format($item['price']); ?> đ</td>
                            <td>
                                <input type="number" name="quantity[<?php echo $id; ?>]" value="<?php echo $item['quantity']; ?>" min="1" style="width: 60px; padding: 5px; text-align: center; border: 1px solid #ccc; border-radius: 4px;">
                            </td>
                            <td><strong><?php echo number_format($subtotal); ?> đ</strong></td>
                            <td>
                                <a href="giohang.php?action=delete&id=<?php echo $id; ?>" style="color: #e74c3c; text-decoration: none;" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">Xóa</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <button type="submit" name="update" class="btn" style="background: #f39c12; border: none; cursor: pointer;">Cập nhật giỏ hàng</button>
                        <a href="sach.php" class="btn" style="background: #7f8c8d; text-decoration: none;">Tiếp tục mua sắm</a>
                    </div>
                    <div style="font-size: 18px; font-weight: bold;">
                        Tổng thanh toán: <span style="color: #e74c3c;"><?php echo number_format($total_price); ?> đ</span>
                    </div>
                </div>
            </form>
            
            <div style="margin-top: 30px; text-align: right;">
                <a href="thanhtoan.php" class="btn" style="background: #27ae60; padding: 12px 25px; font-size: 16px; text-decoration: none;">Tiến hành đặt hàng</a>
            </div>
            
        <?php else: ?>
            <div style="background: white; padding: 40px; text-align: center; border-radius: 8px; margin-top: 20px;">
                <p style="font-size: 16px; color: #666; margin-bottom: 15px;">Giỏ hàng của bạn đang trống!</p>
                <a href="sach.php" class="btn" style="text-decoration: none;">Quay lại mua sách ngay</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>