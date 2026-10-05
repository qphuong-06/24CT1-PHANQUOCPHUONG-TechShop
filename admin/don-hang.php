<?php

session_start();

require_once "../config/database.php";


/* =====================================================
   KIỂM TRA ĐĂNG NHẬP
===================================================== */

if (!isset($_SESSION["user_id"])) {

    header("Location: ../dang-nhap.php");

    exit;
}


if (
    $_SESSION["role"] !== "admin"
    && $_SESSION["role"] !== "manager"
) {

    die("Bạn không có quyền truy cập trang quản trị.");
}


/* =====================================================
   LẤY DANH SÁCH ĐƠN HÀNG
===================================================== */

$sql = "
    SELECT *
    FROM orders
    ORDER BY id DESC
";

$result = $conn->query($sql);


if (!$result) {

    die(
        "Lỗi truy vấn đơn hàng: "
        . $conn->error
    );
}


/* =====================================================
   THỐNG KÊ
===================================================== */

$totalOrders = 0;

$pendingOrders = 0;

$paidOrders = 0;

$unpaidOrders = 0;

$orders = [];


while ($row = $result->fetch_assoc()) {

    $orders[] = $row;

    $totalOrders++;


    if ($row["status"] === "pending") {

        $pendingOrders++;
    }


    if (
        isset($row["payment_status"])
        && $row["payment_status"] === "paid"
    ) {

        $paidOrders++;

    } else {

        $unpaidOrders++;
    }
}


/* =====================================================
   HÀM TRẠNG THÁI ĐƠN HÀNG
===================================================== */

function getOrderStatus($status)
{

    switch ($status) {

        case "pending":

            return [
                "label" => "Chờ xác nhận",
                "class" => "pending"
            ];


        case "confirmed":

            return [
                "label" => "Đã xác nhận",
                "class" => "confirmed"
            ];


        case "shipping":

            return [
                "label" => "Đang giao",
                "class" => "shipping"
            ];


        case "completed":

            return [
                "label" => "Hoàn thành",
                "class" => "completed"
            ];


        case "cancelled":

            return [
                "label" => "Đã hủy",
                "class" => "cancelled"
            ];


        default:

            return [
                "label" => "Không xác định",
                "class" => "pending"
            ];
    }
}


/* =====================================================
   HÀM PHƯƠNG THỨC THANH TOÁN
===================================================== */

function getPaymentMethod($method)
{

    if ($method === "bank") {

        return [
            "label" => "Chuyển khoản",
            "icon" => "bi-bank",
            "class" => "bank"
        ];
    }


    return [
        "label" => "COD",
        "icon" => "bi-cash-coin",
        "class" => "cod"
    ];
}


/* =====================================================
   HÀM TRẠNG THÁI THANH TOÁN
===================================================== */

function getPaymentStatus($status)
{

    if ($status === "paid") {

        return [
            "label" => "Đã thanh toán",
            "class" => "paid",
            "icon" => "bi-check-circle-fill"
        ];
    }


    return [
        "label" => "Chưa thanh toán",
        "class" => "unpaid",
        "icon" => "bi-clock-fill"
    ];
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
Quản lý đơn hàng - TechShop
</title>


<!-- BOOTSTRAP -->

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


<!-- BOOTSTRAP ICONS -->

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
>


<style>

/* =====================================================
   RESET
===================================================== */

* {

    box-sizing: border-box;
}


html {

    background: #080b12;
}


body {

    margin: 0;

    padding: 0;

    font-family:
        "Segoe UI",
        Arial,
        sans-serif;

    background:
        radial-gradient(
            circle at top right,
            #18243d 0%,
            #080b12 48%
        );

    color: #ffffff;

    min-height: 100vh;
}


/* =====================================================
   SIDEBAR
===================================================== */

.sidebar {

    position: fixed;

    left: 0;

    top: 0;

    width: 250px;

    height: 100vh;

    background:
        rgba(8, 12, 22, 0.97);

    border-right:
        1px solid rgba(255,255,255,0.08);

    padding:
        25px 18px;

    z-index: 1000;

    overflow-y: auto;
}


/* LOGO */

.logo {

    font-size: 24px;

    font-weight: 800;

    margin-bottom: 35px;

    color: #ffffff;
}


.logo i {

    margin-right: 5px;

    color: #00d4ff;
}


.logo span {

    color: #00d4ff;
}


/* MENU TITLE */

.menu-title {

    font-size: 12px;

    color: #727d90;

    text-transform: uppercase;

    margin:
        22px 10px 10px;
}


/* MENU */

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

    transition:
        background 0.2s ease,
        color 0.2s ease,
        transform 0.2s ease;
}


