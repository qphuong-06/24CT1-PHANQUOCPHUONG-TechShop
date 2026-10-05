<?php

session_start();

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| KIỂM TRA ĐĂNG NHẬP
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {

    header("Location: ../dang-nhap.php");

    exit;
}

/*
|--------------------------------------------------------------------------
| KIỂM TRA QUYỀN ADMIN / MANAGER
|--------------------------------------------------------------------------
*/

if (
    $_SESSION["role"] !== "admin"
    && $_SESSION["role"] !== "manager"
) {

    die("Bạn không có quyền truy cập trang quản trị.");
}

/*
|--------------------------------------------------------------------------
| LẤY THÔNG TIN TỔNG QUAN
|--------------------------------------------------------------------------
*/

// Tổng số sản phẩm
$result_products = $conn->query(
    "SELECT COUNT(*) AS total
     FROM products"
);

$total_products =
    $result_products->fetch_assoc()["total"];


// Tổng số đơn hàng
$result_orders = $conn->query(
    "SELECT COUNT(*) AS total
     FROM orders"
);

$total_orders =
    $result_orders->fetch_assoc()["total"];


// Tổng số tài khoản
$result_users = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users"
);

$total_users =
    $result_users->fetch_assoc()["total"];


// Tổng doanh thu đơn đã hoàn thành
$result_revenue = $conn->query(
    "SELECT COALESCE(SUM(total_amount), 0) AS total
     FROM orders
     WHERE status = 'completed'"
);

$total_revenue =
    $result_revenue->fetch_assoc()["total"];


// Đơn đang chờ xử lý
$result_pending = $conn->query(
    "SELECT COUNT(*) AS total
     FROM orders
     WHERE status = 'pending'"
);

$total_pending =
    $result_pending->fetch_assoc()["total"];

?>

<!DOCTYPE html>
<html lang="vi">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>Admin Dashboard - TechShop</title>


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

/* =========================================================
   GENERAL
========================================================= */

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
            circle at top left,
            rgba(13,110,253,0.12),
            transparent 28%
        ),
        radial-gradient(
            circle at bottom right,
            rgba(111,66,193,0.10),
            transparent 30%
        ),
        #080b12;

    color: #e9ecef;

    min-height: 100vh;

}


/* =========================================================
   SIDEBAR
========================================================= */

.sidebar {

    position: fixed;

    top: 0;

    left: 0;

    width: 250px;

    height: 100vh;

    padding: 25px 18px;

    background:
        rgba(12,16,25,0.96);

    border-right:
        1px solid rgba(255,255,255,0.07);

    z-index: 1000;

}


/* LOGO */

.sidebar-logo {

    display: flex;

    align-items: center;

    gap: 10px;

    padding:
        8px 10px 28px;

    color: white;

    text-decoration: none;

    font-size: 22px;

    font-weight: 800;

}

.sidebar-logo i {

    color: #4dabff;

    font-size: 25px;

}


/* USER */

.admin-user {

    padding: 15px;

    margin-bottom: 20px;

    border-radius: 15px;

    background:
        rgba(255,255,255,0.045);

    border:
        1px solid rgba(255,255,255,0.07);

}

.admin-avatar {

    width: 42px;

    height: 42px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 12px;

    background:
        linear-gradient(
            135deg,
            #0d6efd,
            #6f42c1
        );

    font-size: 19px;

}

.admin-name {

    font-size: 14px;

    font-weight: 700;

}

.admin-role {

    color: #687281;

    font-size: 11px;

}


/* MENU */

.menu-title {

    padding:
        8px 12px;

    color: #5f6977;

    font-size: 10px;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: 1px;

}

.side-link {

    display: flex;

    align-items: center;

    gap: 12px;

    padding:
        12px 13px;

    margin-bottom: 5px;

    color: #929ba8;

    text-decoration: none;

    border-radius: 11px;

    font-size: 14px;

    transition: 0.25s;

}

