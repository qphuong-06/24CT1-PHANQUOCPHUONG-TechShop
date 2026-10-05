<?php

session_start();

require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Kiểm tra đăng nhập
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {

    header("Location: ../dang-nhap.php");

    exit;
}


/*
|--------------------------------------------------------------------------
| Chỉ Admin mới được quản lý tài khoản
|--------------------------------------------------------------------------
*/

if ($_SESSION["role"] !== "admin") {

    die(
        "Chỉ quản trị viên mới có quyền quản lý tài khoản."
    );
}


/*
|--------------------------------------------------------------------------
| Lấy danh sách tài khoản
|--------------------------------------------------------------------------
*/

$result = $conn->query("
    SELECT
        id,
        username,
        fullname,
        email,
        phone,
        role,
        created_at
    FROM users
    ORDER BY id DESC
");

?>

<!DOCTYPE html>

<html lang="vi">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>Quản lý tài khoản | TechShop</title>


<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
>


<style>

/* =========================================================
   RESET
========================================================= */

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    background:
        radial-gradient(
            circle at top right,
            rgba(59, 130, 246, 0.12),
            transparent 30%
        ),
        radial-gradient(
            circle at bottom left,
            rgba(139, 92, 246, 0.10),
            transparent 30%
        ),
        #0b1120;

    color: #e5e7eb;

    font-family:
        "Segoe UI",
        Arial,
        sans-serif;
}


/* =========================================================
   SIDEBAR
========================================================= */

.sidebar {

    position: fixed;

    left: 0;

    top: 0;

    width: 260px;

    height: 100vh;

    background:
        linear-gradient(
            180deg,
            #111827 0%,
            #0f172a 100%
        );

    border-right:
        1px solid rgba(255,255,255,0.08);

    padding: 24px 16px;

    z-index: 1000;
}


/* =========================================================
   BRAND
========================================================= */

.brand {

    display: flex;

    align-items: center;

    gap: 12px;

    padding:
        0 10px 25px;

    border-bottom:
        1px solid rgba(255,255,255,0.08);

    margin-bottom: 22px;
}


.brand-icon {

    width: 44px;

    height: 44px;

    border-radius: 14px;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    color: white;

    font-size: 22px;

    box-shadow:
        0 10px 30px
        rgba(37,99,235,0.30);
}


.brand-title {

    font-size: 20px;

    font-weight: 800;

    color: white;
}


.brand-subtitle {

    font-size: 11px;

    color: #94a3b8;

    margin-top: 2px;
}


/* =========================================================
   USER BOX
========================================================= */

.user-box {

    background:
        rgba(255,255,255,0.04);

    border:
        1px solid rgba(255,255,255,0.07);

    border-radius: 15px;

    padding: 14px;

    margin-bottom: 22px;
}


.user-name {

    font-weight: 700;

    color: white;

    font-size: 14px;
}


.user-role {

    display: inline-block;

    margin-top: 5px;

    padding:
        4px 9px;

    border-radius: 20px;

    background:
        rgba(239,68,68,0.15);

    color: #fca5a5;

    font-size: 11px;

    font-weight: 700;
}


/* =========================================================
   MENU
========================================================= */

.menu-title {

    color: #64748b;

    font-size: 11px;

    text-transform: uppercase;

    font-weight: 700;

    letter-spacing: 1px;

    padding: 0 12px;

    margin-bottom: 8px;
}


.sidebar a {

    display: flex;

    align-items: center;

    gap: 12px;

    color: #94a3b8;

    text-decoration: none;

    padding:
        12px 14px;

    border-radius: 12px;

    margin-bottom: 5px;

    font-size: 14px;

    font-weight: 600;

    transition: 0.25s;
}


.sidebar a i {

    font-size: 17px;
}


.sidebar a:hover {

    background:
        rgba(255,255,255,0.06);

    color: white;

    transform:
        translateX(3px);
}