.menu a i {

    width: 20px;

    text-align: center;
}


.menu a:hover {

    background:
        rgba(255,255,255,0.06);

    color: #ffffff;

    transform:
        translateX(2px);
}


.menu a.active {

    background:
        linear-gradient(
            135deg,
            #0066ff,
            #00b7ff
        );

    color: #ffffff;

    box-shadow:
        0 8px 20px
        rgba(0,149,255,0.18);
}


/* =====================================================
   MAIN
===================================================== */

.main {

    margin-left: 250px;

    padding: 30px;

    min-height: 100vh;
}


/* =====================================================
   TOPBAR
===================================================== */

.topbar {

    display: flex;

    justify-content:
        space-between;

    align-items: center;

    gap: 20px;

    margin-bottom: 30px;
}


.topbar h1 {

    font-size: 28px;

    font-weight: 800;

    margin: 0;
}


.topbar-subtitle {

    color: #7f8a9d;

    margin-top: 6px;

    font-size: 14px;
}


.user {

    display: flex;

    align-items: center;

    gap: 8px;

    background:
        rgba(255,255,255,0.06);

    border:
        1px solid rgba(255,255,255,0.08);

    padding:
        10px 15px;

    border-radius: 12px;

    color: #cbd5e1;

    white-space: nowrap;
}


.user i {

    color: #00d4ff;

    font-size: 18px;
}


/* =====================================================
   STAT CARDS
===================================================== */

.stats {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 18px;

    margin-bottom: 25px;
}


.stat {

    padding: 22px;

    border-radius: 18px;

    background:
        rgba(255,255,255,0.055);

    border:
        1px solid rgba(255,255,255,0.08);

    backdrop-filter:
        blur(12px);

    transition:
        transform 0.2s ease,
        border-color 0.2s ease;
}


.stat:hover {

    transform:
        translateY(-3px);

    border-color:
        rgba(0,212,255,0.25);
}


.stat-icon {

    width: 45px;

    height: 45px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 12px;

    background:
        rgba(0,212,255,0.12);

    color: #00d4ff;

    font-size: 22px;

    margin-bottom: 12px;
}


.stat h3 {

    margin: 0;

    font-size: 27px;

    font-weight: 800;
}


.stat p {

    margin:
        5px 0 0;

    color: #8e99aa;

    font-size: 14px;
}


/* =====================================================
   TABLE CONTAINER
===================================================== */

.table-box {

    background:
        rgba(255,255,255,0.055) !important;

    border:
        1px solid rgba(255,255,255,0.08);

    border-radius: 20px;

    padding: 20px;

    overflow-x: auto;

    backdrop-filter:
        blur(15px);

    box-shadow:
        0 20px 50px
        rgba(0,0,0,0.18);
}


/* =====================================================
   TABLE — DARK FIX
===================================================== */

.table {

    color: #ffffff !important;

    background:
        transparent !important;

    --bs-table-bg:
        transparent !important;

    --bs-table-color:
        #ffffff !important;

    --bs-table-border-color:
        rgba(255,255,255,0.06) !important;

    margin: 0;

    min-width: 1150px;

    vertical-align: middle;
}


/*
   QUAN TRỌNG:
   Bootstrap thường tự thêm nền trắng.
   Các dòng dưới đây ép toàn bộ bảng
   thành nền trong suốt.
*/

