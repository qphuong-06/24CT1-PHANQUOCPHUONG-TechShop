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
| Chỉ Admin mới được sửa tài khoản
|--------------------------------------------------------------------------
*/

if ($_SESSION["role"] !== "admin") {

    die(
        "Chỉ quản trị viên mới có quyền sửa tài khoản."
    );
}


/*
|--------------------------------------------------------------------------
| Lấy ID tài khoản
|--------------------------------------------------------------------------
*/

$id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;


if ($id <= 0) {

    header("Location: tai-khoan.php");

    exit;
}


/*
|--------------------------------------------------------------------------
| Lưu thay đổi
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullname =
        trim($_POST["fullname"]);

    $email =
        trim($_POST["email"]);

    $phone =
        trim($_POST["phone"]);

    $role =
        $_POST["role"];


    /*
    |----------------------------------------------------------------------
    | Kiểm tra quyền hợp lệ
    |----------------------------------------------------------------------
    */

    $allowedRoles = [
        "customer",
        "manager",
        "admin"
    ];


    if (
        !in_array(
            $role,
            $allowedRoles,
            true
        )
    ) {

        die("Quyền tài khoản không hợp lệ.");
    }


    /*
    |----------------------------------------------------------------------
    | Cập nhật database
    |----------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        UPDATE users
        SET
            fullname = ?,
            email = ?,
            phone = ?,
            role = ?
        WHERE id = ?
    ");


    $stmt->bind_param(
        "ssssi",
        $fullname,
        $email,
        $phone,
        $role,
        $id
    );


    $stmt->execute();

    $stmt->close();


    header(
        "Location: tai-khoan.php"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Lấy tài khoản cần sửa
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT *
    FROM users
    WHERE id = ?
");


$stmt->bind_param(
    "i",
    $id
);


$stmt->execute();


$user =
    $stmt
    ->get_result()
    ->fetch_assoc();


$stmt->close();


if (!$user) {

    die("Không tìm thấy tài khoản.");
}

?>


<!DOCTYPE html>

<html lang="vi">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>Sửa tài khoản - TechShop</title>


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

    font-family:
        Inter,
        system-ui,
        -apple-system,
        BlinkMacSystemFont,
        "Segoe UI",
        sans-serif;

    background:
        radial-gradient(
            circle at top left,
            rgba(37, 99, 235, 0.18),
            transparent 32%
        ),
        radial-gradient(
            circle at bottom right,
            rgba(124, 58, 237, 0.16),
            transparent 30%
        ),
        #080b14;

    color: #f8fafc;

    min-height: 100vh;
}


/* =========================================================
   SIDEBAR
========================================================= */

.sidebar {

    position: fixed;

    top: 0;
    left: 0;

    width: 260px;

    height: 100vh;

    background:
        linear-gradient(
            180deg,
            #0f172a 0%,
            #080b14 100%
        );

    border-right: 1px solid rgba(255,255,255,0.08);

    padding: 24px 16px;

    z-index: 1000;

    overflow-y: auto;
}


.brand {

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 0 10px 24px;

    border-bottom:
        1px solid rgba(255,255,255,0.08);

    margin-bottom: 20px;
}


.brand-icon {

    width: 44px;
    height: 44px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 13px;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    box-shadow:
        0 10px 30px rgba(37,99,235,0.35);

    font-size: 22px;
}


.brand-text {

    font-size: 20px;

    font-weight: 800;

    letter-spacing: -0.5px;
}


.brand-subtitle {

    color: #64748b;

    font-size: 11px;

    margin-top: 2px;
}


/* =========================================================
   SIDEBAR USER
========================================================= */

.sidebar-user {

    background:
        rgba(255,255,255,0.04);

    border:
        1px solid rgba(255,255,255,0.06);

    border-radius: 15px;

    padding: 13px;

    margin-bottom: 20px;
}


.sidebar-user-name {

    font-size: 14px;

    font-weight: 700;

    margin-bottom: 3px;
}


.sidebar-user-role {

    font-size: 11px;

    color: #60a5fa;

    text-transform: uppercase;

    letter-spacing: 0.5px;
}


/* =========================================================
   MENU
========================================================= */

.menu-title {

    color: #64748b;

    font-size: 11px;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: 1px;

    padding: 0 12px;

    margin: 18px 0 8px;
}


.menu-link {

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 12px 13px;

    margin-bottom: 5px;

    border-radius: 12px;

    color: #94a3b8;

    text-decoration: none;

    font-size: 14px;

    transition: all 0.25s ease;
}


.menu-link i {

    font-size: 17px;

    width: 20px;

    text-align: center;
}


.menu-link:hover {

    color: white;

    background:
        rgba(255,255,255,0.07);

    transform: translateX(3px);
}


