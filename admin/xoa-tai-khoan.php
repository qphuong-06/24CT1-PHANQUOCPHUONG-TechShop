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
| Chỉ Admin được xóa
|--------------------------------------------------------------------------
*/

if ($_SESSION["role"] !== "admin") {

    die(
        "Chỉ quản trị viên mới có quyền xóa tài khoản."
    );
}


/*
|--------------------------------------------------------------------------
| Lấy ID
|--------------------------------------------------------------------------
*/

$id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;


/*
|--------------------------------------------------------------------------
| Kiểm tra ID
|--------------------------------------------------------------------------
*/

if ($id <= 0) {

    header("Location: tai-khoan.php");

    exit;
}


/*
|--------------------------------------------------------------------------
| Không cho tự xóa tài khoản đang đăng nhập
|--------------------------------------------------------------------------
*/

if ($id == $_SESSION["user_id"]) {

    die(
        "Bạn không thể xóa tài khoản Admin đang đăng nhập."
    );
}


/*
|--------------------------------------------------------------------------
| Xác nhận xóa
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $confirm =
        $_POST["confirm"] ?? "";


    if ($confirm !== "yes") {

        header("Location: tai-khoan.php");

        exit;
    }


    /*
    |----------------------------------------------------------------------
    | Xóa tài khoản
    |----------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        DELETE FROM users
        WHERE id = ?
    ");


    $stmt->bind_param(
        "i",
        $id
    );


    $stmt->execute();

    $stmt->close();


    /*
    |----------------------------------------------------------------------
    | Quay lại danh sách
    |----------------------------------------------------------------------
    */

    header(
        "Location: tai-khoan.php"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Lấy thông tin tài khoản
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT id, username, fullname, email, phone, role
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


/*
|--------------------------------------------------------------------------
| Không tìm thấy tài khoản
|--------------------------------------------------------------------------
*/

if (!$user) {

    die("Không tìm thấy tài khoản cần xóa.");
}


/*
|--------------------------------------------------------------------------
| Tên quyền hiển thị
|--------------------------------------------------------------------------
*/

$roleNames = [

    "admin" =>
        "Quản trị viên",

    "manager" =>
        "Manager",

    "customer" =>
        "Khách hàng"

];


$roleName =
    $roleNames[$user["role"]]
    ?? $user["role"];

?>


<!DOCTYPE html>

<html lang="vi">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>Xóa tài khoản - TechShop</title>


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

    min-height: 100vh;

    font-family:
        Inter,
        system-ui,
        -apple-system,
        BlinkMacSystemFont,
        "Segoe UI",
        sans-serif;

    color: #f8fafc;

    background:
        radial-gradient(
            circle at top left,
            rgba(37,99,235,0.18),
            transparent 32%
        ),
        radial-gradient(
            circle at bottom right,
            rgba(124,58,237,0.16),
            transparent 30%
        ),
        #080b14;
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

    padding: 24px 16px;

    background:
        linear-gradient(
            180deg,
            #0f172a,
            #080b14
        );

    border-right:
        1px solid rgba(255,255,255,0.08);

    z-index: 1000;
}


.brand {

    display: flex;

    align-items: center;

    gap: 12px;

    padding:
        0 10px 24px;

    margin-bottom: 20px;

    border-bottom:
        1px solid rgba(255,255,255,0.08);
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

    font-size: 21px;
}


.brand-text {

    font-size: 20px;

    font-weight: 800;
}


.brand-subtitle {

    color: #64748b;

    font-size: 11px;

    margin-top: 2px;
}


.sidebar-user {

    padding: 13px;

    margin-bottom: 20px;

    border-radius: 15px;

    background:
        rgba(255,255,255,0.04);

    border:
        1px solid rgba(255,255,255,0.06);
}


.sidebar-user-name {

    font-size: 14px;

    font-weight: 700;

    margin-bottom: 3px;
}


.sidebar-user-role {

    color: #60a5fa;

    font-size: 11px;

    text-transform: uppercase;
}


.menu-title {

    color: #64748b;

    font-size: 11px;

    font-weight: 700;

    letter-spacing: 1px;

    text-transform: uppercase;

    padding: 0 12px;

    margin:
        18px 0 8px;
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

    width: 20px;

    text-align: center;

    font-size: 17px;
}


.menu-link:hover {

    color: white;

    background:
        rgba(255,255,255,0.07);

    transform:
        translateX(3px);
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

    padding:
        0 32px;

    background:
        rgba(8,11,20,0.72);

    backdrop-filter:
        blur(18px);

    border-bottom:
        1px solid rgba(255,255,255,0.07);

    position: sticky;

    top: 0;

    z-index: 100;
}


.topbar-title {

    color: #cbd5e1;

    font-size: 15px;

    font-weight: 600;
}