.table > :not(caption) > * > * {

    background-color:
        transparent !important;

    color:
        #ffffff !important;

    border-bottom-color:
        rgba(255,255,255,0.06) !important;

    box-shadow:
        none !important;
}


.table thead,
.table tbody,
.table tr,
.table td,
.table th {

    background:
        transparent !important;

    background-color:
        transparent !important;
}


.table thead {

    background:
        rgba(255,255,255,0.025) !important;
}


.table thead th {

    background:
        rgba(255,255,255,0.025) !important;

    color:
        #8e99aa !important;

    font-size: 13px;

    font-weight: 700;

    border-bottom:
        1px solid rgba(255,255,255,0.1) !important;

    padding:
        15px 12px;

    white-space: nowrap;
}


.table tbody td {

    background:
        transparent !important;

    color:
        #ffffff !important;

    border-bottom:
        1px solid rgba(255,255,255,0.06) !important;

    padding:
        15px 12px;
}


.table tbody tr {

    background:
        transparent !important;

    transition:
        background 0.2s ease;
}


.table tbody tr:hover {

    background:
        rgba(255,255,255,0.035) !important;
}


.table tbody tr:hover td {

    background:
        rgba(255,255,255,0.035) !important;

    color:
        #ffffff !important;
}


/* =====================================================
   ORDER ID
===================================================== */

.order-id {

    color: #00d4ff;

    font-weight: 800;

    white-space: nowrap;
}


/* =====================================================
   CUSTOMER
===================================================== */

.customer-name {

    font-weight: 700;

    color: #ffffff;

    white-space: nowrap;
}


.customer-phone {

    display: block;

    color: #7f8a9d;

    font-size: 12px;

    margin-top: 4px;

    white-space: nowrap;
}


/* =====================================================
   PAYMENT METHOD
===================================================== */

.payment-method {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    padding:
        7px 10px;

    border-radius: 9px;

    font-size: 12px;

    font-weight: 700;

    white-space: nowrap;
}


.payment-method.cod {

    background:
        rgba(34,197,94,0.12);

    color:
        #4ade80;
}


.payment-method.bank {

    background:
        rgba(59,130,246,0.12);

    color:
        #60a5fa;
}


/* =====================================================
   PAYMENT STATUS
===================================================== */

.payment-status {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding:
        7px 10px;

    border-radius: 9px;

    font-size: 12px;

    font-weight: 700;

    white-space: nowrap;
}


.payment-status.paid {

    background:
        rgba(34,197,94,0.12);

    color:
        #4ade80;
}


.payment-status.unpaid {

    background:
        rgba(239,68,68,0.12);

    color:
        #f87171;
}


/* =====================================================
   ORDER STATUS
===================================================== */

.order-status {

    display: inline-block;

    padding:
        7px 11px;

    border-radius: 9px;

    font-size: 12px;

    font-weight: 700;

    white-space: nowrap;
}


.order-status.pending {

    background:
        rgba(234,179,8,0.12);

    color:
        #facc15;
}


.order-status.confirmed {

    background:
        rgba(59,130,246,0.12);

    color:
        #60a5fa;
}


.order-status.shipping {

    background:
        rgba(168,85,247,0.12);

    color:
        #c084fc;
}


.order-status.completed {

    background:
        rgba(34,197,94,0.12);

    color:
        #4ade80;
}


.order-status.cancelled {

    background:
        rgba(239,68,68,0.12);

    color:
        #f87171;
}


/* =====================================================
   TOTAL
===================================================== */

.total {

    font-weight: 800;

    color: #ffffff;

    white-space: nowrap;
}


/* =====================================================
   DATE
===================================================== */

.order-date {

    color: #9aa4b2;

    font-size: 12px;

    white-space: nowrap;
}


/* =====================================================
   UPDATE BUTTON
===================================================== */

.btn-view {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 6px;

    text-decoration: none;

    background:
        rgba(0,212,255,0.1);

    color:
        #00d4ff;

    border:
        1px solid rgba(0,212,255,0.2);

    padding:
        8px 12px;

    border-radius: 9px;

    font-size: 12px;

    font-weight: 700;

    white-space: nowrap;

    transition:
        background 0.2s ease,
        color 0.2s ease,
        transform 0.2s ease;
}


