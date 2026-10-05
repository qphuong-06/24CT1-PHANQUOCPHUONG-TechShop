<?php

session_start();

require_once "../config/database.php";


/* =========================
   KIỂM TRA QUYỀN
========================= */

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


/* =========================
   LẤY ID ĐƠN HÀNG
========================= */

$order_id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;


if ($order_id <= 0) {

    header("Location: don-hang.php");

    exit;
}


/* =========================
   CẬP NHẬT
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $status =
        $_POST["status"] ?? "pending";


    $payment_status =
        $_POST["payment_status"] ?? "unpaid";


    $allowed_status = [

        "pending",
        "confirmed",
        "shipping",
        "completed",
        "cancelled"

    ];


    $allowed_payment = [

        "unpaid",
        "paid"

    ];


    if (
        !in_array(
            $status,
            $allowed_status,
            true
        )
    ) {

        die("Trạng thái đơn hàng không hợp lệ.");
    }


    if (
        !in_array(
            $payment_status,
            $allowed_payment,
            true
        )
    ) {

        die("Trạng thái thanh toán không hợp lệ.");
    }


    $stmt = $conn->prepare(
        "UPDATE orders
         SET status = ?,
             payment_status = ?
         WHERE id = ?"
    );


    $stmt->bind_param(
        "ssi",
        $status,
        $payment_status,
        $order_id
    );


    $stmt->execute();

    $stmt->close();


    header(
        "Location: cap-nhat-don.php?id="
        . $order_id
        . "&success=1"
    );

    exit;
}


/* =========================
   LẤY ĐƠN HÀNG
========================= */

$stmt = $conn->prepare(
    "SELECT *
     FROM orders
     WHERE id = ?"
);


$stmt->bind_param(
    "i",
    $order_id
);


$stmt->execute();


$result = $stmt->get_result();


$order = $result->fetch_assoc();


$stmt->close();


if (!$order) {

    die("Không tìm thấy đơn hàng.");
}


?>

<!DOCTYPE html>

<html lang="vi">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
Cập nhật đơn hàng #DH<?= $order["id"] ?>
</title>


<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
>


<style>

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    font-family:
        "Segoe UI",
        Arial,
        sans-serif;

    background:
        radial-gradient(
            circle at top right,
            #18243d,
            #080b12 50%
        );

    color: white;

    min-height: 100vh;
}


/* SIDEBAR */

.sidebar {

    position: fixed;

    left: 0;
    top: 0;

    width: 250px;

    height: 100vh;

    background:
        rgba(8,12,22,0.97);

    border-right:
        1px solid rgba(255,255,255,0.08);

    padding: 25px 18px;
}


.logo {

    font-size: 24px;

    font-weight: 800;

    margin-bottom: 35px;
}


.logo span {
    color: #00d4ff;
}


.menu-title {

    color: #727d90;

    font-size: 12px;

    text-transform: uppercase;

    margin:
        20px 10px 10px;
}


.menu a {

    display: flex;

    align-items: center;

    gap: 12px;

    text-decoration: none;

    color: #aeb7c7;

    padding:
        12px 14px;

    border-radius: 12px;

    margin-bottom: 6px;
}


.menu a:hover,
.menu a.active {

    color: white;

    background:
        linear-gradient(
            135deg,
            #0066ff,
            #00b7ff
        );
}


/* MAIN */

.main {

    margin-left: 250px;

    padding: 35px;

    max-width: 1200px;
}


.topbar {

    display: flex;

    justify-content:
        space-between;

    align-items: center;

    margin-bottom: 30px;
}


.topbar h1 {

    margin: 0;

    font-size: 28px;

    font-weight: 800;
}


.back {

    color: #9aa4b2;

    text-decoration: none;
}


.back:hover {

    color: #00d4ff;
}


/* CARD */

.card-box {

    background:
        rgba(255,255,255,0.055);

    border:
        1px solid rgba(255,255,255,0.08);

    border-radius: 20px;

    padding: 25px;

    margin-bottom: 20px;

    backdrop-filter: blur(15px);
}