.menu-link.active {

    color: white;

    background:
        linear-gradient(
            135deg,
            rgba(37,99,235,0.95),
            rgba(124,58,237,0.95)
        );

    box-shadow:
        0 8px 25px rgba(37,99,235,0.25);
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

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0 32px;

    border-bottom:
        1px solid rgba(255,255,255,0.07);

    background:
        rgba(8,11,20,0.72);

    backdrop-filter: blur(18px);

    position: sticky;

    top: 0;

    z-index: 100;
}


.topbar-title {

    font-size: 15px;

    color: #cbd5e1;

    font-weight: 600;
}


.topbar-right {

    display: flex;

    align-items: center;

    gap: 10px;

    color: #64748b;

    font-size: 13px;
}


/* =========================================================
   CONTENT
========================================================= */

.content {

    padding: 35px;
}


.page-header {

    display: flex;

    align-items: flex-start;

    justify-content: space-between;

    gap: 20px;

    margin-bottom: 28px;
}


.page-title {

    font-size: 30px;

    font-weight: 800;

    letter-spacing: -1px;

    margin: 0 0 8px;
}


.page-description {

    color: #94a3b8;

    margin: 0;

    font-size: 14px;
}


/* =========================================================
   BREADCRUMB
========================================================= */

.breadcrumb-custom {

    display: flex;

    align-items: center;

    gap: 8px;

    margin-bottom: 18px;

    font-size: 13px;

    color: #64748b;
}


.breadcrumb-custom a {

    color: #60a5fa;

    text-decoration: none;
}


.breadcrumb-custom a:hover {

    text-decoration: underline;
}


/* =========================================================
   FORM CARD
========================================================= */

.form-card {

    max-width: 850px;

    background:
        linear-gradient(
            145deg,
            rgba(15,23,42,0.96),
            rgba(15,23,42,0.72)
        );

    border:
        1px solid rgba(255,255,255,0.08);

    border-radius: 22px;

    box-shadow:
        0 25px 70px rgba(0,0,0,0.35);

    overflow: hidden;
}


.form-card-header {

    padding: 25px 28px;

    border-bottom:
        1px solid rgba(255,255,255,0.07);

    display: flex;

    align-items: center;

    gap: 15px;
}


.form-icon {

    width: 48px;
    height: 48px;

    border-radius: 14px;

    display: flex;

    align-items: center;
    justify-content: center;

    background:
        linear-gradient(
            135deg,
            rgba(37,99,235,0.2),
            rgba(124,58,237,0.2)
        );

    color: #60a5fa;

    font-size: 21px;
}


.form-card-title {

    margin: 0;

    font-size: 18px;

    font-weight: 750;
}


.form-card-subtitle {

    color: #64748b;

    font-size: 12px;

    margin-top: 3px;
}


.form-body {

    padding: 30px;
}


/* =========================================================
   FORM
========================================================= */

.form-label {

    color: #cbd5e1;

    font-size: 13px;

    font-weight: 650;

    margin-bottom: 8px;
}


.form-control,
.form-select {

    background:
        rgba(2,6,23,0.7) !important;

    border:
        1px solid rgba(255,255,255,0.1) !important;

    color: #f8fafc !important;

    border-radius: 11px;

    min-height: 48px;

    padding:
        10px 14px;

    transition:
        all 0.2s ease;
}


.form-control:focus,
.form-select:focus {

    border-color:
        rgba(59,130,246,0.8) !important;

    box-shadow:
        0 0 0 4px rgba(59,130,246,0.1) !important;
}


.form-control::placeholder {

    color: #475569;
}


.form-control:disabled {

    background:
        rgba(30,41,59,0.55) !important;

    color: #64748b !important;

    cursor: not-allowed;
}


.form-select option {

    background: #0f172a;

    color: white;
}


/* =========================================================
   USERNAME BOX
========================================================= */

.username-box {

    display: flex;

    align-items: center;

    gap: 12px;

    min-height: 48px;

    padding: 0 14px;

    border:
        1px solid rgba(255,255,255,0.08);

    border-radius: 11px;

    background:
        rgba(30,41,59,0.5);

    color: #64748b;
}


.username-box i {

    color: #475569;

    font-size: 17px;
}


.username-box span {

    color: #94a3b8;

    font-size: 14px;
}


/* =========================================================
   ROLE INFO
========================================================= */

.role-info {

    margin-top: 10px;

    padding: 14px 16px;

    border-radius: 12px;

    background:
        rgba(37,99,235,0.07);

    border:
        1px solid rgba(37,99,235,0.15);

    color: #94a3b8;

    font-size: 12px;

    line-height: 1.6;
}


.role-info i {

    color: #60a5fa;

    margin-right: 5px;
}