.sidebar a.active {

    background:
        linear-gradient(
            135deg,
            rgba(37,99,235,0.25),
            rgba(124,58,237,0.20)
        );

    color: white;

    border:
        1px solid
        rgba(96,165,250,0.15);
}


/* =========================================================
   MAIN
========================================================= */

.main {

    margin-left: 260px;

    min-height: 100vh;
}


/* =========================================================
   TOPBAR
========================================================= */

.topbar {

    height: 72px;

    padding:
        0 32px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    border-bottom:
        1px solid rgba(255,255,255,0.07);

    background:
        rgba(15,23,42,0.75);

    backdrop-filter:
        blur(15px);
}


.topbar-title {

    font-size: 18px;

    font-weight: 700;

    color: white;
}


.topbar-right {

    display: flex;

    align-items: center;

    gap: 10px;

    color: #94a3b8;

    font-size: 13px;
}


.status-dot {

    width: 9px;

    height: 9px;

    border-radius: 50%;

    background: #22c55e;

    box-shadow:
        0 0 12px
        rgba(34,197,94,0.8);
}


/* =========================================================
   CONTENT
========================================================= */

.content {

    padding: 34px;
}


/* =========================================================
   HEADER
========================================================= */

.page-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

    margin-bottom: 28px;
}


.page-title {

    color: white;

    font-size: 30px;

    font-weight: 800;

    margin: 0;
}


.page-subtitle {

    color: #94a3b8;

    margin-top: 7px;

    font-size: 14px;
}


/* =========================================================
   HEADER BUTTON
========================================================= */

.btn-home {

    text-decoration: none;

    color: #cbd5e1;

    border:
        1px solid rgba(255,255,255,0.10);

    background:
        rgba(255,255,255,0.04);

    padding:
        11px 17px;

    border-radius: 12px;

    font-weight: 600;

    transition: 0.25s;
}


.btn-home:hover {

    color: white;

    background:
        rgba(255,255,255,0.08);
}


/* =========================================================
   STATS
========================================================= */

.stats-grid {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 18px;

    margin-bottom: 24px;
}


.stat-card {

    position: relative;

    overflow: hidden;

    background:
        linear-gradient(
            145deg,
            rgba(30,41,59,0.95),
            rgba(15,23,42,0.95)
        );

    border:
        1px solid rgba(255,255,255,0.08);

    border-radius: 18px;

    padding: 22px;

    box-shadow:
        0 20px 50px
        rgba(0,0,0,0.20);
}


.stat-card::after {

    content: "";

    position: absolute;

    width: 100px;

    height: 100px;

    border-radius: 50%;

    background:
        rgba(59,130,246,0.08);

    right: -30px;

    top: -30px;
}


.stat-icon {

    width: 44px;

    height: 44px;

    border-radius: 13px;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        rgba(59,130,246,0.12);

    color: #60a5fa;

    font-size: 20px;

    margin-bottom: 14px;
}


.stat-number {

    color: white;

    font-size: 28px;

    font-weight: 800;
}


.stat-label {

    color: #64748b;

    font-size: 13px;

    margin-top: 3px;
}


/* =========================================================
   TABLE CARD
========================================================= */

.table-card {

    background:
        linear-gradient(
            145deg,
            rgba(30,41,59,0.95),
            rgba(15,23,42,0.95)
        );

    border:
        1px solid rgba(255,255,255,0.08);

    border-radius: 20px;

    overflow: hidden;

    box-shadow:
        0 25px 70px
        rgba(0,0,0,0.25);
}


.table-header {

    padding: 22px 24px;

    border-bottom:
        1px solid rgba(255,255,255,0.07);

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;
}


.table-title {

    color: white;

    font-size: 17px;

    font-weight: 750;

    margin: 0;
}


.table-subtitle {

    color: #64748b;

    font-size: 12px;

    margin-top: 4px;
}


/* =========================================================
   SEARCH
========================================================= */

.search-box {

    position: relative;

    width: 280px;
}


.search-box i {

    position: absolute;

    left: 14px;

    top: 50%;

    transform:
        translateY(-50%);

    color: #64748b;
}