.btn-view:hover {

    background:
        #00d4ff;

    color:
        #061018;

    transform:
        translateY(-1px);
}


/* =====================================================
   EMPTY
===================================================== */

.empty {

    text-align: center;

    padding:
        70px 20px;

    color: #8e99aa;
}


.empty i {

    font-size: 55px;

    display: block;

    margin-bottom: 15px;

    color: #00d4ff;
}


.empty h4 {

    color: #ffffff;

    margin-bottom: 8px;
}


.empty p {

    margin: 0;
}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 1100px) {

    .stats {

        grid-template-columns:
            repeat(2, 1fr);
    }
}


@media (max-width: 768px) {

    .sidebar {

        position: static;

        width: 100%;

        height: auto;

        border-right: 0;

        border-bottom:
            1px solid rgba(255,255,255,0.08);
    }


    .main {

        margin-left: 0;

        padding: 20px;
    }


    .stats {

        grid-template-columns:
            1fr;
    }


    .topbar {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;
    }


    .user {

        width: 100%;
    }


    .table-box {

        padding: 12px;

        border-radius: 15px;
    }
}

</style>

</head>


<body>


<!-- =====================================================
     SIDEBAR
===================================================== -->

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

            <span>Tổng quan</span>

        </a>


        <a href="san-pham.php">

            <i class="bi bi-box-seam-fill"></i>

            <span>Sản phẩm</span>

        </a>


        <a href="them-san-pham.php">

            <i class="bi bi-plus-circle-fill"></i>

            <span>Thêm sản phẩm</span>

        </a>


        <a
            href="don-hang.php"
            class="active"
        >

            <i class="bi bi-receipt-cutoff"></i>

            <span>Đơn hàng</span>

        </a>


        <a href="tai-khoan.php">

            <i class="bi bi-people-fill"></i>

            <span>Tài khoản</span>

        </a>


    </div>


    <div class="menu-title">

        Hệ thống

    </div>


    <div class="menu">


        <a href="../index.php">

            <i class="bi bi-globe2"></i>

            <span>Xem website</span>

        </a>


        <a href="../dang-xuat.php">

            <i class="bi bi-box-arrow-right"></i>

            <span>Đăng xuất</span>

        </a>


    </div>


</aside>


<!-- =====================================================
     MAIN
===================================================== -->