/* =========================================================
   BUTTONS
========================================================= */

.form-actions {

    display: flex;

    align-items: center;

    gap: 10px;

    margin-top: 28px;

    padding-top: 24px;

    border-top:
        1px solid rgba(255,255,255,0.07);
}


.btn-save {

    border: 0;

    color: white;

    font-weight: 700;

    padding:
        12px 20px;

    border-radius: 11px;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    box-shadow:
        0 10px 25px rgba(37,99,235,0.25);

    transition:
        all 0.25s ease;
}


.btn-save:hover {

    color: white;

    transform: translateY(-2px);

    box-shadow:
        0 14px 30px rgba(37,99,235,0.35);
}


.btn-cancel {

    color: #cbd5e1;

    background:
        rgba(255,255,255,0.05);

    border:
        1px solid rgba(255,255,255,0.1);

    padding:
        11px 19px;

    border-radius: 11px;

    font-weight: 600;

    text-decoration: none;

    transition: all 0.2s ease;
}


.btn-cancel:hover {

    color: white;

    background:
        rgba(255,255,255,0.1);
}


/* =========================================================
   SECURITY CARD
========================================================= */

.security-card {

    max-width: 850px;

    margin-top: 18px;

    padding: 18px 20px;

    border-radius: 16px;

    background:
        rgba(34,197,94,0.06);

    border:
        1px solid rgba(34,197,94,0.13);

    color: #94a3b8;

    font-size: 12px;

    line-height: 1.6;
}


.security-card i {

    color: #4ade80;

    margin-right: 7px;
}


/* =========================================================
   FOOTER
========================================================= */

.footer {

    padding: 25px 35px;

    margin-top: 20px;

    color: #475569;

    font-size: 12px;

    border-top:
        1px solid rgba(255,255,255,0.05);
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 900px) {

    .sidebar {

        width: 220px;
    }

    .main {

        margin-left: 220px;
    }

    .content {

        padding: 25px;
    }
}


@media (max-width: 700px) {

    .sidebar {

        position: relative;

        width: 100%;

        height: auto;
    }

    .main {

        margin-left: 0;
    }

    .topbar {

        padding: 0 18px;
    }

    .content {

        padding: 20px;
    }

    .page-title {

        font-size: 24px;
    }

    .form-body {

        padding: 22px;
    }

    .form-card-header {

        padding: 20px;
    }

    .form-actions {

        flex-direction: column;

        align-items: stretch;
    }

    .btn-save,
    .btn-cancel {

        width: 100%;

        text-align: center;
    }
}

</style>

</head>


<body>


<!-- =====================================================
     SIDEBAR
===================================================== -->

<aside class="sidebar">


<div class="brand">

    <div class="brand-icon">

        <i class="bi bi-cpu-fill"></i>

    </div>


    <div>

        <div class="brand-text">
            TechShop
        </div>

        <div class="brand-subtitle">
            ADMIN PANEL
        </div>

    </div>

</div>



<div class="sidebar-user">

    <div class="sidebar-user-name">

        <?= htmlspecialchars(
            $_SESSION["fullname"] ?? "Quản trị viên"
        ) ?>

    </div>

    <div class="sidebar-user-role">

        <i class="bi bi-shield-check"></i>

        Administrator

    </div>

</div>



<div class="menu-title">
    Quản lý
</div>


<a
    href="index.php"
    class="menu-link"
>

    <i class="bi bi-grid-1x2-fill"></i>

    Tổng quan

</a>


<a
    href="san-pham.php"
    class="menu-link"
>

    <i class="bi bi-box-seam-fill"></i>

    Sản phẩm

</a>


<a
    href="them-san-pham.php"
    class="menu-link"
>

    <i class="bi bi-plus-square-fill"></i>

    Thêm sản phẩm

</a>


<a
    href="don-hang.php"
    class="menu-link"
>

    <i class="bi bi-receipt-cutoff"></i>

    Đơn hàng

</a>


<a
    href="tai-khoan.php"
    class="menu-link active"
>

    <i class="bi bi-people-fill"></i>

    Tài khoản

</a>



<div class="menu-title">
    Hệ thống
</div>


<a
    href="../index.php"
    class="menu-link"
>

    <i class="bi bi-globe2"></i>

    Xem website

</a>


<a
    href="../dang-xuat.php"
    class="menu-link"
>

    <i class="bi bi-box-arrow-right"></i>

    Đăng xuất

</a>


</aside>



<!-- =====================================================
     MAIN
===================================================== -->

<main class="main">


<!-- TOPBAR -->

<header class="topbar">

    <div class="topbar-title">

        <i class="bi bi-person-gear me-2"></i>

        Quản lý tài khoản

    </div>


    <div class="topbar-right">

        <i class="bi bi-shield-lock"></i>

        Khu vực quản trị

    </div>

