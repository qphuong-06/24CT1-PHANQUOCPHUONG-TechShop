<?php

session_start();

require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: dang-nhap.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$sql = $conn->prepare(
    "SELECT *
     FROM orders
     WHERE user_id = ?
     ORDER BY id DESC"
);

$sql->bind_param("i", $user_id);

$sql->execute();

$result = $sql->get_result();

?>

<!DOCTYPE html>
<html lang="vi">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>Lịch sử đơn hàng - TechShop</title>

<!-- Bootstrap -->

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<!-- Bootstrap Icons -->

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
>


<style>

/* =========================
   GENERAL
========================= */

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family:
        "Segoe UI",
        Arial,
        sans-serif;

    color: #e9ecef;

    background:
        radial-gradient(
            circle at top left,
            rgba(13,110,253,0.12),
            transparent 30%
        ),
        #080b12;

    min-height: 100vh;

}


/* =========================
   NAVBAR
========================= */

.site-navbar {

    position: sticky;

    top: 0;

    z-index: 1000;

    background:
        rgba(8,11,18,0.88);

    backdrop-filter: blur(15px);

    border-bottom:
        1px solid rgba(255,255,255,0.08);

}

.navbar-brand {

    color: white !important;

    font-size: 21px;

    font-weight: 800;

}

.navbar-brand i {

    color: #4dabff;

}

.nav-link {

    color: #adb5bd !important;

    transition: 0.3s;

}

.nav-link:hover {

    color: white !important;

}


/* =========================
   PAGE HEADER
========================= */

.page-header {

    padding: 70px 0 45px;

    text-align: center;

}

.page-header-icon {

    width: 72px;

    height: 72px;

    margin: 0 auto 18px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 20px;

    background:
        linear-gradient(
            135deg,
            #0d6efd,
            #6f42c1
        );

    box-shadow:
        0 15px 35px rgba(13,110,253,0.25);

}

.page-header-icon i {

    font-size: 32px;

}

.page-header h1 {

    font-size: 34px;

    font-weight: 800;

    margin-bottom: 10px;

}

.page-header p {

    color: #8f98a5;

    margin: 0;

}


/* =========================
   CONTENT
========================= */

.orders-container {

    max-width: 1050px;

    margin: auto;

    padding:
        0 20px 70px;

}


/* =========================
   EMPTY
========================= */

.empty-box {

    padding: 60px 30px;

    text-align: center;

    border-radius: 22px;

    background:
        rgba(255,255,255,0.045);

    border:
        1px solid rgba(255,255,255,0.08);

}

.empty-icon {

    font-size: 55px;

    color: #495057;

    margin-bottom: 15px;

}

.empty-box h3 {

    font-weight: 700;

}

.empty-box p {

    color: #8f98a5;

}


/* =========================
   ORDER CARD
========================= */

.order-card {

    position: relative;

    margin-bottom: 22px;

    padding: 25px;

    border-radius: 20px;

    background:
        rgba(255,255,255,0.055);

    border:
        1px solid rgba(255,255,255,0.09);

    box-shadow:
        0 15px 40px rgba(0,0,0,0.18);

    transition:
        transform 0.3s,
        border-color 0.3s,
        box-shadow 0.3s;

}

.order-card:hover {

    transform:
        translateY(-3px);

    border-color:
        rgba(77,171,255,0.35);

    box-shadow:
        0 20px 50px rgba(0,0,0,0.3);

}


/* =========================
   ORDER TOP
========================= */

.order-top {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    padding-bottom: 20px;

    margin-bottom: 20px;

    border-bottom:
        1px solid rgba(255,255,255,0.08);

}

.order-number {

    display: flex;

    align-items: center;

    gap: 12px;

}

.order-icon {

    width: 46px;

    height: 46px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 13px;

    background:
        rgba(13,110,253,0.13);

    color: #5da9ff;

}

.order-number strong {

    display: block;

    font-size: 17px;

}

.order-date {

    color: #7f8996;

    font-size: 13px;

    margin-top: 3px;

}