.search-box input {

    width: 100%;

    height: 42px;

    padding:
        8px 14px 8px 40px;

    background:
        rgba(15,23,42,0.8);

    border:
        1px solid rgba(255,255,255,0.10);

    border-radius: 11px;

    color: white;

    outline: none;
}


.search-box input:focus {

    border-color:
        #3b82f6;

    box-shadow:
        0 0 0 3px
        rgba(59,130,246,0.10);
}


.search-box input::placeholder {

    color: #64748b;
}


/* =========================================================
   TABLE
========================================================= */

.table-wrapper {

    overflow-x: auto;
}


table {

    width: 100%;

    min-width: 1050px;

    border-collapse: collapse;
}


thead {

    background:
        rgba(255,255,255,0.025);
}


thead th {

    color: #64748b;

    font-size: 11px;

    text-transform: uppercase;

    letter-spacing: 0.6px;

    font-weight: 700;

    padding:
        15px 18px;

    border-bottom:
        1px solid rgba(255,255,255,0.07);

    white-space: nowrap;
}


tbody td {

    padding:
        17px 18px;

    border-bottom:
        1px solid rgba(255,255,255,0.055);

    color: #cbd5e1;

    font-size: 13px;

    vertical-align: middle;
}


tbody tr {

    transition: 0.2s;
}


tbody tr:hover {

    background:
        rgba(255,255,255,0.025);
}


.id-number {

    color: #64748b;

    font-weight: 700;
}


.username {

    color: white;

    font-weight: 750;
}


.fullname {

    color: #cbd5e1;

    font-weight: 600;
}


.email {

    color: #94a3b8;
}


.phone {

    color: #94a3b8;
}


/* =========================================================
   ROLE BADGES
========================================================= */

.role-badge {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding:
        6px 10px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 750;
}


.role-admin {

    background:
        rgba(239,68,68,0.13);

    color: #fca5a5;

    border:
        1px solid rgba(239,68,68,0.18);
}


.role-manager {

    background:
        rgba(245,158,11,0.13);

    color: #fcd34d;

    border:
        1px solid rgba(245,158,11,0.18);
}


.role-customer {

    background:
        rgba(100,116,139,0.13);

    color: #cbd5e1;

    border:
        1px solid rgba(100,116,139,0.18);
}


/* =========================================================
   DATE
========================================================= */

.date-text {

    color: #64748b;

    white-space: nowrap;

    font-size: 12px;
}


/* =========================================================
   ACTIONS
========================================================= */

.action-group {

    display: flex;

    align-items: center;

    gap: 7px;
}


.btn-edit {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    text-decoration: none;

    color: #fcd34d;

    background:
        rgba(245,158,11,0.10);

    border:
        1px solid rgba(245,158,11,0.18);

    padding:
        7px 10px;

    border-radius: 9px;

    font-size: 12px;

    font-weight: 700;

    transition: 0.2s;
}


.btn-edit:hover {

    color: white;

    background:
        rgba(245,158,11,0.20);
}


.btn-delete {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    text-decoration: none;

    color: #fca5a5;

    background:
        rgba(239,68,68,0.10);

    border:
        1px solid rgba(239,68,68,0.18);

    padding:
        7px 10px;

    border-radius: 9px;

    font-size: 12px;

    font-weight: 700;

    transition: 0.2s;
}


.btn-delete:hover {

    color: white;

    background:
        rgba(239,68,68,0.20);
}


.logged-in {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    color: #60a5fa;

    background:
        rgba(59,130,246,0.10);

    border:
        1px solid rgba(59,130,246,0.15);

    padding:
        7px 10px;

    border-radius: 9px;

    font-size: 11px;

    font-weight: 700;

    white-space: nowrap;
}


/* =========================================================
   EMPTY
========================================================= */

.empty-state {

    padding:
        65px 20px;

    text-align: center;
}