</header>



<!-- CONTENT -->

<section class="content">


<!-- BREADCRUMB -->

<div class="breadcrumb-custom">

    <a href="index.php">
        Dashboard
    </a>

    <i class="bi bi-chevron-right"></i>

    <a href="tai-khoan.php">
        Tài khoản
    </a>

    <i class="bi bi-chevron-right"></i>

    <span>
        Sửa tài khoản
    </span>

</div>



<!-- HEADER -->

<div class="page-header">

    <div>

        <h1 class="page-title">

            Chỉnh sửa tài khoản

        </h1>

        <p class="page-description">

            Cập nhật thông tin và quyền truy cập của tài khoản.

        </p>

    </div>

</div>



<!-- FORM -->

<div class="form-card">


<div class="form-card-header">

    <div class="form-icon">

        <i class="bi bi-person-gear"></i>

    </div>


    <div>

        <h2 class="form-card-title">

            Thông tin tài khoản

        </h2>

        <div class="form-card-subtitle">

            Tài khoản #<?= (int) $user["id"] ?>

        </div>

    </div>

</div>



<div class="form-body">


<form method="POST">


<!-- USERNAME -->

<div class="mb-4">

    <label class="form-label">

        <i class="bi bi-person me-1"></i>

        Tên đăng nhập

    </label>


    <div class="username-box">

        <i class="bi bi-person-lock"></i>

        <span>

            <?= htmlspecialchars(
                $user["username"]
            ) ?>

        </span>

    </div>


    <div class="form-text text-secondary mt-2">

        Tên đăng nhập không thể thay đổi.

    </div>

</div>



<!-- FULLNAME + PHONE -->

<div class="row">


<div class="col-md-7 mb-4">

    <label class="form-label">

        <i class="bi bi-person-vcard me-1"></i>

        Họ và tên

    </label>


    <input
        type="text"
        name="fullname"
        class="form-control"
        value="<?= htmlspecialchars(
            $user["fullname"]
        ) ?>"
        required
    >

</div>



<div class="col-md-5 mb-4">

    <label class="form-label">

        <i class="bi bi-telephone me-1"></i>

        Số điện thoại

    </label>


    <input
        type="text"
        name="phone"
        class="form-control"
        value="<?= htmlspecialchars(
            $user["phone"]
        ) ?>"
    >

</div>


</div>



<!-- EMAIL -->

<div class="mb-4">

    <label class="form-label">

        <i class="bi bi-envelope me-1"></i>

        Email

    </label>


    <input
        type="email"
        name="email"
        class="form-control"
        value="<?= htmlspecialchars(
            $user["email"]
        ) ?>"
        required
    >

</div>



<!-- ROLE -->

<div class="mb-3">

    <label class="form-label">

        <i class="bi bi-shield-check me-1"></i>

        Quyền tài khoản

    </label>


    <select
        name="role"
        class="form-select"
    >


        <option
            value="customer"
            <?= $user["role"] === "customer"
                ? "selected"
                : "" ?>
        >

            Khách hàng

        </option>


        <option
            value="manager"
            <?= $user["role"] === "manager"
                ? "selected"
                : "" ?>
        >

            Manager

        </option>


        <option
            value="admin"
            <?= $user["role"] === "admin"
                ? "selected"
                : "" ?>
        >

            Admin

        </option>


    </select>


    <div class="role-info">

        <i class="bi bi-info-circle-fill"></i>

        <strong>Khách hàng:</strong>
        mua sản phẩm và quản lý đơn hàng của mình.

        <br>

        <strong>Manager:</strong>
        quản lý sản phẩm và đơn hàng.

        <br>

        <strong>Admin:</strong>
        có toàn quyền quản trị hệ thống.

    </div>

</div>



<!-- ACTION -->

<div class="form-actions">


<button
    type="submit"
    class="btn-save"
>

    <i class="bi bi-check-circle-fill me-2"></i>

    Lưu thay đổi

</button>


<a
    href="tai-khoan.php"
    class="btn-cancel"
>

    <i class="bi bi-x-circle me-2"></i>

    Hủy

</a>


</div>


</form>


</div>

</div>



<!-- SECURITY -->

<div class="security-card">

    <i class="bi bi-shield-check"></i>

    <strong>Bảo mật:</strong>

    Chỉ tài khoản Admin mới có quyền thay đổi
    thông tin và phân quyền tài khoản.

</div>


</section>



<!-- FOOTER -->

<footer class="footer">

    © 2026 TechShop Admin Panel
    • Hệ thống quản lý cửa hàng phụ kiện công nghệ

</footer>


</main>


</body>

</html>
