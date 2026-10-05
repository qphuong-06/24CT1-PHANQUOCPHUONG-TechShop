<?php

session_start();

require_once "config/database.php";

/*
|--------------------------------------------------------------------------
| Tìm kiếm
|--------------------------------------------------------------------------
*/

$search = isset($_GET["search"])
    ? trim($_GET["search"])
    : "";


/*
|--------------------------------------------------------------------------
| Lọc danh mục
|--------------------------------------------------------------------------
*/

$category_id = isset($_GET["category"])
    ? (int) $_GET["category"]
    : 0;


/*
|--------------------------------------------------------------------------
| Lấy danh mục
|--------------------------------------------------------------------------
*/

$categories = $conn->query("
    SELECT *
    FROM categories
    ORDER BY name ASC
");


/*
|--------------------------------------------------------------------------
| Lấy sản phẩm
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        products.*,
        categories.name AS category_name
    FROM products
    LEFT JOIN categories
        ON products.category_id = categories.id
    WHERE 1=1
";


$params = [];
$types = "";


/*
|--------------------------------------------------------------------------
| Điều kiện tìm kiếm
|--------------------------------------------------------------------------
*/

if ($search !== "") {

    $sql .= "
        AND (
            products.name LIKE ?
            OR products.description LIKE ?
        )
    ";

    $search_value = "%" . $search . "%";

    $params[] = $search_value;
    $params[] = $search_value;

    $types .= "ss";
}


/*
|--------------------------------------------------------------------------
| Điều kiện danh mục
|--------------------------------------------------------------------------
*/

if ($category_id > 0) {

    $sql .= "
        AND products.category_id = ?
    ";

    $params[] = $category_id;

    $types .= "i";
}


$sql .= "
    ORDER BY products.id DESC
";


$stmt = $conn->prepare($sql);


if (!empty($params)) {

    $stmt->bind_param(
        $types,
        ...$params
    );

}


$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Sản phẩm | TechShop</title>


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

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            background: #f6f8fb;

            color: #172033;

            font-family:
                "Segoe UI",
                Arial,
                sans-serif;

        }


        /* =========================
           NAVBAR
        ========================= */

        .main-navbar {

            background:
                rgba(10, 18, 35, 0.97);

            padding: 14px 0;

            position: sticky;

            top: 0;

            z-index: 1000;

            box-shadow:
                0 5px 25px
                rgba(0, 0, 0, 0.12);

        }


        .brand {

            color: white !important;

            font-size: 25px;

            font-weight: 800;

            text-decoration: none;

        }


        .brand-icon {

            display: inline-flex;

            width: 40px;

            height: 40px;

            align-items: center;

            justify-content: center;

            border-radius: 12px;

            background:
                linear-gradient(
                    135deg,
                    #0d6efd,
                    #6f42c1
                );

            margin-right: 8px;

        }


        .nav-link-custom {

            color: #dce3ef !important;

            text-decoration: none;

            font-weight: 500;

            margin: 0 7px;

            transition: 0.25s;

        }


        .nav-link-custom:hover {

            color: white !important;

        }


        /* =========================
           PAGE HEADER
        ========================= */

        .page-header {

            padding: 70px 20px;

            color: white;

            background:

                radial-gradient(
                    circle at 80% 20%,
                    rgba(13,110,253,0.4),
                    transparent 30%
                ),

                linear-gradient(
                    135deg,
                    #07111f,
                    #17294a
                );

        }


        .page-header h1 {

            font-size:
                clamp(38px, 5vw, 58px);

            font-weight: 800;

            letter-spacing: -1px;

        }


        .page-header p {

            color: #c7d1df;

            font-size: 17px;

        }


        /* =========================
           SEARCH BOX
        ========================= */

        .search-box {

            background: white;

            border-radius: 18px;

            padding: 20px;

            box-shadow:
                0 12px 35px
                rgba(24,39,75,0.08);

            margin-top: -35px;

            position: relative;

            z-index: 5;

        }


        .search-input {

            height: 50px;

            border-radius: 11px;

            border: 1px solid #e1e6ef;

            padding-left: 18px;

        }


        .search-input:focus {

            border-color: #0d6efd;

            box-shadow:
                0 0 0 3px
                rgba(13,110,253,0.1);

        }


        .search-button {

            height: 50px;

            border-radius: 11px;

            font-weight: 700;

        }


        /* =========================
           FILTER
        ========================= */

        .filter-card {

            background: white;

            border-radius: 18px;

            padding: 22px;

            border: 1px solid #edf0f5;

            box-shadow:
                0 10px 30px
                rgba(24,39,75,0.05);

        }


        .filter-title {

            font-weight: 800;

            font-size: 17px;

            margin-bottom: 15px;

        }


        .category-link {

            display: block;

            padding: 10px 13px;

            border-radius: 10px;

            color: #566174;

            text-decoration: none;

            margin-bottom: 5px;

            transition: 0.2s;

        }


        .category-link:hover {

            background: #eef5ff;

            color: #0d6efd;

        }


        .category-link.active {

            background: #0d6efd;

            color: white;

        }


        /* =========================
           PRODUCT CARD
        ========================= */

        .product-card {

            background: white;

            border: 1px solid #edf0f5;

            border-radius: 20px;

            overflow: hidden;

            height: 100%;

            transition: 0.3s;

        }


        .product-card:hover {

            transform:
                translateY(-8px);

            box-shadow:
                0 20px 45px
                rgba(23,32,51,0.12);

        }


        .product-image-wrapper {

            height: 245px;

            background: #f7f8fa;

            overflow: hidden;

            position: relative;

        }


        .product-image {

            width: 100%;

            height: 100%;

            object-fit: cover;

            transition: 0.45s;

        }


        .product-card:hover
        .product-image {

            transform: scale(1.07);

        }


        .product-badge {

            position: absolute;

            top: 15px;

            left: 15px;

            z-index: 2;

            background: #0d6efd;

            color: white;

            padding: 6px 11px;

            border-radius: 50px;

            font-size: 11px;

            font-weight: 800;

        }


        .stock-badge {

            position: absolute;

            top: 15px;

            right: 15px;

            z-index: 2;

            background: white;

            padding: 6px 10px;

            border-radius: 50px;

            font-size: 11px;

            font-weight: 700;

            box-shadow:
                0 3px 10px
                rgba(0,0,0,0.08);

        }


        .product-body {

            padding: 20px;

        }


        .product-category {

            color: #0d6efd;

            font-size: 11px;

            text-transform: uppercase;

            font-weight: 800;

            letter-spacing: 0.8px;

        }


        .product-name {

            font-size: 18px;

            font-weight: 700;

            margin-top: 8px;

            min-height: 48px;

        }


        .product-description {

            color: #7b8494;

            font-size: 14px;

            line-height: 1.6;

            min-height: 45px;

        }


        .price {

            color: #e63946;

            font-size: 21px;

            font-weight: 800;

        }


        .detail-button {

            border-radius: 10px;

            padding: 10px;

            font-weight: 700;

        }


        /* =========================
           FOOTER
        ========================= */

        .footer {

            background: #07111f;

            color: #b8c2d1;

            margin-top: 80px;

            padding: 55px 0 25px;

        }


        .footer-title {

            color: white;

            font-weight: 800;

            margin-bottom: 15px;

        }


        .footer a {

            color: #b8c2d1;

            text-decoration: none;

            display: block;

            margin-bottom: 8px;

        }


        .footer a:hover {

            color: white;

        }


        .footer-bottom {

            border-top:
                1px solid
                rgba(255,255,255,0.08);

            margin-top: 35px;

            padding-top: 20px;

            text-align: center;

            font-size: 14px;

        }


        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 768px) {

            .product-image-wrapper {

                height: 220px;

            }

            .page-header {

                padding: 55px 20px;

            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="main-navbar">

    <div class="container">

        <div
            class="d-flex align-items-center justify-content-between flex-wrap gap-3"
        >

            <a
                href="index.php"
                class="brand"
            >

                <span class="brand-icon">

                    <i class="bi bi-cpu"></i>

                </span>

                TechShop

            </a>


            <div class="d-flex align-items-center flex-wrap">

                <a
                    href="index.php"
                    class="nav-link-custom"
                >
                    Trang chủ
                </a>


                <a
                    href="san-pham.php"
                    class="nav-link-custom"
                    style="color:white !important;"
                >
                    Sản phẩm
                </a>


                <a
                    href="gio-hang.php"
                    class="nav-link-custom"
                >

                    <i class="bi bi-cart3"></i>

                    Giỏ hàng

                </a>


                <?php if (isset($_SESSION["user_id"])): ?>

                    <span
                        class="text-white mx-2"
                        style="font-size:14px;"
                    >

                        Xin chào,

                        <strong>
                            <?= htmlspecialchars(
                                $_SESSION["fullname"]
                            ) ?>
                        </strong>

                    </span>


                    <?php if (
                        $_SESSION["role"] === "admin"
                        || $_SESSION["role"] === "manager"
                    ): ?>

                        <a
                            href="admin/index.php"
                            class="btn btn-warning btn-sm me-2"
                        >

                            <i class="bi bi-speedometer2"></i>

                            Quản trị

                        </a>

                    <?php endif; ?>


                    <a
                        href="dang-xuat.php"
                        class="btn btn-outline-danger btn-sm"
                    >
                        Đăng xuất
                    </a>


                <?php else: ?>

                    <a
                        href="dang-nhap.php"
                        class="btn btn-outline-light btn-sm me-2"
                    >
                        Đăng nhập
                    </a>


                    <a
                        href="dang-ky.php"
                        class="btn btn-primary btn-sm"
                    >
                        Đăng ký
                    </a>

                <?php endif; ?>

            </div>

        </div>

    </div>

</nav>



<!-- =====================================================
     HEADER
===================================================== -->

<section class="page-header">

    <div class="container">

        <div class="text-center">

            <div
                class="mb-3"
                style="
                    color:#74b2ff;
                    font-weight:700;
                    letter-spacing:1.5px;
                "
            >

                TECHSHOP COLLECTION

            </div>


            <h1>
                Khám phá sản phẩm
            </h1>


            <p class="mt-3 mb-0">

                Phụ kiện công nghệ chất lượng
                cho học tập, làm việc và giải trí.

            </p>

        </div>

    </div>

</section>



<!-- =====================================================
     SEARCH
===================================================== -->

<div class="container">

    <div class="search-box">

        <form
            method="GET"
            action="san-pham.php"
        >

            <div class="row g-2">

                <div class="col-md-9">

                    <div class="position-relative">

                        <i
                            class="bi bi-search position-absolute"
                            style="
                                left:17px;
                                top:16px;
                                color:#8a93a3;
                            "
                        ></i>


                        <input
                            type="text"
                            name="search"
                            class="form-control search-input"
                            style="padding-left:45px;"
                            placeholder="Tìm kiếm tai nghe, chuột, bàn phím..."
                            value="<?= htmlspecialchars($search) ?>"
                        >

                    </div>

                </div>


                <div class="col-md-3">

                    <button
                        type="submit"
                        class="btn btn-primary w-100 search-button"
                    >

                        <i class="bi bi-search"></i>

                        Tìm sản phẩm

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>



<!-- =====================================================
     PRODUCTS
===================================================== -->

<div class="container py-5">

    <div class="row g-4">


        <!-- FILTER -->

        <div class="col-lg-3">

            <div class="filter-card">

                <div class="filter-title">

                    <i class="bi bi-funnel"></i>

                    Danh mục

                </div>


                <a
                    href="san-pham.php"
                    class="category-link
                    <?= $category_id === 0 ? "active" : "" ?>"
                >

                    <i class="bi bi-grid"></i>

                    Tất cả sản phẩm

                </a>


                <?php if ($categories): ?>

                    <?php while (
                        $category = $categories->fetch_assoc()
                    ): ?>

                        <a
                            href="san-pham.php?category=<?= $category["id"] ?>"
                            class="category-link
                            <?= $category_id === (int)$category["id"]
                                ? "active"
                                : "" ?>"
                        >

                            <i class="bi bi-chevron-right"></i>

                            <?= htmlspecialchars(
                                $category["name"]
                            ) ?>

                        </a>

                    <?php endwhile; ?>

                <?php endif; ?>

            </div>

        </div>



        <!-- PRODUCT LIST -->

        <div class="col-lg-9">


            <div
                class="d-flex justify-content-between align-items-center mb-4"
            >

                <div>

                    <h3
                        class="fw-bold mb-1"
                    >

                        <?php if ($search !== ""): ?>

                            Kết quả tìm kiếm

                        <?php else: ?>

                            Tất cả sản phẩm

                        <?php endif; ?>

                    </h3>


                    <?php if ($search !== ""): ?>

                        <div class="text-muted">

                            Từ khóa:
                            <strong>
                                <?= htmlspecialchars($search) ?>
                            </strong>

                        </div>

                    <?php endif; ?>

                </div>

            </div>



            <div class="row g-4">


                <?php if (
                    $result
                    && $result->num_rows > 0
                ): ?>


                    <?php while (
                        $product = $result->fetch_assoc()
                    ): ?>

                        <div class="col-md-6 col-xl-4">


                            <div class="product-card">


                                <!-- IMAGE -->

                                <div
                                    class="product-image-wrapper"
                                >


                                    <span class="product-badge">

                                        MỚI

                                    </span>


                                    <?php if (
                                        (int)$product["stock"] > 0
                                    ): ?>

                                        <span
                                            class="stock-badge text-success"
                                        >

                                            <i
                                                class="bi bi-check-circle-fill"
                                            ></i>

                                            Còn hàng

                                        </span>

                                    <?php else: ?>

                                        <span
                                            class="stock-badge text-danger"
                                        >

                                            <i
                                                class="bi bi-x-circle-fill"
                                            ></i>

                                            Hết hàng

                                        </span>

                                    <?php endif; ?>


                                    <?php if (
                                        !empty($product["image"])
                                    ): ?>

                                        <img
                                            src="images/<?= htmlspecialchars(
                                                $product["image"]
                                            ) ?>"
                                            class="product-image"
                                            alt="<?= htmlspecialchars(
                                                $product["name"]
                                            ) ?>"
                                        >

                                    <?php else: ?>

                                        <div
                                            class="
                                                h-100
                                                d-flex
                                                align-items-center
                                                justify-content-center
                                                text-muted
                                            "
                                        >

                                            <i
                                                class="bi bi-image"
                                                style="
                                                    font-size:45px;
                                                "
                                            ></i>

                                        </div>

                                    <?php endif; ?>

                                </div>



                                <!-- BODY -->

                                <div class="product-body">


                                    <div class="product-category">

                                        <?= htmlspecialchars(
                                            $product["category_name"]
                                            ?? "Chưa phân loại"
                                        ) ?>

                                    </div>


                                    <div class="product-name">

                                        <?= htmlspecialchars(
                                            $product["name"]
                                        ) ?>

                                    </div>


                                    <div
                                        class="product-description"
                                    >

                                        <?= htmlspecialchars(
                                            mb_strimwidth(
                                                $product["description"],
                                                0,
                                                90,
                                                "..."
                                            )
                                        ) ?>

                                    </div>


                                    <div
                                        class="price mt-3"
                                    >

                                        <?= number_format(
                                            $product["price"],
                                            0,
                                            ",",
                                            "."
                                        ) ?>

                                        ₫

                                    </div>


                                    <a
                                        href="chi-tiet.php?id=<?= $product["id"] ?>"
                                        class="
                                            btn
                                            btn-primary
                                            detail-button
                                            w-100
                                            mt-3
                                        "
                                    >

                                        <i
                                            class="bi bi-eye"
                                        ></i>

                                        Xem chi tiết

                                    </a>

                                </div>

                            </div>

                        </div>


                    <?php endwhile; ?>


                <?php else: ?>


                    <div class="col-12">

                        <div
                            class="text-center bg-white rounded-4 p-5"
                        >

                            <i
                                class="bi bi-search"
                                style="
                                    font-size:55px;
                                    color:#b8c2d1;
                                "
                            ></i>


                            <h4 class="mt-3">

                                Không tìm thấy sản phẩm

                            </h4>


                            <p class="text-muted">

                                Hãy thử từ khóa khác hoặc
                                xem tất cả sản phẩm.

                            </p>


                            <a
                                href="san-pham.php"
                                class="btn btn-primary"
                            >

                                Xem tất cả sản phẩm

                            </a>

                        </div>

                    </div>


                <?php endif; ?>


            </div>

        </div>

    </div>