.topbar-right {

    display: flex;

    align-items: center;

    gap: 8px;

    color: #64748b;

    font-size: 13px;
}


/* =========================================================
   CONTENT
========================================================= */

.content {

    padding: 35px;
}


.breadcrumb-custom {

    display: flex;

    align-items: center;

    gap: 8px;

    margin-bottom: 20px;

    color: #64748b;

    font-size: 13px;
}


.breadcrumb-custom a {

    color: #60a5fa;

    text-decoration: none;
}


.breadcrumb-custom a:hover {

    text-decoration: underline;
}


.page-title {

    margin: 0 0 8px;

    font-size: 30px;

    font-weight: 800;

    letter-spacing: -1px;
}


.page-description {

    margin-bottom: 28px;

    color: #94a3b8;

    font-size: 14px;
}


/* =========================================================
   DELETE CARD
========================================================= */

.delete-wrapper {

    max-width: 760px;

    margin:
        40px auto;
}


.delete-card {

    overflow: hidden;

    border-radius: 24px;

    background:
        linear-gradient(
            145deg,
            rgba(15,23,42,0.97),
            rgba(15,23,42,0.75)
        );

    border:
        1px solid rgba(255,255,255,0.08);

    box-shadow:
        0 30px 80px rgba(0,0,0,0.4);
}


/* =========================================================
   WARNING HEADER
========================================================= */

.warning-header {

    padding:
        35px 30px;

    text-align: center;

    border-bottom:
        1px solid rgba(255,255,255,0.07);
}


.warning-icon {

    width: 76px;
    height: 76px;

    margin:
        0 auto 20px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 22px;

    background:
        rgba(239,68,68,0.12);

    border:
        1px solid rgba(239,68,68,0.2);

    color: #f87171;

    font-size: 34px;

    box-shadow:
        0 15px 40px rgba(239,68,68,0.12);
}


.warning-title {

    margin: 0 0 8px;

    font-size: 25px;

    font-weight: 800;
}


.warning-text {

    max-width: 540px;

    margin:
        0 auto;

    color: #94a3b8;

    font-size: 14px;

    line-height: 1.7;
}


/* =========================================================
   USER INFO
========================================================= */

.user-info {

    padding: 28px 30px;
}


.user-info-title {

    color: #64748b;

    font-size: 11px;

    font-weight: 700;

    letter-spacing: 1px;

    text-transform: uppercase;

    margin-bottom: 14px;
}


.user-box {

    display: flex;

    align-items: center;

    gap: 16px;

    padding: 18px;

    border-radius: 16px;

    background:
        rgba(255,255,255,0.04);

    border:
        1px solid rgba(255,255,255,0.07);
}


.avatar {

    width: 52px;
    height: 52px;

    flex-shrink: 0;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 15px;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    color: white;

    font-size: 22px;

    font-weight: 800;
}


.user-name {

    font-size: 16px;

    font-weight: 750;

    margin-bottom: 4px;
}


.username {

    color: #64748b;

    font-size: 12px;
}


.user-details {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 12px;

    margin-top: 14px;
}


.detail-item {

    padding: 13px;

    border-radius: 12px;

    background:
        rgba(255,255,255,0.025);

    border:
        1px solid rgba(255,255,255,0.05);
}


.detail-label {

    color: #64748b;

    font-size: 11px;

    margin-bottom: 5px;
}


.detail-value {

    color: #cbd5e1;

    font-size: 13px;

    word-break: break-word;
}


.role-badge {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    padding:
        5px 10px;

    border-radius: 20px;

    background:
        rgba(59,130,246,0.12);

    color: #60a5fa;

    font-size: 11px;

    font-weight: 700;
}


/* =========================================================
   DANGER NOTICE
========================================================= */

.danger-notice {

    margin:
        0 30px 25px;

    padding:
        16px 18px;

    border-radius: 14px;

    background:
        rgba(239,68,68,0.07);

    border:
        1px solid rgba(239,68,68,0.15);

    color: #fca5a5;

    font-size: 12px;

    line-height: 1.6;
}


.danger-notice i {

    color: #f87171;

    margin-right: 7px;
}


/* =========================================================
   ACTIONS
========================================================= */

.actions {

    display: flex;

    gap: 12px;

    padding:
        0 30px 30px;
}


.btn-delete {

    flex: 1;

    border: 0;

    padding:
        13px 20px;

    border-radius: 12px;

    background:
        linear-gradient(
            135deg,
            #dc2626,
            #ef4444
        );

    color: white;

    font-weight: 750;

    box-shadow:
        0 10px 25px rgba(239,68,68,0.2);

    transition: all 0.25s ease;
}


.btn-delete:hover {

    color: white;

    transform:
        translateY(-2px);

    box-shadow:
        0 15px 35px rgba(239,68,68,0.3);
}


