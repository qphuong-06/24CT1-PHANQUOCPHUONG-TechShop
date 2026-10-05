<?php

session_start();

require_once "../config/database.php";


// =========================
// KIỂM TRA ĐĂNG NHẬP
// =========================

if (!isset($_SESSION["user_id"])) {

    header("Location: ../dang-nhap.php");

    exit;
}


// =========================
// KIỂM TRA QUYỀN
// =========================

if (
    $_SESSION["role"] !== "admin"
    &&
    $_SESSION["role"] !== "manager"
) {

    die("Bạn không có quyền truy cập trang quản trị.");

}


// =========================
// LẤY SẢN PHẨM
// =========================

$sql = "
    SELECT
        products.*,
        categories.name AS category_name
    FROM products
    LEFT JOIN categories
        ON products.category_id = categories.id
    ORDER BY products.id DESC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>

<html lang="vi">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>
    Quản lý sản phẩm | TechShop Admin
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

/* =========================
   GLOBAL
========================= */

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    min-height: 100vh;

    background:
        radial-gradient(
            circle at top left,
            rgba(37, 99, 235, 0.15),
            transparent 30%
        ),
        #0b1120;

    color: #e5e7eb;

    font-family:
        Inter,
        system-ui,
        -apple-system,
        BlinkMacSystemFont,
        "Segoe UI",
        sans-serif;
}


/* =========================
   SIDEBAR
========================= */

.sidebar {

    position: fixed;

    top: 0;
    left: 0;

    width: 250px;
    height: 100vh;

    background:
        linear-gradient(
            180deg,
            #111827,
            #0f172a
        );

    border-right:
        1px solid rgba(255,255,255,0.08);

    padding: 24px 16px;

    z-index: 1000;
}


.logo {

    display: flex;

    align-items: center;

    gap: 10px;

    color: white;

    text-decoration: none;

    font-size: 22px;

    font-weight: 800;

    padding: 10px 12px;

    margin-bottom: 30px;
}


.logo-icon {

    width: 40px;
    height: 40px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 12px;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    box-shadow:
        0 8px 25px rgba(37,99,235,0.35);
}


.sidebar-title {

    color: #64748b;

    font-size: 11px;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: 1px;

    padding: 0 12px;

    margin-bottom: 10px;
}


.menu-item {

    display: flex;

    align-items: center;

    gap: 12px;

    color: #94a3b8;

    text-decoration: none;

    padding: 12px 14px;

    margin-bottom: 6px;

    border-radius: 12px;

    transition: 0.25s;
}


.menu-item:hover {

    color: white;

    background:
        rgba(255,255,255,0.07);

    transform:
        translateX(3px);
}


.menu-item.active {

    color: white;

    background:
        linear-gradient(
            135deg,
            rgba(37,99,235,0.9),
            rgba(124,58,237,0.9)
        );

    box-shadow:
        0 8px 25px rgba(37,99,235,0.2);
}


.menu-icon {

    width: 20px;

    text-align: center;
}


/* =========================
   USER BOX
========================= */

.user-box {

    position: absolute;

    left: 16px;
    right: 16px;

    bottom: 20px;

    padding: 14px;

    background:
        rgba(255,255,255,0.05);

    border:
        1px solid rgba(255,255,255,0.08);

    border-radius: 14px;
}


.user-name {

    color: white;

    font-weight: 700;
}


.user-role {

    color: #94a3b8;

    font-size: 12px;
}


/* =========================
   MAIN
========================= */

.main {

    margin-left: 250px;

    min-height: 100vh;
}


/* =========================
   TOPBAR
========================= */

.topbar {

    height: 75px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0 35px;

    border-bottom:
        1px solid rgba(255,255,255,0.08);

    background:
        rgba(15,23,42,0.75);

    backdrop-filter: blur(15px);
}


.topbar-title {

    color: white;

    font-size: 20px;

    font-weight: 700;
}


.view-site {

    color: #cbd5e1;

    text-decoration: none;

    padding: 9px 14px;

    border-radius: 10px;

    border:
        1px solid rgba(255,255,255,0.1);

    transition: 0.25s;
}


.view-site:hover {

    color: white;

    background:
        rgba(255,255,255,0.07);
}


/* =========================
   CONTENT
========================= */

.content {

    padding: 35px;
}


.page-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

    margin-bottom: 25px;
}


.page-title {

    color: white;

    font-size: 30px;

    font-weight: 800;

    margin-bottom: 5px;
}


.page-description {

    color: #94a3b8;

    margin: 0;
}


/* =========================
   ADD BUTTON
========================= */

.btn-add {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    color: white;

    text-decoration: none;

    padding: 12px 18px;

    border-radius: 12px;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    font-weight: 700;

    transition: 0.25s;

    white-space: nowrap;
}