</div>



<!-- =====================================================
     FOOTER
===================================================== -->

<footer class="footer">

    <div class="container">

        <div class="row g-5">


            <div class="col-lg-5">

                <div class="footer-title fs-4">

                    <i class="bi bi-cpu"></i>

                    TechShop

                </div>

                <p>

                    Phụ kiện công nghệ chất lượng
                    cho học tập, làm việc và giải trí.

                </p>

            </div>


            <div class="col-6 col-lg-2">

                <div class="footer-title">

                    Mua sắm

                </div>

                <a href="index.php">
                    Trang chủ
                </a>

                <a href="san-pham.php">
                    Sản phẩm
                </a>

                <a href="gio-hang.php">
                    Giỏ hàng
                </a>

            </div>


            <div class="col-6 col-lg-2">

                <div class="footer-title">

                    Tài khoản

                </div>

                <a href="dang-nhap.php">
                    Đăng nhập
                </a>

                <a href="dang-ky.php">
                    Đăng ký
                </a>

                <?php if (isset($_SESSION["user_id"])): ?>

                    <a href="dang-xuat.php">
                        Đăng xuất
                    </a>

                <?php endif; ?>

            </div>


            <div class="col-lg-3">

                <div class="footer-title">

                    Liên hệ

                </div>

                <p>

                    <i class="bi bi-telephone"></i>

                    0900 000 000

                </p>

                <p>

                    <i class="bi bi-envelope"></i>

                    support@techshop.vn

                </p>

            </div>

        </div>


        <div class="footer-bottom">

            © 2026 TechShop.
            All rights reserved.

        </div>

    </div>

</footer>


</body>

</html>