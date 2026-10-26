<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bookstore";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    error_log("BookStore database connection error: " . $e->getMessage());
    http_response_code(500);
    exit("Không thể kết nối cơ sở dữ liệu. Vui lòng kiểm tra cấu hình MySQL.");
}
?>
