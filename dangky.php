<?php
include 'connect.php';
session_start();

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Vui lòng điền đầy đủ thông tin!";
    } elseif ($password !== $confirm_password) {
        $error = "Mật khẩu nhập lại không khớp!";
    } else {
        // Kiểm tra xem tên đăng nhập đã tồn tại trong database chưa
        $check_sql = "SELECT * FROM users WHERE username = '$username'";
        $check_result = mysqli_query($conn, $check_sql);

        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $error = "Tên đăng nhập này đã tồn tại, vui lòng chọn tên khác!";
        } else {
            // Thêm tài khoản mới với phân quyền mặc định là 'user'
            $role = 'user';
            $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
            
            if (mysqli_query($conn, $sql)) {
                $success = "Đăng ký thành công! Bạn có thể <a href='dangnhap.php' style='color: #0056b3; font-weight: bold;'>đăng nhập ngay</a>.";
            } else {
                $error = "Đã có lỗi xảy ra, vui lòng thử lại sau!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="form-box">
        <h2>Đăng Ký Tài Khoản</h2>
        
        <?php if (!empty($error)): ?>
            <p style="color: #e74c3c; margin-bottom: 15px; text-align: center; font-weight: 500;"><?php echo $error; ?></p>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div style="background: #e8f8f5; color: #27ae60; padding: 12px; border-radius: 4px; margin-bottom: 15px; text-align: center; border: 1px solid #a3e4d7;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Tên đăng nhập:</label>
                <input type="text" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            <div class="form-group">
                <label>Mật khẩu:</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Nhập lại mật khẩu:</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn-submit">Đăng ký</button>
        </form>

        <p style="text-align: center; margin-top: 20px; font-size: 14px; color: #666;">
            Đã có tài khoản? <a href="dangnhap.php" style="color: #0056b3; text-decoration: none; font-weight: 500;">Đăng nhập ngay</a>
        </p>
    </div>
</body>
</html>