.side-link i {

    width: 20px;

    font-size: 17px;

}

.side-link:hover {

    color: white;

    background:
        rgba(13,110,253,0.12);

}

.side-link.active {

    color: white;

    background:
        linear-gradient(
            135deg,
            rgba(13,110,253,0.22),
            rgba(111,66,193,0.18)
        );

}


/* =========================================================
   MAIN
========================================================= */

.main-content {

    margin-left: 250px;

    min-height: 100vh;

}


/* =========================================================
   TOPBAR
========================================================= */

.topbar {

    height: 72px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding:
        0 35px;

    background:
        rgba(8,11,18,0.82);

    backdrop-filter: blur(15px);

    border-bottom:
        1px solid rgba(255,255,255,0.07);

    position: sticky;

    top: 0;

    z-index: 900;

}

.topbar-title {

    font-weight: 700;

    font-size: 16px;

}

.topbar-right {

    display: flex;

    align-items: center;

    gap: 10px;

}


/* =========================================================
   CONTENT
========================================================= */

.dashboard-content {

    padding:
        35px;

}


/* HEADER */

.dashboard-header {

    margin-bottom: 30px;

}

.dashboard-header h1 {

    font-size: 31px;

    font-weight: 800;

    margin-bottom: 7px;

}

.dashboard-header p {

    color: #737d8b;

    margin: 0;

}


/* =========================================================
   STAT CARDS
========================================================= */

.stat-card {

    position: relative;

    overflow: hidden;

    min-height: 165px;

    padding: 24px;

    border-radius: 20px;

    background:
        rgba(255,255,255,0.055);

    border:
        1px solid rgba(255,255,255,0.08);

    transition:
        transform 0.3s,
        border-color 0.3s,
        box-shadow 0.3s;

}

.stat-card:hover {

    transform:
        translateY(-5px);

    border-color:
        rgba(77,171,255,0.3);

    box-shadow:
        0 20px 45px rgba(0,0,0,0.25);

}

.stat-icon {

    width: 50px;

    height: 50px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 14px;

    font-size: 22px;

    margin-bottom: 17px;

}

.icon-blue {

    color: #5da9ff;

    background:
        rgba(13,110,253,0.13);

}

.icon-green {

    color: #43d17a;

    background:
        rgba(25,135,84,0.13);

}

.icon-yellow {

    color: #ffc107;

    background:
        rgba(255,193,7,0.12);

}

.icon-purple {

    color: #b197fc;

    background:
        rgba(111,66,193,0.14);

}

.stat-label {

    color: #737d8b;

    font-size: 13px;

    margin-bottom: 5px;

}

.stat-number {

    color: white;

    font-size: 27px;

    font-weight: 800;

}

.stat-description {

    color: #626c79;

    font-size: 11px;

    margin-top: 3px;

}


/* Decorative circle */

.stat-card::after {

    content: "";

    position: absolute;

    width: 100px;

    height: 100px;

    right: -35px;

    bottom: -40px;

    border-radius: 50%;

    background:
        rgba(255,255,255,0.025);

}


/* =========================================================
   SECTION TITLE
========================================================= */

.section-title {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin:
        42px 0 18px;

}

.section-title h3 {

    font-size: 19px;

    font-weight: 750;

    margin: 0;

}

.section-title span {

    color: #687281;

    font-size: 12px;

}


/* =========================================================
   QUICK ACTIONS
========================================================= */

.action-card {

    display: block;

    height: 100%;

    padding: 23px;

    color: white;

    text-decoration: none;

    border-radius: 17px;

    background:
        rgba(255,255,255,0.045);

    border:
        1px solid rgba(255,255,255,0.08);

    transition:
        transform 0.3s,
        background 0.3s,
        border-color 0.3s;

}

.action-card:hover {

    color: white;

    transform:
        translateY(-4px);

    background:
        rgba(255,255,255,0.07);

    border-color:
        rgba(77,171,255,0.3);

}