.btn-add:hover {

    color: white;

    transform:
        translateY(-2px);

    box-shadow:
        0 10px 25px rgba(37,99,235,0.3);
}


/* =========================
   TOOLBAR
========================= */

.toolbar {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 15px;

    margin-bottom: 18px;
}


.product-count {

    color: #64748b;

    font-size: 13px;
}


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

    padding:
        11px 14px 11px 40px;

    border-radius: 11px;

    border:
        1px solid rgba(255,255,255,0.09);

    background:
        rgba(255,255,255,0.04);

    color: white;

    outline: none;

    transition: 0.25s;
}


.search-box input::placeholder {

    color: #64748b;
}


.search-box input:focus {

    border-color: #3b82f6;

    box-shadow:
        0 0 0 3px rgba(59,130,246,0.12);
}


/* =========================
   PRODUCT CARD
========================= */

.product-card {

    background:
        rgba(15,23,42,0.82);

    border:
        1px solid rgba(255,255,255,0.08);

    border-radius: 20px;

    overflow: hidden;

    box-shadow:
        0 20px 50px rgba(0,0,0,0.2);
}


/* =========================
   TABLE
========================= */

.table-wrapper {

    overflow-x: auto;
}


.product-table {

    width: 100%;

    min-width: 1050px;

    margin: 0;

    border-collapse: collapse;
}


.product-table thead {

    background:
        rgba(255,255,255,0.05);
}


.product-table th {

    color: #94a3b8;

    font-size: 12px;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: 0.5px;

    padding: 17px 18px;

    border-bottom:
        1px solid rgba(255,255,255,0.08);
}


.product-table td {

    color: #cbd5e1;

    padding: 16px 18px;

    border-bottom:
        1px solid rgba(255,255,255,0.06);

    vertical-align: middle;
}


.product-table tbody tr {

    transition: 0.2s;
}


.product-table tbody tr:hover {

    background:
        rgba(255,255,255,0.035);
}


/* =========================
   PRODUCT IMAGE
========================= */

.product-image {

    width: 75px;

    height: 75px;

    object-fit: cover;

    border-radius: 14px;

    border:
        1px solid rgba(255,255,255,0.1);

    background: #111827;

    transition: 0.3s;
}


.product-image:hover {

    transform:
        scale(1.08);

    box-shadow:
        0 10px 25px rgba(0,0,0,0.35);
}


.no-image {

    width: 75px;

    height: 75px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 14px;

    background:
        rgba(255,255,255,0.05);

    color: #64748b;

    font-size: 12px;

    text-align: center;
}


/* =========================
   PRODUCT NAME
========================= */

.product-name {

    color: white;

    font-weight: 700;

    font-size: 14px;

    margin-bottom: 5px;
}


.product-description {

    max-width: 260px;

    color: #64748b;

    font-size: 12px;

    line-height: 1.5;

    display:
        -webkit-box;

    -webkit-line-clamp: 2;

    -webkit-box-orient: vertical;

    overflow: hidden;
}


/* =========================
   ID
========================= */

.product-id {

    color: #60a5fa;

    font-weight: 800;
}


/* =========================
   CATEGORY
========================= */

.category-badge {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    padding: 7px 10px;

    border-radius: 999px;

    background:
        rgba(124,58,237,0.12);

    color: #c084fc;

    font-size: 11px;

    font-weight: 700;
}


/* =========================
   PRICE
========================= */

.product-price {

    color: #60a5fa;

    font-weight: 800;

    white-space: nowrap;
}


/* =========================
   STOCK
========================= */

.stock-badge {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding: 7px 10px;

    border-radius: 999px;

    font-size: 11px;

    font-weight: 700;
}


.stock-good {

    color: #34d399;

    background:
        rgba(52,211,153,0.12);
}


.stock-empty {

    color: #f87171;

    background:
        rgba(248,113,113,0.12);
}


.stock-dot {

    width: 6px;

    height: 6px;

    border-radius: 50%;

    background: currentColor;
}


/* =========================
   DELETE BUTTON
========================= */

.btn-delete {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    color: #f87171;

    text-decoration: none;

    padding: 8px 12px;

    border-radius: 9px;

    border:
        1px solid rgba(248,113,113,0.15);

    background:
        rgba(248,113,113,0.07);

    font-size: 12px;

    font-weight: 700;

    transition: 0.25s;

    white-space: nowrap;
}


.btn-delete:hover {

    color: white;

    background:
        #dc2626;

    border-color:
        #dc2626;

    transform:
        translateY(-1px);
}


/* =========================
   EMPTY
========================= */

.empty-products {

    padding: 70px 20px;

    text-align: center;
}