.card-title {

    font-size: 18px;

    font-weight: 800;

    margin-bottom: 20px;
}


/* INFO */

.info-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 18px;
}


.info {

    background:
        rgba(255,255,255,0.04);

    padding: 17px;

    border-radius: 13px;
}


.info-label {

    color: #7f8a9d;

    font-size: 12px;

    margin-bottom: 5px;
}


.info-value {

    font-weight: 700;
}


/* FORM */

.form-label {

    color: #b7c0cf;

    font-weight: 600;
}


.form-select {

    background-color: #111827;

    border:
        1px solid rgba(255,255,255,0.12);

    color: white;

    padding: 13px;

    border-radius: 12px;
}


.form-select:focus {

    background-color: #111827;

    color: white;

    border-color: #00d4ff;

    box-shadow: none;
}


.form-select option {

    background: #111827;

    color: white;
}


/* BUTTON */

.btn-save {

    border: 0;

    padding:
        13px 22px;

    border-radius: 12px;

    background:
        linear-gradient(
            135deg,
            #0066ff,
            #00c6ff
        );

    color: white;

    font-weight: 800;

    transition: 0.2s;
}


.btn-save:hover {

    transform:
        translateY(-2px);

    box-shadow:
        0 10px 25px
        rgba(0,198,255,0.2);
}


/* SUCCESS */

.success {

    background:
        rgba(34,197,94,0.12);

    border:
        1px solid rgba(34,197,94,0.25);

    color: #4ade80;

    padding: 14px 16px;

    border-radius: 12px;

    margin-bottom: 20px;
}


/* RESPONSIVE */

@media (max-width: 768px) {

    .sidebar {

        position: static;

        width: 100%;

        height: auto;
    }


    .main {

        margin-left: 0;

        padding: 20px;
    }


    .info-grid {

        grid-template-columns: 1fr;
    }


    .topbar {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;
    }
}

</style>

</head>

<body>


<!-- SIDEBAR -->

<aside class="sidebar">


    <div class="logo">

        <i class="bi bi-cpu"></i>

        Tech<span>Shop</span>

    </div>


    <div class="menu-title">
        Quản lý
    </div>


    <div class="menu">

        <a href="index.php">

            <i class="bi bi-grid-1x2-fill"></i>

            Tổng quan

        </a>


        <a href="san-pham.php">

            <i class="bi bi-box-seam-fill"></i>

            Sản phẩm

        </a>


        <a href="them-san-pham.php">

            <i class="bi bi-plus-circle-fill"></i>

            Thêm sản phẩm

        </a>


        <a href="don-hang.php" class="active">

            <i class="bi bi-receipt-cutoff"></i>

            Đơn hàng

        </a>


        <a href="tai-khoan.php">

            <i class="bi bi-people-fill"></i>

            Tài khoản

        </a>

    </div>


    <div class="menu-title">
        Hệ thống
    </div>


    <div class="menu">

        <a href="../index.php">

            <i class="bi bi-globe2"></i>

            Xem website

        </a>


        <a href="../dang-xuat.php">

            <i class="bi bi-box-arrow-right"></i>

            Đăng xuất

        </a>

    </div>

</aside>


<!-- MAIN -->