.btn-cancel {

    flex: 1;

    display: flex;

    align-items: center;

    justify-content: center;

    padding:
        12px 20px;

    border-radius: 12px;

    color: #cbd5e1;

    background:
        rgba(255,255,255,0.05);

    border:
        1px solid rgba(255,255,255,0.1);

    text-decoration: none;

    font-weight: 650;

    transition: all 0.25s ease;
}


.btn-cancel:hover {

    color: white;

    background:
        rgba(255,255,255,0.1);
}


/* =========================================================
   FOOTER
========================================================= */

.footer {

    padding:
        25px 35px;

    color: #475569;

    font-size: 12px;

    border-top:
        1px solid rgba(255,255,255,0.05);
}


/* =========================================================
   RESPONSIVE
========================================================= */

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

        padding:
            0 18px;
    }


    .content {

        padding: 20px;
    }


    .page-title {

        font-size: 24px;
    }


    .delete-wrapper {

        margin:
            25px auto;
    }


    .warning-header {

        padding:
            28px 20px;
    }


    .user-info {

        padding:
            22px 20px;
    }


    .user-details {

        grid-template-columns: 1fr;
    }


    .danger-notice {

        margin:
            0 20px 20px;
    }


    .actions {

        flex-direction: column;

        padding:
            0 20px 22px;
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


<header class="topbar">

    <div class="topbar-title">

        <i class="bi bi-person-x-fill me-2"></i>

        Xóa tài khoản

    </div>


    <div class="topbar-right">

        <i class="bi bi-shield-lock"></i>

        Khu vực quản trị

    </div>

</header>



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
        Xóa tài khoản
    </span>

</div>



<h1 class="page-title">

    Xóa tài khoản

</h1>


<p class="page-description">

    Kiểm tra thông tin trước khi thực hiện thao tác xóa.

</p>



<!-- DELETE -->

<div class="delete-wrapper">


<div class="delete-card">


<!-- WARNING -->

<div class="warning-header">


<div class="warning-icon">

    <i class="bi bi-exclamation-triangle-fill"></i>

</div>


<h2 class="warning-title">

    Bạn có chắc muốn xóa?

</h2>


<p class="warning-text">

    Tài khoản này sẽ bị xóa khỏi hệ thống.
    Dữ liệu tài khoản có thể ảnh hưởng đến
    các dữ liệu liên quan trong cơ sở dữ liệu.

</p>


</div>



<!-- USER INFO -->

<div class="user-info">


<div class="user-info-title">

    Thông tin tài khoản

</div>



<div class="user-box">


<div class="avatar">

    <i class="bi bi-person-fill"></i>

</div>


<div>

    <div class="user-name">

        <?= htmlspecialchars(
            $user["fullname"]
        ) ?>

    </div>


    <div class="username">

        @<?= htmlspecialchars(
            $user["username"]
        ) ?>

    </div>

</div>


</div>



<div class="user-details">


<div class="detail-item">

    <div class="detail-label">

        ID tài khoản

    </div>

    <div class="detail-value">

        #<?= (int) $user["id"] ?>

    </div>

</div>



<div class="detail-item">

    <div class="detail-label">

        Quyền

    </div>

    <div class="detail-value">

        <span class="role-badge">

            <i class="bi bi-shield-fill-check"></i>

            <?= htmlspecialchars(
                $roleName
            ) ?>

        </span>

    </div>

</div>



<div class="detail-item">

    <div class="detail-label">

        Email

    </div>

    <div class="detail-value">

        <?= htmlspecialchars(
            $user["email"]
        ) ?>

    </div>

</div>



<div class="detail-item">

    <div class="detail-label">

        Số điện thoại

    </div>

    <div class="detail-value">

        <?= htmlspecialchars(
            $user["phone"] ?: "Chưa cập nhật"
        ) ?>

    </div>

</div>


</div>


</div>



<!-- WARNING -->

<div class="danger-notice">

    <i class="bi bi-exclamation-circle-fill"></i>

    <strong>Cảnh báo:</strong>

    Đây là thao tác không thể hoàn tác.
    Hãy chắc chắn rằng bạn thực sự muốn
    xóa tài khoản này.

</div>



<!-- ACTIONS -->

<form method="POST">


<input
    type="hidden"
    name="confirm"
    value="yes"
>


<div class="actions">


<a
    href="tai-khoan.php"
    class="btn-cancel"
>

    <i class="bi bi-arrow-left me-2"></i>

    Không, quay lại

</a>


<button
    type="submit"
    class="btn-delete"
>

    <i class="bi bi-trash3-fill me-2"></i>

    Xác nhận xóa tài khoản

</button>


</div>


</form>


</div>


</div>


</section>



<footer class="footer">

    © 2026 TechShop Admin Panel
    • Hệ thống quản lý cửa hàng phụ kiện công nghệ

</footer>


</main>


</body>

</html>