.empty-icon {

    width: 75px;

    height: 75px;

    margin:
        0 auto 20px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 22px;

    background:
        rgba(37,99,235,0.12);

    color: #60a5fa;

    font-size: 32px;
}


.empty-products h4 {

    color: white;

    font-weight: 700;
}


.empty-products p {

    color: #64748b;
}


/* =========================
   BACK
========================= */

.back-btn {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    color: #cbd5e1;

    text-decoration: none;

    padding: 11px 16px;

    border-radius: 11px;

    border:
        1px solid rgba(255,255,255,0.08);

    transition: 0.25s;
}


.back-btn:hover {

    color: white;

    background:
        rgba(255,255,255,0.07);
}


/* =========================
   RESPONSIVE
========================= */

@media (max-width: 900px) {

    .sidebar {

        position: relative;

        width: 100%;

        height: auto;

        border-right: none;

        border-bottom:
            1px solid rgba(255,255,255,0.08);
    }


    .user-box {

        position: static;

        margin-top: 20px;
    }


    .main {

        margin-left: 0;
    }


    .content {

        padding: 20px;
    }


    .page-header {

        align-items: flex-start;

        flex-direction: column;
    }


    .toolbar {

        align-items: flex-start;

        flex-direction: column;
    }


    .search-box {

        width: 100%;
    }

}


@media (max-width: 600px) {

    .topbar {

        padding: 0 20px;
    }


    .view-site {

        display: none;
    }


    .page-title {

        font-size: 24px;
    }

}

</style>

</head>


<body>


<!-- =========================
     SIDEBAR
========================= -->

<aside class="sidebar">


<a
    href="index.php"
    class="logo"
>

    <span class="logo-icon">

        <i class="bi bi-cart3"></i>

    </span>

    TechShop

</a>


<div class="sidebar-title">
    Quản lý
</div>


<a
    href="index.php"
    class="menu-item"
>

    <span class="menu-icon">

        <i class="bi bi-grid-1x2-fill"></i>

    </span>

    Tổng quan

</a>


<a
    href="san-pham.php"
    class="menu-item active"
>

    <span class="menu-icon">

        <i class="bi bi-box-seam"></i>

    </span>

    Sản phẩm

</a>


<a
    href="them-san-pham.php"
    class="menu-item"
>

    <span class="menu-icon">

        <i class="bi bi-plus-circle"></i>

    </span>

    Thêm sản phẩm

</a>


<a
    href="don-hang.php"
    class="menu-item"
>

    <span class="menu-icon">

        <i class="bi bi-receipt"></i>

    </span>

    Đơn hàng

</a>


<a
    href="tai-khoan.php"
    class="menu-item"
>

    <span class="menu-icon">

        <i class="bi bi-people"></i>

    </span>

    Tài khoản

</a>


<div class="sidebar-title mt-4">
    Hệ thống
</div>


<a
    href="../index.php"
    class="menu-item"
    target="_blank"
>

    <span class="menu-icon">

        <i class="bi bi-globe2"></i>

    </span>

    Xem website

</a>


<a
    href="../dang-xuat.php"
    class="menu-item"
>

    <span class="menu-icon">

        <i class="bi bi-box-arrow-right"></i>

    </span>

    Đăng xuất

</a>


<!-- USER -->

<div class="user-box">


<div class="d-flex align-items-center gap-2">


<div
    class="logo-icon"
    style="
        width:38px;
        height:38px;
        font-size:16px;
    "
>

    <i class="bi bi-person-fill"></i>

</div>


<div>

    <div class="user-name">

        <?= htmlspecialchars(
            $_SESSION["fullname"]
            ?? "Quản trị viên"
        ) ?>

    </div>


    <div class="user-role">

        <?= htmlspecialchars(
            $_SESSION["role"]
            ?? "admin"
        ) ?>

    </div>

</div>


</div>


</div>


</aside>



<!-- =========================
     MAIN
========================= -->

<main class="main">


<!-- TOPBAR -->

<div class="topbar">


<div class="topbar-title">

    <i class="bi bi-box-seam me-2"></i>

    Quản lý sản phẩm

</div>


<a
    href="../index.php"
    target="_blank"
    class="view-site"
>

    <i class="bi bi-box-arrow-up-right me-1"></i>

    Xem website

</a>


</div>



<!-- CONTENT -->

<div class="content">


<!-- HEADER -->

<div class="page-header">


<div>

    <div class="page-title">

        Quản lý sản phẩm

    </div>


    <p class="page-description">

        Quản lý sản phẩm, giá bán, danh mục và tồn kho của TechShop.

    </p>

</div>


<a
    href="them-san-pham.php"
    class="btn-add"