<main class="main">


    <!-- TOPBAR -->

    <div class="topbar">


        <div>

            <h1>

                Quản lý đơn hàng

            </h1>


            <div class="topbar-subtitle">

                Theo dõi đơn hàng và thanh toán

            </div>

        </div>


        <div class="user">

            <i class="bi bi-person-circle"></i>

            <?= htmlspecialchars(
                $_SESSION["fullname"]
                ?? $_SESSION["username"]
            ) ?>

        </div>


    </div>


    <!-- =================================================
         THỐNG KÊ
    ================================================= -->

    <div class="stats">


        <!-- TOTAL -->

        <div class="stat">

            <div class="stat-icon">

                <i class="bi bi-receipt"></i>

            </div>


            <h3>

                <?= $totalOrders ?>

            </h3>


            <p>

                Tổng đơn hàng

            </p>

        </div>


        <!-- PENDING -->

        <div class="stat">

            <div class="stat-icon">

                <i class="bi bi-hourglass-split"></i>

            </div>


            <h3>

                <?= $pendingOrders ?>

            </h3>


            <p>

                Chờ xác nhận

            </p>

        </div>


        <!-- PAID -->

        <div class="stat">

            <div class="stat-icon">

                <i class="bi bi-check-circle"></i>

            </div>


            <h3>

                <?= $paidOrders ?>

            </h3>


            <p>

                Đã thanh toán

            </p>

        </div>


        <!-- UNPAID -->

        <div class="stat">

            <div class="stat-icon">

                <i class="bi bi-exclamation-circle"></i>

            </div>


            <h3>

                <?= $unpaidOrders ?>

            </h3>


            <p>

                Chưa thanh toán

            </p>

        </div>


    </div>


    <!-- =================================================
         TABLE
    ================================================= -->

    <div class="table-box">


        <?php if (count($orders) === 0): ?>


            <div class="empty">


                <i class="bi bi-inbox"></i>


                <h4>

                    Chưa có đơn hàng

                </h4>


                <p>

                    Khi khách hàng đặt hàng,
                    đơn hàng sẽ xuất hiện tại đây.

                </p>


            </div>


        <?php else: ?>


            <table class="table">


                <thead>


                    <tr>


                        <th>

                            Mã đơn

                        </th>


                        <th>

                            Khách hàng

                        </th>


                        <th>

                            Thanh toán

                        </th>


                        <th>

                            Trạng thái TT

                        </th>


                        <th>

                            Tổng tiền

                        </th>


                        <th>

                            Trạng thái đơn

                        </th>


                        <th>

                            Ngày đặt

                        </th>


                        <th>

                            Thao tác

                        </th>


                    </tr>


                </thead>


                <tbody>


                <?php foreach ($orders as $order): ?>


                    <?php


                    $order_status =
                        getOrderStatus(
                            $order["status"]
                        );


                    $payment_method =
                        getPaymentMethod(
                            $order["payment_method"]
                        );


                    $payment_status =
                        getPaymentStatus(
                            $order["payment_status"]
                            ?? "unpaid"
                        );


                    ?>


                    <tr>


                        <!-- MÃ ĐƠN -->

                        <td>

                            <span class="order-id">

                                #DH<?= $order["id"] ?>

                            </span>

                        </td>


                        <!-- KHÁCH HÀNG -->

                        <td>


                            <span class="customer-name">

                                <?= htmlspecialchars(
                                    $order["fullname"]
                                ) ?>

                            </span>


                            <span class="customer-phone">

                                <?= htmlspecialchars(
                                    $order["phone"]
                                ) ?>

                            </span>


                        </td>


                        <!-- PHƯƠNG THỨC -->

                        <td>


                            <span
                                class="
                                payment-method
                                <?= $payment_method["class"] ?>
                                "
                            >


                                <i
                                    class="
                                    bi
                                    <?= $payment_method["icon"] ?>
                                    "
                                ></i>


                                <?= $payment_method["label"] ?>


                            </span>


                        </td>


                        <!-- TRẠNG THÁI THANH TOÁN -->

                        <td>


                            <span
                                class="
                                payment-status
                                <?= $payment_status["class"] ?>
                                "
                            >


                                <i
                                    class="
                                    bi
                                    <?= $payment_status["icon"] ?>
                                    "
                                ></i>


                                <?= $payment_status["label"] ?>


                            </span>


                        </td>


                        <!-- TỔNG TIỀN -->

                        <td class="total">


                            <?= number_format(
                                $order["total_amount"],
                                0,
                                ",",
                                "."
                            ) ?>


                            ₫


                        </td>


                        <!-- TRẠNG THÁI ĐƠN -->

                        <td>


                            <span
                                class="
                                order-status
                                <?= $order_status["class"] ?>
                                "
                            >


                                <?= $order_status["label"] ?>


                            </span>


                        </td>


                        <!-- NGÀY -->

                        <td>


                            <span class="order-date">

                                <?= date(
                                    "d/m/Y H:i",
                                    strtotime(
                                        $order["created_at"]
                                    )
                                ) ?>


                            </span>


                        </td>


                        <!-- CẬP NHẬT -->

                        <td>


                            <a
                                href="
                                cap-nhat-don.php?id=
                                <?= $order["id"] ?>
                                "
                                class="btn-view"
                            >


                                <i
                                    class="
                                    bi bi-pencil-square
                                    "
                                ></i>


                                Cập nhật


                            </a>


                        </td>


                    </tr>


                <?php endforeach; ?>


                </tbody>


            </table>


        <?php endif; ?>


    </div>


</main>


</body>

</html>