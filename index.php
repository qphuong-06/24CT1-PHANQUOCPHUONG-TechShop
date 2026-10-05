<?php

session_start();

require_once "config/database.php";

/*
|--------------------------------------------------------------------------
| Lấy sản phẩm mới nhất
|--------------------------------------------------------------------------
*/

$result = $conn->query("
    SELECT
        products.*,
        categories.name AS category_name
    FROM products
    LEFT JOIN categories
        ON products.category_id = categories.id
    ORDER BY products.id DESC
    LIMIT 8
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

    <title>TechShop | Phụ kiện công nghệ</title>

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
            background: rgba(10, 18, 35, 0.97);
            backdrop-filter: blur(12px);
            padding: 14px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.12);
        }

        .brand {
            color: white !important;
            font-size: 25px;
            font-weight: 800;
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .brand-icon {
            display: inline-flex;
            width: 40px;
            height: 40px;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: linear-gradient(
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
            color: #ffffff !important;
        }


        /* =========================
           HERO
        ========================= */

        .hero {
            position: relative;
            overflow: hidden;
            padding: 105px 20px;
            color: white;

            background:
                radial-gradient(
                    circle at 85% 20%,
                    rgba(13, 110, 253, 0.45),
                    transparent 30%
                ),
                radial-gradient(
                    circle at 15% 80%,
                    rgba(111, 66, 193, 0.35),
                    transparent 30%
                ),
                linear-gradient(
                    135deg,
                    #07111f,
                    #101d35 55%,
                    #17294a
                );
        }

        .hero::before {
            content: "";
            position: absolute;
            width: 450px;
            height: 450px;
            border-radius: 50%;
            background: rgba(13, 110, 253, 0.08);
            right: -150px;
            top: -150px;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-badge {
            display: inline-block;
            padding: 8px 16px;
            border: 1px solid rgba(255,255,255,0.2);
            background: rgba(255,255,255,0.08);
            border-radius: 50px;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .hero h1 {
            font-size: clamp(42px, 6vw, 70px);
            line-height: 1.05;
            font-weight: 800;
            letter-spacing: -2px;
        }

        .hero h1 span {
            background: linear-gradient(
                90deg,
                #5da9ff,
                #9c7bff
            );
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-description {
            color: #c6d0df;
            max-width: 650px;
            font-size: 18px;
            line-height: 1.7;
        }

        .hero-btn {
            border: none;
            border-radius: 12px;
            padding: 13px 25px;
            font-weight: 700;
            background: #0d6efd;
            color: white;
            transition: 0.25s;
        }

        .hero-btn:hover {
            background: #0b5ed7;
            transform: translateY(-2px);
            color: white;
        }

        .hero-btn-outline {
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 12px;
            padding: 13px 25px;
            font-weight: 600;
            background: rgba(255,255,255,0.05);
            color: white;
            text-decoration: none;
            transition: 0.25s;
        }

        .hero-btn-outline:hover {
            background: white;
            color: #101d35;
        }


        /* =========================
           SECTION
        ========================= */

        .section {
            padding: 75px 0;
        }

        .section-label {
            color: #0d6efd;
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .section-title {
            font-size: 34px;
            font-weight: 800;
            margin-top: 7px;
        }

        .section-description {
            color: #6c757d;
        }


        /* =========================
           CATEGORY
        ========================= */

        .category-card {
            background: white;
            border-radius: 18px;
            padding: 28px 20px;
            text-align: center;
            text-decoration: none;
            color: #172033;
            display: block;
            border: 1px solid #edf0f5;
            transition: 0.3s;
            height: 100%;
        }

        .category-card:hover {
            transform: translateY(-7px);
            box-shadow:
                0 18px 45px rgba(24, 39, 75, 0.10);
            color: #0d6efd;
        }

        .category-icon {
            width: 65px;
            height: 65px;
            margin: auto;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            background: #eef5ff;
            color: #0d6efd;
            margin-bottom: 17px;
        }

        .category-card h5 {
            font-weight: 700;
            margin-bottom: 5px;
        }

        .category-card small {
            color: #8a93a3;
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
            transform: translateY(-8px);
            box-shadow:
                0 20px 45px rgba(23, 32, 51, 0.12);
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

        .product-card:hover .product-image {
            transform: scale(1.06);
        }

        .product-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: #0d6efd;
            color: white;
            padding: 6px 11px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 700;
        }

        .product-body {
            padding: 20px;
        }

        .product-category {
            font-size: 12px;
            color: #0d6efd;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.7px;
        }

        .product-name {
            font-size: 18px;
            font-weight: 700;
            color: #172033;
            margin: 8px 0;
            min-height: 45px;
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

        .product-btn {
            border-radius: 10px;
            font-weight: 600;
            padding: 10px;
        }


        /* =========================
           SERVICE
        ========================= */

        .service-section {
            background: white;
            border-top: 1px solid #edf0f5;
            border-bottom: 1px solid #edf0f5;
        }

        .service-item {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .service-icon {
            min-width: 50px;
            height: 50px;
            border-radius: 14px;
            background: #eef5ff;
            color: #0d6efd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .service-item h6 {
            font-weight: 700;
            margin: 0 0 3px;
        }

        .service-item small {
            color: #7b8494;
        }


        /* =========================
           FOOTER
        ========================= */

        .footer {
            background: #07111f;
            color: #b8c2d1;
            padding: 60px 0 25px;
        }

        .footer-title {
            color: white;
            font-weight: 800;
            margin-bottom: 18px;
        }

        .footer a {
            color: #b8c2d1;
            text-decoration: none;
            display: block;
            margin-bottom: 9px;
            transition: 0.2s;
        }

        .footer a:hover {
            color: white;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.08);
            margin-top: 40px;
            padding-top: 20px;
            text-align: center;
            font-size: 14px;
        }


        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 991px) {

            .nav-link-custom {
                display: inline-block;
                margin-top: 5px;
            }

            .hero {
                padding: 80px 20px;
            }

        }

        @media (max-width: 576px) {

            .hero h1 {
                font-size: 42px;
            }

            .section {
                padding: 55px 0;
            }

            .product-image-wrapper {
                height: 220px;
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

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">

            <!-- LOGO -->

            <a
                href="index.php"
                class="brand"
            >

                <span class="brand-icon">
                    <i class="bi bi-cpu"></i>
                </span>

                TechShop

            </a>


            <!-- MENU -->

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
                        style="font-size: 14px;"
                    >

                        Xin chào,
                        <strong>
                            <?= htmlspecialchars($_SESSION["fullname"]) ?>
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
     HERO
===================================================== -->

<section class="hero">

    <div class="container hero-content">

        <div class="row align-items-center">

            <div class="col-lg-8">

                <div class="hero-badge">
                    <i class="bi bi-stars"></i>
                    Công nghệ hiện đại · Giá tốt mỗi ngày
                </div>

                <h1>
                    Nâng cấp
                    <span>trải nghiệm</span>
                    công nghệ.
                </h1>

                <p class="hero-description mt-4">

                    Khám phá bộ sưu tập phụ kiện công nghệ
                    chất lượng dành cho học tập, làm việc
                    và giải trí.

                </p>


                <div class="d-flex gap-3 flex-wrap mt-4">

                    <a
                        href="san-pham.php"
                        class="hero-btn"
                    >
                        <i class="bi bi-bag-check"></i>
                        Khám phá sản phẩm
                    </a>

                    <a
                        href="#san-pham"
                        class="hero-btn-outline"
                    >
                        Xem sản phẩm nổi bật
                    </a>

                </div>

            </div>

        </div>

    </div>

</section>



<!-- =====================================================
     CATEGORY
===================================================== -->

<section class="section">

    <div class="container">

        <div class="text-center mb-5">

            <div class="section-label">
                Danh mục sản phẩm
            </div>

            <h2 class="section-title">
                Khám phá theo nhu cầu
            </h2>

            <p class="section-description">
                Những phụ kiện công nghệ được lựa chọn phổ biến.
            </p>

        </div>


        <div class="row g-4">

            <div class="col-6 col-lg-3">

                <a
                    href="san-pham.php"
                    class="category-card"
                >

                    <div class="category-icon">
                        <i class="bi bi-headphones"></i>
                    </div>

                    <h5>Tai nghe</h5>

                    <small>
                        Bluetooth & Gaming
                    </small>

                </a>

            </div>


            <div class="col-6 col-lg-3">

                <a
                    href="san-pham.php"
                    class="category-card"
                >

                    <div class="category-icon">
                        <i class="bi bi-mouse3"></i>
                    </div>

                    <h5>Chuột</h5>

                    <small>
                        Gaming & Văn phòng
                    </small>

                </a>

            </div>


            <div class="col-6 col-lg-3">

                <a
                    href="san-pham.php"
                    class="category-card"
                >

                    <div class="category-icon">
                        <i class="bi bi-keyboard"></i>
                    </div>

                    <h5>Bàn phím</h5>

                    <small>
                        Cơ & Không dây
                    </small>

                </a>

            </div>


            <div class="col-6 col-lg-3">

                <a
                    href="san-pham.php"
                    class="category-card"
                >

                    <div class="category-icon">
                        <i class="bi bi-lightning-charge"></i>
                    </div>

                    <h5>Cáp & Sạc</h5>

                    <small>
                        Sạc nhanh & phụ kiện
                    </small>

                </a>

            </div>

        </div>

    </div>

</section>



<!-- =====================================================
     PRODUCTS
===================================================== -->

<section
    class="section pt-4"
    id="san-pham"
>

    <div class="container">

        <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">

            <div>

                <div class="section-label">
                    Sản phẩm mới
                </div>

                <h2 class="section-title mb-1">
                    Sản phẩm nổi bật
                </h2>

                <p class="section-description mb-0">
                    Những sản phẩm mới nhất tại TechShop.
                </p>

            </div>


            <a
                href="san-pham.php"
                class="btn btn-outline-primary"
            >
                Xem tất cả
                <i class="bi bi-arrow-right"></i>
            </a>

        </div>


        <div class="row g-4">


            <?php if ($result && $result->num_rows > 0): ?>


                <?php while ($product = $result->fetch_assoc()): ?>

                    <div class="col-sm-6 col-lg-3">


                        <div class="product-card">


                            <!-- IMAGE -->

                            <div class="product-image-wrapper">

                                <span class="product-badge">
                                    MỚI
                                </span>


                                <?php if (!empty($product["image"])): ?>

                                    <img
                                        src="images/<?= htmlspecialchars($product["image"]) ?>"
                                        class="product-image"
                                        alt="<?= htmlspecialchars($product["name"]) ?>"
                                    >

                                <?php else: ?>

                                    <div
                                        class="h-100 d-flex align-items-center justify-content-center text-muted"
                                    >
                                        <i
                                            class="bi bi-image"
                                            style="font-size: 45px;"
                                        ></i>
                                    </div>

                                <?php endif; ?>

                            </div>


                            <!-- BODY -->

                            <div class="product-body">

                                <div class="product-category">

                                    <?= htmlspecialchars(
                                        $product["category_name"]
                                    ) ?>

                                </div>


                                <div class="product-name">

                                    <?= htmlspecialchars(
                                        $product["name"]
                                    ) ?>

                                </div>


                                <div class="product-description">

                                    <?= htmlspecialchars(
                                        mb_strimwidth(
                                            $product["description"],
                                            0,
                                            75,
                                            "..."
                                        )
                                    ) ?>

                                </div>


                                <div
                                    class="d-flex justify-content-between align-items-center mt-3"
                                >

                                    <div class="price">

                                        <?= number_format(
                                            $product["price"],
                                            0,
                                            ",",
                                            "."
                                        ) ?>

                                        ₫

                                    </div>

                                </div>


                                <div class="mt-3">

                                    <a
                                        href="chi-tiet.php?id=<?= $product["id"] ?>"
                                        class="btn btn-primary product-btn w-100"
                                    >

                                        <i class="bi bi-eye"></i>
                                        Xem chi tiết

                                    </a>

                                </div>

                            </div>

                        </div>

                    </div>


                <?php endwhile; ?>


            <?php else: ?>

                <div class="col-12">

                    <div class="alert alert-info">

                        Chưa có sản phẩm nào.

                    </div>

                </div>

            <?php endif; ?>


        </div>

    </div>

</section>



<!-- =====================================================
     SERVICE
===================================================== -->

<section class="service-section py-5">

    <div class="container">

        <div class="row g-4">


            <div class="col-md-3">

                <div class="service-item">

                    <div class="service-icon">
                        <i class="bi bi-truck"></i>
                    </div>

                    <div>

                        <h6>
                            Giao hàng nhanh
                        </h6>

                        <small>
                            Giao hàng tận nơi
                        </small>

                    </div>

                </div>

            </div>


            <div class="col-md-3">

                <div class="service-item">

                    <div class="service-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>

                    <div>

                        <h6>
                            Sản phẩm chất lượng
                        </h6>

                        <small>
                            Kiểm tra trước khi giao
                        </small>

                    </div>

                </div>

            </div>


            <div class="col-md-3">

                <div class="service-item">

                    <div class="service-icon">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>

                    <div>

                        <h6>
                            Hỗ trợ đổi trả
                        </h6>

                        <small>
                            Hỗ trợ khách hàng tận tình
                        </small>

                    </div>

                </div>

            </div>


            <div class="col-md-3">

                <div class="service-item">

                    <div class="service-icon">
                        <i class="bi bi-headset"></i>
                    </div>

                    <div>

                        <h6>
                            Hỗ trợ khách hàng
                        </h6>

                        <small>
                            Tư vấn nhanh chóng
                        </small>

                    </div>

                </div>

            </div>


        </div>

    </div>

</section>



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

                    Website bán phụ kiện công nghệ trực tuyến,
                    mang đến những sản phẩm tiện ích cho học tập,
                    làm việc và giải trí.

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

                <p class="mb-2">
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