.action-icon {

    width: 48px;

    height: 48px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 13px;

    margin-bottom: 16px;

    background:
        rgba(13,110,253,0.12);

    color: #5da9ff;

    font-size: 20px;

}

.action-card h5 {

    font-size: 15px;

    font-weight: 700;

    margin-bottom: 7px;

}

.action-card p {

    color: #737d8b;

    font-size: 12px;

    margin: 0;

}


/* =========================================================
   SYSTEM CARD
========================================================= */

.system-card {

    padding: 24px;

    border-radius: 18px;

    background:
        rgba(255,255,255,0.045);

    border:
        1px solid rgba(255,255,255,0.08);

}

.system-row {

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding:
        13px 0;

    border-bottom:
        1px solid rgba(255,255,255,0.06);

}

.system-row:last-child {

    border-bottom: none;

}

.system-label {

    color: #8b95a2;

    font-size: 13px;

}

.system-value {

    color: white;

    font-size: 13px;

    font-weight: 700;

}

.status-online {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    color: #43d17a;

}

.status-dot {

    width: 7px;

    height: 7px;

    border-radius: 50%;

    background: #43d17a;

    box-shadow:
        0 0 10px #43d17a;

}


/* =========================================================
   FOOTER
========================================================= */

.footer {

    padding:
        25px 35px;

    color: #555f6c;

    font-size: 12px;

    border-top:
        1px solid rgba(255,255,255,0.06);

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 991px) {

    .sidebar {

        position: relative;

        width: 100%;

        height: auto;

        padding: 15px;

    }

    .sidebar-logo {

        padding:
            5px 5px 15px;

    }

    .admin-user {

        display: none;

    }

    .menu-title {

        display: none;

    }

    .side-link {

        display: inline-flex;

        margin-right: 5px;

    }

    .main-content {

        margin-left: 0;

    }

    .topbar {

        padding:
            0 20px;

    }

    .dashboard-content {

        padding:
            25px 20px;

    }

}

@media (max-width: 576px) {

    .topbar-title {

        display: none;

    }

    .dashboard-header h1 {

        font-size: 25px;

    }

}

</style>

</head>


<body>


<!-- =========================================================
     SIDEBAR
========================================================= -->

<aside class="sidebar">


    <!-- LOGO -->

    <a
        href="index.php"
        class="sidebar-logo"
    >

        <i class="bi bi-cpu-fill"></i>

        TechShop

    </a>


    <!-- ADMIN -->

    <div class="admin-user">

        <div class="d-flex align-items-center gap-3">

            <div class="admin-avatar">

                <i class="bi bi-person-fill"></i>

            </div>

            <div>

                <div class="admin-name">

                    <?= htmlspecialchars(
                        $_SESSION["fullname"]
                    ) ?>

                </div>

                <div class="admin-role">

                    <?= strtoupper(
                        $_SESSION["role"]
                    ) ?>

                </div>

            </div>

        </div>

    </div>


    <!-- MENU -->

    <div class="menu-title">

        Quản lý

    </div>


    <a
        href="index.php"
        class="side-link active"
    >

        <i class="bi bi-grid-1x2-fill"></i>

        Tổng quan

    </a>


    <a
        href="san-pham.php"
        class="side-link"
    >

        <i class="bi bi-box-seam"></i>

        Sản phẩm

    </a>


    <a
        href="them-san-pham.php"
        class="side-link"
    >

        <i class="bi bi-plus-square"></i>

        Thêm sản phẩm

    </a>


    <a
        href="don-hang.php"
        class="side-link"
    >

        <i class="bi bi-receipt"></i>

        Đơn hàng

    </a>


    <a
        href="tai-khoan.php"
        class="side-link"
    >

        <i class="bi bi-people"></i>

        Tài khoản

    </a>


    <div class="menu-title mt-3">

        Hệ thống

    </div>


    <a
        href="../index.php"
        class="side-link"
    >

        <i class="bi bi-globe2"></i>

        Xem website

    </a>


    <a
        href="../dang-xuat.php"
        class="side-link"
    >

        <i class="bi bi-box-arrow-right"></i>

        Đăng xuất

    </a>