/* =========================
   STATUS
========================= */

.status {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    padding: 8px 13px;

    border-radius: 30px;

    font-size: 13px;

    font-weight: 700;

    white-space: nowrap;

}

.status-pending {

    color: #ffc107;

    background:
        rgba(255,193,7,0.12);

}

.status-confirmed {

    color: #4dabff;

    background:
        rgba(13,110,253,0.12);

}

.status-shipping {

    color: #a78bfa;

    background:
        rgba(111,66,193,0.14);

}

.status-completed {

    color: #43d17a;

    background:
        rgba(25,135,84,0.13);

}

.status-cancelled {

    color: #ff7b86;

    background:
        rgba(220,53,69,0.12);

}


/* =========================
   ORDER INFO
========================= */

.order-info {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 15px;

}

.info-box {

    padding: 18px;

    border-radius: 14px;

    background:
        rgba(255,255,255,0.035);

}

.info-label {

    color: #737d8b;

    font-size: 12px;

    margin-bottom: 7px;

}

.info-value {

    font-size: 16px;

    font-weight: 700;

}

.total-value {

    color: #5da9ff;

    font-size: 19px;

}


/* =========================
   CUSTOMER
========================= */

.customer-info {

    margin-top: 15px;

    padding-top: 15px;

    border-top:
        1px solid rgba(255,255,255,0.07);

    color: #8f98a5;

    font-size: 13px;

}

.customer-info i {

    color: #5da9ff;

    margin-right: 5px;

}


/* =========================
   SHOP BUTTON
========================= */

.shop-btn {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    margin-top: 15px;

    padding: 11px 18px;

    color: white;

    text-decoration: none;

    border-radius: 10px;

    background:
        linear-gradient(
            135deg,
            #0d6efd,
            #6f42c1
        );

    font-size: 14px;

    font-weight: 600;

    transition: 0.3s;

}

.shop-btn:hover {

    color: white;

    transform:
        translateY(-2px);

}


/* =========================
   FOOTER
========================= */

footer {

    padding: 30px 20px;

    text-align: center;

    color: #687281;

    font-size: 13px;

    border-top:
        1px solid rgba(255,255,255,0.07);

}


/* =========================
   RESPONSIVE
========================= */

@media (max-width: 768px) {

    .page-header {

        padding:
            50px 20px 35px;

    }

    .page-header h1 {

        font-size: 28px;

    }

    .order-top {

        align-items: flex-start;

        flex-direction: column;

    }

    .order-info {

        grid-template-columns:
            1fr;

    }

}

</style>

</head>


<body>


<!-- =========================
     NAVBAR
========================= -->

<nav class="navbar navbar-expand-lg site-navbar">

    <div class="container">

        <a
            class="navbar-brand"
            href="index.php"
        >

            <i class="bi bi-cpu-fill me-2"></i>

            TechShop

        </a>


        <div class="d-flex align-items-center gap-2">

            <a
                href="index.php"
                class="btn btn-outline-light btn-sm"
            >

                <i class="bi bi-house me-1"></i>

                Trang chủ

            </a>


            <a
                href="dang-xuat.php"
                class="btn btn-danger btn-sm"
            >

                <i class="bi bi-box-arrow-right me-1"></i>

                Đăng xuất

            </a>

        </div>

    </div>

</nav>


<!-- =========================
     HEADER
========================= -->

<section class="page-header">

    <div class="page-header-icon">

        <i class="bi bi-box-seam"></i>

    </div>


    <h1>

        Lịch sử đơn hàng

    </h1>


    <p>

        Theo dõi và quản lý những đơn hàng bạn đã đặt tại TechShop.

    </p>

</section>


<!-- =========================
     ORDERS
========================= -->

<main class="orders-container">


<?php if ($result->num_rows === 0): ?>


    <!-- EMPTY -->

    <div class="empty-box">

        <div class="empty-icon">

            <i class="bi bi-bag-x"></i>

        </div>


        <h3>

            Bạn chưa có đơn hàng nào

        </h3>


        <p>

            Hãy khám phá các sản phẩm công nghệ và đặt đơn hàng đầu tiên.

        </p>


        <a
            href="san-pham.php"
            class="shop-btn"
        >

            <i class="bi bi-cart3"></i>

            Khám phá sản phẩm

        </a>

    </div>


