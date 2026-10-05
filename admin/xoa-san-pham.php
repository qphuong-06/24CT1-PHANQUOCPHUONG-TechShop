<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../dang-nhap.php");
    exit;
}

if (
    $_SESSION["role"] !== "admin"
    && $_SESSION["role"] !== "manager"
) {
    die("Bạn không có quyền truy cập.");
}


$id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;


if ($id <= 0) {

    header("Location: san-pham.php");
    exit;
}


$stmt = $conn->prepare(
    "DELETE FROM products WHERE id = ?"
);

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$stmt->close();


header("Location: san-pham.php");

exit;