</aside>


<!-- =========================================================
     MAIN
========================================================= -->

<div class="main-content">


    <!-- TOPBAR -->

    <header class="topbar">

        <div class="topbar-title">

            <i class="bi bi-speedometer2 me-2"></i>

            Admin Dashboard

        </div>


        <div class="topbar-right">

            <span class="text-secondary small">

                Xin chào,

                <strong class="text-white">

                    <?= htmlspecialchars(
                        $_SESSION["fullname"]
                    ) ?>

                </strong>

            </span>


            <a
                href="../index.php"
                class="btn btn-outline-light btn-sm"
            >

                <i class="bi bi-house"></i>

            </a>

        </div>

    </header>


    <!-- CONTENT -->

    <main class="dashboard-content">


        <!-- HEADER -->

        <div class="dashboard-header">

            <h1>

                Tổng quan hệ thống

            </h1>

            <p>

                Theo dõi hoạt động và quản lý cửa hàng TechShop.

            </p>

        </div>


        <!-- =================================================
             STATISTICS
        ================================================== -->

        <div class="row g-4">


            <!-- PRODUCTS -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon icon-blue">

                        <i class="bi bi-box-seam"></i>

                    </div>


                    <div class="stat-label">

                        Tổng sản phẩm

                    </div>


                    <div class="stat-number">

                        <?= $total_products ?>

                    </div>


                    <div class="stat-description">

                        Sản phẩm đang có trong cửa hàng

                    </div>

                </div>

            </div>


            <!-- ORDERS -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon icon-green">

                        <i class="bi bi-receipt"></i>

                    </div>


                    <div class="stat-label">

                        Tổng đơn hàng

                    </div>


                    <div class="stat-number">

                        <?= $total_orders ?>

                    </div>


                    <div class="stat-description">

                        Tất cả đơn hàng trong hệ thống

                    </div>

                </div>

            </div>


            <!-- USERS -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon icon-yellow">

                        <i class="bi bi-people"></i>

                    </div>


                    <div class="stat-label">

                        Tài khoản

                    </div>


                    <div class="stat-number">

                        <?= $total_users ?>

                    </div>


                    <div class="stat-description">

                        Người dùng đã đăng ký

                    </div>

                </div>

            </div>


            <!-- REVENUE -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon icon-purple">

                        <i class="bi bi-currency-dollar"></i>

                    </div>


                    <div class="stat-label">

                        Doanh thu

                    </div>


                    <div class="stat-number">

                        <?= number_format(
                            $total_revenue,
                            0,
                            ",",
                            "."
                        ) ?>

                        ₫

                    </div>


                    <div class="stat-description">

                        Từ các đơn đã hoàn thành

                    </div>

                </div>

            </div>

        </div>


        <!-- =================================================
             QUICK ACTIONS
        ================================================== -->

        <div class="section-title">

            <h3>

                Truy cập nhanh

            </h3>


            <span>

                Các chức năng thường dùng

            </span>

        </div>


        <div class="row g-3">


            <!-- PRODUCTS -->

            <div class="col-xl-4 col-md-6">

                <a
                    href="san-pham.php"
                    class="action-card"
                >

                    <div class="action-icon">

                        <i class="bi bi-box-seam"></i>

                    </div>


                    <h5>

                        Quản lý sản phẩm

                    </h5>


                    <p>

                        Xem, quản lý và xóa sản phẩm trong cửa hàng.

                    </p>

                </a>

            </div>


            <!-- ADD -->

            <div class="col-xl-4 col-md-6">

                <a
                    href="them-san-pham.php"
                    class="action-card"
                >

                    <div class="action-icon">

                        <i class="bi bi-plus-circle"></i>

                    </div>


                    <h5>

                        Thêm sản phẩm

                    </h5>


                    <p>

                        Thêm sản phẩm mới vào hệ thống.

                    </p>

                </a>

            </div>


            <!-- ORDERS -->

            <div class="col-xl-4 col-md-6">

                <a
                    href="don-hang.php"
                    class="action-card"
                >

                    <div class="action-icon">

                        <i class="bi bi-receipt"></i>

                    </div>


                    <h5>

                        Quản lý đơn hàng

                    </h5>


                    <p>

                        Xem và cập nhật trạng thái đơn hàng.

                    </p>

                </a>

            </div>


            <!-- USERS -->

            <div class="col-xl-4 col-md-6">

                <a
                    href="tai-khoan.php"
                    class="action-card"
                >

                    <div class="action-icon">

                        <i class="bi bi-people"></i>

                    </div>


                    <h5>

                        Quản lý tài khoản

                    </h5>


                    <p>

                        Quản lý khách hàng và tài khoản quản trị.

                    </p>

                </a>

            </div>


            <!-- WEBSITE -->

            <div class="col-xl-4 col-md-6">

                <a
                    href="../index.php"
                    class="action-card"
                >

                    <div class="action-icon">

                        <i class="bi bi-globe2"></i>

                    </div>


                    <h5>

                        Xem website

                    </h5>


                    <p>

                        Mở trang bán hàng TechShop.

                    </p>

                </a>

            </div>


            <!-- LOGOUT -->

            <div class="col-xl-4 col-md-6">

                <a
                    href="../dang-xuat.php"
                    class="action-card"
                >

                    <div class="action-icon">

                        <i class="bi bi-box-arrow-right"></i>

                    </div>


                    <h5>

                        Đăng xuất

                    </h5>


                    <p>

                        Đăng xuất khỏi hệ thống quản trị.

                    </p>

                </a>

            </div>

        </div>


        <!-- =================================================
             SYSTEM
        ================================================== -->

        <div class="section-title">

            <h3>

                Trạng thái hệ thống

            </h3>

            <span>

                TechShop

            </span>

        </div>


        <div class="row">


            <div class="col-lg-7">

                <div class="system-card">


                    <div class="system-row">

                        <span class="system-label">

                            Trạng thái website

                        </span>


                        <span class="system-value status-online">

                            <span class="status-dot"></span>

                            Đang hoạt động

                        </span>

                    </div>


                    <div class="system-row">

                        <span class="system-label">

                            Cơ sở dữ liệu

                        </span>


                        <span class="system-value status-online">

                            <span class="status-dot"></span>

                            Đã kết nối

                        </span>

                    </div>


                    <div class="system-row">

                        <span class="system-label">

                            Quyền hiện tại

                        </span>


                        <span class="system-value">

                            <?= strtoupper(
                                $_SESSION["role"]
                            ) ?>

                        </span>

                    </div>


                    <div class="system-row">

                        <span class="system-label">

                            Đơn đang chờ xử lý

                        </span>


                        <span class="system-value">

                            <?= $total_pending ?>

                        </span>

                    </div>


                </div>

            </div>


            <div class="col-lg-5 mt-3 mt-lg-0">

                <div class="system-card h-100">

                    <div class="mb-3">

                        <h5 class="fw-bold mb-1">

                            TechShop

                        </h5>

                        <small class="text-secondary">

                            Hệ thống bán phụ kiện công nghệ

                        </small>

                    </div>


                    <p class="text-secondary small mb-0">

                        Quản lý sản phẩm, tài khoản,
                        đơn hàng và doanh thu
                        trong một giao diện duy nhất.

                    </p>

                </div>

            </div>

        </div>


    </main>


    <!-- FOOTER -->

    <footer class="footer">

        © 2026 TechShop · Admin Dashboard ·

        Website bán phụ kiện công nghệ

    </footer>


</div>


</body>

</html>