.empty-icon {

    width: 70px;

    height: 70px;

    margin:
        0 auto 18px;

    border-radius: 20px;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        rgba(59,130,246,0.10);

    color: #60a5fa;

    font-size: 30px;
}


.empty-title {

    color: white;

    font-weight: 750;

    font-size: 18px;
}


.empty-text {

    color: #64748b;

    font-size: 13px;

    margin-top: 6px;
}


/* =========================================================
   FOOTER
========================================================= */

.page-footer {

    padding:
        24px 0 10px;

    text-align: center;

    color: #475569;

    font-size: 12px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1100px) {

    .stats-grid {

        grid-template-columns:
            1fr;
    }

}


@media (max-width: 900px) {

    .sidebar {

        position: relative;

        width: 100%;

        height: auto;
    }


    .main {

        margin-left: 0;
    }


    .sidebar a {

        display: inline-flex;

        margin-right: 5px;
    }


    .menu-title {

        margin-top: 15px;
    }


    .content {

        padding: 20px;
    }


    .topbar {

        padding:
            0 20px;
    }


    .page-header {

        flex-direction: column;

        align-items: flex-start;
    }


    .table-header {

        flex-direction: column;

        align-items: flex-start;
    }


    .search-box {

        width: 100%;
    }

}


@media (max-width: 600px) {

    .topbar-right {

        display: none;
    }


    .page-title {

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


    <div class="brand">

        <div class="brand-icon">

            <i class="bi bi-cpu-fill"></i>

        </div>


        <div>

            <div class="brand-title">
                TechShop
            </div>

            <div class="brand-subtitle">
                ADMIN PANEL
            </div>

        </div>

    </div>


    <div class="user-box">

        <div class="user-name">

            <i class="bi bi-person-circle me-1"></i>

            <?= htmlspecialchars(
                $_SESSION["fullname"]
                ?? "Quản trị viên"
            ) ?>

        </div>


        <span class="user-role">

            <i class="bi bi-shield-fill-check me-1"></i>

            ADMIN

        </span>

    </div>


    <div class="menu-title">
        Quản lý
    </div>


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


    <a href="don-hang.php">

        <i class="bi bi-receipt-cutoff"></i>

        Đơn hàng

    </a>


    <a
        href="tai-khoan.php"
        class="active"
    >

        <i class="bi bi-people-fill"></i>

        Tài khoản

    </a>


    <div class="menu-title mt-4">
        Hệ thống
    </div>


    <a
        href="../index.php"
        target="_blank"
    >

        <i class="bi bi-globe2"></i>

        Xem website

    </a>


    <a href="../dang-xuat.php">

        <i class="bi bi-box-arrow-right"></i>

        Đăng xuất

    </a>


</aside>



<!-- =========================================================
     MAIN
========================================================= -->

<main class="main">


    <!-- TOPBAR -->

    <div class="topbar">

        <div class="topbar-title">

            <i class="bi bi-people-fill me-2"></i>

            Quản lý tài khoản

        </div>


        <div class="topbar-right">

            <span class="status-dot"></span>

            Hệ thống đang hoạt động

        </div>

    </div>



    <!-- CONTENT -->

    <div class="content">


        <!-- PAGE HEADER -->

        <div class="page-header">

            <div>

                <h1 class="page-title">

                    Quản lý tài khoản

                </h1>


                <div class="page-subtitle">

                    Quản lý người dùng và phân quyền hệ thống TechShop.

                </div>

            </div>


            <a
                href="index.php"
                class="btn-home"
            >

                <i class="bi bi-arrow-left me-1"></i>

                Quay lại quản trị

            </a>

        </div>



        <?php

        /*
        |--------------------------------------------------------------------------
        | Đếm số tài khoản
        |--------------------------------------------------------------------------
        */

        $totalUsers = 0;

        $adminCount = 0;

        $customerCount = 0;


        if ($result) {

            /*
            | Không dùng num_rows để tránh ảnh hưởng
            | đến vòng lặp fetch_assoc bên dưới.
            |
            | Đếm riêng bằng query.
            */

            $countResult = $conn->query("
                SELECT
                    COUNT(*) AS total,
                    SUM(
                        CASE
                            WHEN role = 'admin'
                            THEN 1
                            ELSE 0
                        END
                    ) AS admins,
                    SUM(
                        CASE
                            WHEN role = 'customer'
                            THEN 1
                            ELSE 0
                        END
                    ) AS customers
                FROM users
            ");


            if ($countResult) {

                $countData =
                    $countResult->fetch_assoc();

                $totalUsers =
                    (int) $countData["total"];

                $adminCount =
                    (int) $countData["admins"];

                $customerCount =
                    (int) $countData["customers"];
            }
        }

        ?>



        <!-- =================================================
             STATS
        ================================================= -->

        <div class="stats-grid">


            <div class="stat-card">

                <div class="stat-icon">

                    <i class="bi bi-people-fill"></i>

                </div>


                <div class="stat-number">

                    <?= $totalUsers ?>

                </div>


                <div class="stat-label">

                    Tổng tài khoản

                </div>

            </div>



            <div class="stat-card">

                <div class="stat-icon">

                    <i class="bi bi-shield-fill-check"></i>

                </div>


                <div class="stat-number">

                    <?= $adminCount ?>

                </div>


                <div class="stat-label">

                    Tài khoản Admin

                </div>

            </div>



            <div class="stat-card">

                <div class="stat-icon">

                    <i class="bi bi-person-check-fill"></i>

                </div>


                <div class="stat-number">

                    <?= $customerCount ?>

                </div>


                <div class="stat-label">

                    Khách hàng

                </div>

            </div>


        </div>



        <!-- =================================================
             TABLE
        ================================================= -->

        <div class="table-card">


            <div class="table-header">


                <div>

                    <h2 class="table-title">

                        <i class="bi bi-list-ul me-2"></i>

                        Danh sách tài khoản

                    </h2>


                    <div class="table-subtitle">

                        Quản lý thông tin và quyền truy cập người dùng.

                    </div>

                </div>



                <div class="search-box">

                    <i class="bi bi-search"></i>

                    <input
                        type="text"
                        id="searchInput"
                        placeholder="Tìm tài khoản..."
                    >

                </div>


            </div>



            <div class="table-wrapper">


                <table id="userTable">


                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Username
                            </th>

                            <th>
                                Họ tên
                            </th>

                            <th>
                                Email
                            </th>

                            <th>
                                SĐT
                            </th>

                            <th>
                                Quyền
                            </th>

                            <th>
                                Ngày tạo
                            </th>

                            <th>
                                Thao tác
                            </th>

                        </tr>

                    </thead>



                    <tbody>


                    <?php if (
                        $result
                        && $result->num_rows > 0
                    ): ?>


                        <?php while (
                            $user =
                            $result->fetch_assoc()
                        ): ?>


                            <tr>


                                <!-- ID -->

                                <td>

                                    <span class="id-number">

                                        #<?= $user["id"] ?>

                                    </span>

                                </td>



                                <!-- USERNAME -->

                                <td>

                                    <span class="username">

                                        <?= htmlspecialchars(
                                            $user["username"]
                                        ) ?>

                                    </span>

                                </td>



                                <!-- FULLNAME -->

                                <td>

                                    <span class="fullname">

                                        <?= htmlspecialchars(
                                            $user["fullname"]
                                        ) ?>

                                    </span>

                                </td>



                                <!-- EMAIL -->

                                <td>

                                    <span class="email">

                                        <?= htmlspecialchars(
                                            $user["email"]
                                        ) ?>

                                    </span>

                                </td>



                                <!-- PHONE -->

                                <td>

                                    <span class="phone">

                                        <?= htmlspecialchars(
                                            $user["phone"]
                                        ) ?>

                                    </span>

                                </td>



                                <!-- ROLE -->

                                <td>


                                    <?php if (
                                        $user["role"]
                                        === "admin"
                                    ): ?>


                                        <span
                                            class="
                                                role-badge
                                                role-admin
                                            "
                                        >

                                            <i
                                                class="
                                                    bi
                                                    bi-shield-fill
                                                "
                                            ></i>

                                            Admin

                                        </span>


                                    <?php elseif (
                                        $user["role"]
                                        === "manager"
                                    ): ?>


                                        <span
                                            class="
                                                role-badge
                                                role-manager
                                            "
                                        >

                                            <i
                                                class="
                                                    bi
                                                    bi-person-gear
                                                "
                                            ></i>

                                            Manager

                                        </span>


                                    <?php else: ?>


                                        <span
                                            class="
                                                role-badge
                                                role-customer
                                            "
                                        >

                                            <i
                                                class="
                                                    bi
                                                    bi-person
                                                "
                                            ></i>

                                            Khách hàng

                                        </span>


                                    <?php endif; ?>


                                </td>



                                <!-- DATE -->

                                <td>

                                    <span class="date-text">

                                        <i
                                            class="
                                                bi
                                                bi-calendar3
                                                me-1
                                            "
                                        ></i>

                                        <?= htmlspecialchars(
                                            $user["created_at"]
                                        ) ?>

                                    </span>

                                </td>



                                <!-- ACTION -->

                                <td>


                                    <div class="action-group">


                                        <!-- EDIT -->

                                        <a
                                            href="
                                                sua-tai-khoan.php?id=
                                                <?= $user["id"] ?>
                                            "
                                            class="btn-edit"
                                        >

                                            <i
                                                class="
                                                    bi
                                                    bi-pencil-square
                                                "
                                            ></i>

                                            Sửa

                                        </a>



                                        <?php if (
                                            $user["id"]
                                            !=
                                            $_SESSION["user_id"]
                                        ): ?>


                                            <!-- DELETE -->

                                            <a
                                                href="
                                                    xoa-tai-khoan.php?id=
                                                    <?= $user["id"] ?>
                                                "
                                                class="btn-delete"
                                                onclick="
                                                    return confirm(
                                                        'Bạn có chắc chắn muốn xóa tài khoản này không?'
                                                    );
                                                "
                                            >

                                                <i
                                                    class="
                                                        bi
                                                        bi-trash3-fill
                                                    "
                                                ></i>

                                                Xóa

                                            </a>


                                        <?php else: ?>


                                            <span
                                                class="logged-in"
                                            >

                                                <i
                                                    class="
                                                        bi
                                                        bi-person-check-fill
                                                    "
                                                ></i>

                                                Đang đăng nhập

                                            </span>


                                        <?php endif; ?>


                                    </div>


                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td colspan="8">


                                <div class="empty-state">


                                    <div class="empty-icon">

                                        <i
                                            class="
                                                bi
                                                bi-people
                                            "
                                        ></i>

                                    </div>


                                    <div class="empty-title">

                                        Chưa có tài khoản

                                    </div>


                                    <div class="empty-text">

                                        Hiện tại hệ thống chưa có tài khoản nào.

                                    </div>


                                </div>


                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>


                </table>


            </div>


        </div>



        <!-- FOOTER -->

        <div class="page-footer">

            TechShop Admin Panel
            • Quản lý tài khoản

        </div>


    </div>


</main>



<script>

/*
|--------------------------------------------------------------------------
| Tìm kiếm tài khoản
|--------------------------------------------------------------------------
*/

const searchInput =
    document.getElementById("searchInput");

const userTable =
    document.getElementById("userTable");


if (searchInput && userTable) {

    searchInput.addEventListener(
        "input",
        function () {

            const keyword =
                this.value
                .toLowerCase()
                .trim();


            const rows =
                userTable
                .querySelectorAll("tbody tr");


            rows.forEach(function (row) {

                const text =
                    row.textContent
                    .toLowerCase();


                if (
                    text.includes(keyword)
                ) {

                    row.style.display = "";

                } else {

                    row.style.display = "none";

                }

            });

        }
    );

}

</script>


</body>

</html>