<?php else: ?>


    <?php while ($order = $result->fetch_assoc()): ?>


        <?php

        $status = [

            "pending" =>
                "Chờ xác nhận",

            "confirmed" =>
                "Đã xác nhận",

            "shipping" =>
                "Đang giao",

            "completed" =>
                "Hoàn thành",

            "cancelled" =>
                "Đã hủy"

        ];


        $statusClass = [

            "pending" =>
                "status-pending",

            "confirmed" =>
                "status-confirmed",

            "shipping" =>
                "status-shipping",

            "completed" =>
                "status-completed",

            "cancelled" =>
                "status-cancelled"

        ];


        $statusIcon = [

            "pending" =>
                "bi-hourglass-split",

            "confirmed" =>
                "bi-check-circle",

            "shipping" =>
                "bi-truck",

            "completed" =>
                "bi-check-circle-fill",

            "cancelled" =>
                "bi-x-circle-fill"

        ];


        $currentStatus =
            $order["status"];

        ?>


        <!-- ORDER CARD -->

        <div class="order-card">


            <!-- TOP -->

            <div class="order-top">


                <div class="order-number">

                    <div class="order-icon">

                        <i class="bi bi-receipt"></i>

                    </div>


                    <div>

                        <strong>

                            Đơn hàng #<?= $order["id"] ?>

                        </strong>


                        <div class="order-date">

                            <i class="bi bi-calendar3 me-1"></i>

                            <?= date(
                                "d/m/Y H:i",
                                strtotime(
                                    $order["created_at"]
                                )
                            ) ?>

                        </div>

                    </div>

                </div>


                <!-- STATUS -->

                <div
                    class="status
                    <?= $statusClass[$currentStatus]
                        ?? "status-pending" ?>"
                >

                    <i
                        class="bi
                        <?= $statusIcon[$currentStatus]
                            ?? "bi-question-circle" ?>"
                    ></i>

                    <?= $status[$currentStatus]
                        ?? $currentStatus ?>

                </div>

            </div>


            <!-- INFORMATION -->

            <div class="order-info">


                <!-- CUSTOMER -->

                <div class="info-box">

                    <div class="info-label">

                        <i class="bi bi-person me-1"></i>

                        Người nhận

                    </div>


                    <div class="info-value">

                        <?= htmlspecialchars(
                            $order["fullname"]
                        ) ?>

                    </div>

                </div>


                <!-- PHONE -->

                <div class="info-box">

                    <div class="info-label">

                        <i class="bi bi-telephone me-1"></i>

                        Số điện thoại

                    </div>


                    <div class="info-value">

                        <?= htmlspecialchars(
                            $order["phone"]
                        ) ?>

                    </div>

                </div>


                <!-- TOTAL -->

                <div class="info-box">

                    <div class="info-label">

                        <i class="bi bi-wallet2 me-1"></i>

                        Tổng thanh toán

                    </div>


                    <div class="info-value total-value">

                        <?= number_format(
                            $order["total_amount"],
                            0,
                            ",",
                            "."
                        ) ?>

                        VNĐ

                    </div>

                </div>


            </div>


            <!-- ADDRESS -->

            <div class="customer-info">

                <i class="bi bi-geo-alt-fill"></i>

                <?= htmlspecialchars(
                    $order["address"]
                ) ?>

            </div>


            <!-- SHOP -->

            <a
                href="san-pham.php"
                class="shop-btn"
            >

                <i class="bi bi-cart3"></i>

                Tiếp tục mua sắm

            </a>

        </div>


    <?php endwhile; ?>


<?php endif; ?>


</main>


<!-- =========================
     FOOTER
========================= -->

<footer>

    © 2026 TechShop · Phụ kiện công nghệ chính hãng

</footer>


</body>

</html>