<main class="main">


    <div class="topbar">


        <div>

            <h1>
                Cập nhật đơn #DH<?= $order["id"] ?>
            </h1>

            <div style="color:#7f8a9d;">
                Quản lý trạng thái đơn hàng và thanh toán
            </div>

        </div>


        <a
            href="don-hang.php"
            class="back"
        >

            <i class="bi bi-arrow-left"></i>

            Quay lại đơn hàng

        </a>


    </div>


    <?php if (isset($_GET["success"])): ?>

        <div class="success">

            <i class="bi bi-check-circle-fill"></i>

            Cập nhật đơn hàng thành công.

        </div>

    <?php endif; ?>


    <!-- THÔNG TIN KHÁCH -->

    <div class="card-box">


        <div class="card-title">

            <i class="bi bi-person-circle"></i>

            Thông tin khách hàng

        </div>


        <div class="info-grid">


            <div class="info">

                <div class="info-label">
                    Họ và tên
                </div>

                <div class="info-value">

                    <?= htmlspecialchars(
                        $order["fullname"]
                    ) ?>

                </div>

            </div>


            <div class="info">

                <div class="info-label">
                    Số điện thoại
                </div>

                <div class="info-value">

                    <?= htmlspecialchars(
                        $order["phone"]
                    ) ?>

                </div>

            </div>


            <div class="info">

                <div class="info-label">
                    Địa chỉ
                </div>

                <div class="info-value">

                    <?= htmlspecialchars(
                        $order["address"]
                    ) ?>

                </div>

            </div>


            <div class="info">

                <div class="info-label">
                    Tổng tiền
                </div>

                <div
                    class="info-value"
                    style="color:#00d4ff;font-size:20px;"
                >

                    <?= number_format(
                        $order["total_amount"],
                        0,
                        ",",
                        "."
                    ) ?>

                    ₫

                </div>

            </div>


        </div>

    </div>


    <!-- THANH TOÁN -->

    <div class="card-box">


        <div class="card-title">

            <i class="bi bi-credit-card"></i>

            Thanh toán

        </div>


        <form method="POST">


            <div class="row g-4">


                <div class="col-md-6">


                    <label class="form-label">

                        Phương thức thanh toán

                    </label>


                    <div class="info" style="margin-top:8px;">

                        <?php if (
                            $order["payment_method"] === "bank"
                        ): ?>

                            <span
                                style="color:#60a5fa;font-weight:800;"
                            >

                                <i class="bi bi-bank"></i>

                                Chuyển khoản ngân hàng

                            </span>

                        <?php else: ?>

                            <span
                                style="color:#4ade80;font-weight:800;"
                            >

                                <i class="bi bi-cash-coin"></i>

                                Thanh toán COD

                            </span>

                        <?php endif; ?>

                    </div>


                </div>


                <div class="col-md-6">


                    <label
                        class="form-label"
                        for="payment_status"
                    >

                        Trạng thái thanh toán

                    </label>


                    <select
                        name="payment_status"
                        id="payment_status"
                        class="form-select"
                    >


                        <option
                            value="unpaid"
                            <?= $order["payment_status"] === "unpaid"
                                ? "selected"
                                : "" ?>
                        >

                            🔴 Chưa thanh toán

                        </option>


                        <option
                            value="paid"
                            <?= $order["payment_status"] === "paid"
                                ? "selected"
                                : "" ?>
                        >

                            🟢 Đã thanh toán

                        </option>


                    </select>


                </div>


            </div>


    </div>


    <!-- TRẠNG THÁI ĐƠN -->

    <div class="card-box">


        <div class="card-title">

            <i class="bi bi-box-seam"></i>

            Trạng thái đơn hàng

        </div>


        <label
            class="form-label"
            for="status"
        >

            Trạng thái hiện tại

        </label>


        <select
            name="status"
            id="status"
            class="form-select"
            style="max-width:500px;"
        >


            <option
                value="pending"
                <?= $order["status"] === "pending"
                    ? "selected"
                    : "" ?>
            >

                🟡 Chờ xác nhận

            </option>


            <option
                value="confirmed"
                <?= $order["status"] === "confirmed"
                    ? "selected"
                    : "" ?>
            >

                🔵 Đã xác nhận

            </option>


            <option
                value="shipping"
                <?= $order["status"] === "shipping"
                    ? "selected"
                    : "" ?>
            >

                🟣 Đang giao hàng

            </option>


            <option
                value="completed"
                <?= $order["status"] === "completed"
                    ? "selected"
                    : "" ?>
            >

                🟢 Hoàn thành

            </option>


            <option
                value="cancelled"
                <?= $order["status"] === "cancelled"
                    ? "selected"
                    : "" ?>
            >

                🔴 Đã hủy

            </option>


        </select>


        <div style="margin-top:25px;">

            <button
                type="submit"
                class="btn-save"
            >

                <i class="bi bi-check-lg"></i>

                Lưu thay đổi

            </button>

        </div>


        </form>


    </div>


</main>


</body>

</html>