>

    <i class="bi bi-plus-lg"></i>

    Thêm sản phẩm

</a>


</div>



<!-- TOOLBAR -->

<div class="toolbar">


<div class="product-count">

    <i class="bi bi-box-seam me-1"></i>

    Danh sách sản phẩm

</div>


<div class="search-box">

    <i class="bi bi-search"></i>

    <input
        type="text"
        id="productSearch"
        placeholder="Tìm sản phẩm..."
    >

</div>


</div>



<!-- PRODUCT TABLE -->

<div class="product-card">


<div class="table-wrapper">


<table class="product-table">


<thead>

<tr>

<th>
    ID
</th>

<th>
    Hình ảnh
</th>

<th>
    Sản phẩm
</th>

<th>
    Danh mục
</th>

<th>
    Giá
</th>

<th>
    Tồn kho
</th>

<th>
    Thao tác
</th>

</tr>

</thead>


<tbody id="productTable">


<?php if (
    $result
    &&
    $result->num_rows > 0
): ?>


<?php while (
    $product =
    $result->fetch_assoc()
): ?>


<tr>


<!-- ID -->

<td>

    <span class="product-id">

        #<?= $product["id"] ?>

    </span>

</td>



<!-- IMAGE -->

<td>


<?php if (
    !empty(
        $product["image"]
    )
): ?>


<img
    src="../images/<?= htmlspecialchars(
        $product["image"]
    ) ?>"
    alt="<?= htmlspecialchars(
        $product["name"]
    ) ?>"
    class="product-image"
>


<?php else: ?>


<div class="no-image">

    <i class="bi bi-image me-1"></i>

    Không có ảnh

</div>


<?php endif; ?>


</td>



<!-- PRODUCT -->

<td>


<div class="product-name">

    <?= htmlspecialchars(
        $product["name"]
    ) ?>

</div>


<?php if (
    !empty(
        $product["description"]
    )
): ?>


<div class="product-description">

    <?= htmlspecialchars(
        $product["description"]
    ) ?>

</div>


<?php endif; ?>


</td>



<!-- CATEGORY -->

<td>


<span class="category-badge">

    <i class="bi bi-tag"></i>

    <?= htmlspecialchars(
        $product["category_name"]
        ??
        "Chưa phân loại"
    ) ?>

</span>


</td>



<!-- PRICE -->

<td>


<div class="product-price">

    <?= number_format(
        $product["price"],
        0,
        ",",
        "."
    ) ?>

    ₫

</div>


</td>



<!-- STOCK -->

<td>


<?php if (
    $product["stock"] > 0
): ?>


<span class="stock-badge stock-good">

    <span class="stock-dot"></span>

    Còn <?= $product["stock"] ?>

</span>


<?php else: ?>


<span class="stock-badge stock-empty">

    <span class="stock-dot"></span>

    Hết hàng

</span>


<?php endif; ?>


</td>



<!-- ACTION -->

<td>


<a
    href="xoa-san-pham.php?id=<?= $product["id"] ?>"
    class="btn-delete"
    onclick="
        return confirm(
            'Bạn có chắc muốn xóa sản phẩm này không?'
        );
    "
>

    <i class="bi bi-trash3"></i>

    Xóa

</a>


</td>


</tr>


<?php endwhile; ?>


<?php else: ?>


<tr>


<td colspan="7">


<div class="empty-products">


<div class="empty-icon">

    <i class="bi bi-box-seam"></i>

</div>


<h4>

    Chưa có sản phẩm

</h4>


<p>

    Hiện tại cửa hàng chưa có sản phẩm nào.

</p>


<a
    href="them-san-pham.php"
    class="btn-add"
>

    <i class="bi bi-plus-lg"></i>

    Thêm sản phẩm đầu tiên

</a>


</div>


</td>


</tr>


<?php endif; ?>


</tbody>


</table>


</div>


</div>



<!-- BACK -->

<div class="mt-4">


<a
    href="index.php"
    class="back-btn"
>

    <i class="bi bi-arrow-left"></i>

    Quay lại quản trị

</a>


</div>


</div>


</main>



<!-- =========================
     SEARCH JAVASCRIPT
========================= -->

<script>

const searchInput =
    document.getElementById(
        "productSearch"
    );

const rows =
    document.querySelectorAll(
        "#productTable tr"
    );


searchInput.addEventListener(
    "input",
    function () {

        const keyword =
            this.value
                .toLowerCase()
                .trim();


        rows.forEach(
            function (row) {

                const text =
                    row.textContent
                        .toLowerCase();


                if (
                    text.includes(
                        keyword
                    )
                ) {

                    row.style.display = "";

                } else {

                    row.style.display = "none";

                }

            }
        );

    }
);

</script>


</body>

</html>
