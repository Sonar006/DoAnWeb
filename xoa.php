<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'connect.php';
if (($_SESSION['role'] ?? '') !== 'admin') { header('Location: dangnhap.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: admin.php'); exit; }
$token = (string)($_SESSION['csrf_token'] ?? '');
$postedToken = (string)($_POST['csrf_token'] ?? '');
if ($token === '' || !hash_equals($token, $postedToken)) { header('Location: admin.php?msg=csrf'); exit; }
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) { header('Location: admin.php?msg=notfound'); exit; }
$stmt = $conn->prepare('DELETE FROM sanpham WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
header('Location: admin.php?msg=' . ($stmt->affected_rows > 0 ? 'deleted' : 'notfound'));
exit;
