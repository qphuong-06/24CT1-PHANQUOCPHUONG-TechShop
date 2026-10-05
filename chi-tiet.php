<?php

session_start();

require_once "config/database.php";


/*
|--------------------------------------------------------------------------
| Lấy ID sản phẩm
|--------------------------------------------------------------------------
*/

$id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;


if ($id <= 0) {

    die("Sản phẩm không hợp lệ.");

}


/*
|--------------------------------------------------------------------------
| Lấy sản phẩm
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        products.*,
        categories.name AS category_name
    FROM products
    LEFT JOIN categories
        ON products.category_id = categories.id
    WHERE products.id = ?
");


$stmt->bind_param(
    "i",
    $id
);


$stmt->execute();


$result = $stmt->get_result();


$product = $result->fetch_assoc();


$stmt->close();


if (!$product) {

    die("Không tìm thấy sản phẩm.");

}


/*
|--------------------------------------------------------------------------
| Thêm sản phẩm vào giỏ hàng
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $quantity = isset($_POST["quantity"])
        ? (int) $_POST["quantity"]
        : 1;


    if ($quantity < 1) {

        $quantity = 1;

    }


    if ($quantity > $product["stock"]) {

        $quantity = $product["stock"];

    }


    if (!isset($_SESSION["cart"])) {

        $_SESSION["cart"] = [];

    }


    if (isset($_SESSION["cart"][$id])) {

        $_SESSION["cart"][$id] += $quantity;

    } else {

        $_SESSION["cart"][$id] = $quantity;

    }


    if (
        $_SESSION["cart"][$id]
        > $product["stock"]
    ) {

        $_SESSION["cart"][$id]
            = $product["stock"];

    }


    header(
        "Location: gio-hang.php"
    );

    exit;

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

    <title>
        <?= htmlspecialchars(
            $product["name"]
        ) ?>
        | TechShop
    </title>


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
                rgba(0,0,0,0.12);

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

            color: #dce3ef;

            text-decoration: none;

            font-weight: 500;

            margin: 0 7px;

        }


        .nav-link-custom:hover {

            color: white;

        }


        /* =========================
           PAGE HEADER
        ========================= */

        .page-header {

            padding: 55px 20px;

            color: white;

            background:

                radial-gradient(
                    circle at 85% 20%,
                    rgba(13,110,253,0.4),
                    transparent 30%
                ),

                linear-gradient(
                    135deg,
                    #07111f,
                    #17294a
                );

        }


        .breadcrumb-custom {

            color: #aebbd0;

            font-size: 14px;

        }


        .breadcrumb-custom a {

            color: #74b2ff;

            text-decoration: none;

        }


        /* =========================
           PRODUCT DETAIL
        ========================= */

        .product-detail-card {

            background: white;

            border-radius: 24px;

            border: 1px solid #edf0f5;

            padding: 35px;

            box-shadow:
                0 15px 45px
                rgba(23,32,51,0.08);

        }


        /* =========================
           IMAGE
        ========================= */

        .product-image-box {

            background: #f7f8fa;

            border-radius: 20px;

            overflow: hidden;

            height: 500px;

            display: flex;

            align-items: center;

            justify-content: center;

            position: relative;

        }


        .product-image {

            width: 100%;

            height: 100%;

            object-fit: cover;

            transition: 0.4s;

        }


        .product-image-box:hover
        .product-image {

            transform: scale(1.03);

        }


        .image-badge {

            position: absolute;

            top: 20px;

            left: 20px;

            z-index: 2;

            background: #0d6efd;

            color: white;

            padding: 8px 14px;

            border-radius: 50px;

            font-size: 12px;

            font-weight: 800;

        }


        /* =========================
           PRODUCT INFO
        ========================= */

        .category-badge {

            display: inline-block;

            background: #eef5ff;

            color: #0d6efd;

            padding: 7px 12px;

            border-radius: 50px;

            font-size: 12px;

            font-weight: 800;

            text-transform: uppercase;

            letter-spacing: 0.6px;

        }


        .product-title {

            font-size:
                clamp(30px, 4vw, 44px);

            line-height: 1.15;

            font-weight: 800;

            letter-spacing: -1px;

            margin-top: 18px;

        }


        .price {

            color: #e63946;

            font-size: 32px;

            font-weight: 800;

        }


        .description {

            color: #687386;

            line-height: 1.8;

            font-size: 16px;

        }


        .stock-box {

            display: inline-flex;

            align-items: center;

            gap: 8px;

            padding: 9px 14px;

            border-radius: 10px;

            font-size: 14px;

            font-weight: 700;

        }


        .stock-available {

            background: #eaf8f0;

            color: #198754;

        }


        .stock-empty {

            background: #fff0f0;

            color: #dc3545;

        }


        /* =========================
           QUANTITY
        ========================= */

        .quantity-label {

            font-weight: 700;

            margin-bottom: 8px;

        }


        .quantity-input {

            width: 130px;

            height: 48px;

            border-radius: 10px;

            border: 1px solid #dce2ea;

            padding: 0 15px;

            font-weight: 600;

        }


        .quantity-input:focus {

            border-color: #0d6efd;

            box-shadow:
                0 0 0 3px
                rgba(13,110,253,0.1);

        }


        .cart-button {

            min-height: 52px;

            border-radius: 12px;

            font-weight: 800;

            font-size: 16px;

            padding: 0 28px;

        }


        /* =========================
           BENEFITS
        ========================= */

        .benefit-box {

            background: #f8faff;

            border: 1px solid #edf2fa;

            border-radius: 15px;

            padding: 18px;

            height: 100%;

        }


        .benefit-icon {

            width: 42px;

            height: 42px;

            border-radius: 12px;

            background: #eaf2ff;

            color: #0d6efd;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 19px;

            margin-bottom: 12px;

        }


        .benefit-box h6 {

            font-weight: 800;

        }


        .benefit-box p {

            color: #7b8494;

            font-size: 13px;

            margin-bottom: 0;

        }


        /* =========================
           FOOTER
        ========================= */

        .footer {

            background: #07111f;

            color: #b8c2d1;

            margin-top: 70px;

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

            .product-detail-card {

                padding: 20px;

            }


            .product-image-box {

                height: 350px;

            }


            .product-title {

                font-size: 32px;

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
            class="
                d-flex
                align-items-center
                justify-content-between
                flex-wrap
                gap-3
            "
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


            <div
                class="
                    d-flex
                    align-items-center
                    flex-wrap
                "
            >

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


                <?php if (
                    isset($_SESSION["user_id"])
                ): ?>


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
                            class="
                                btn
                                btn-warning
                                btn-sm
                                me-2
                            "
                        >

                            <i
                                class="bi bi-speedometer2"
                            ></i>

                            Quản trị

                        </a>

                    <?php endif; ?>


                    <a
                        href="dang-xuat.php"
                        class="
                            btn
                            btn-outline-danger
                            btn-sm
                        "
                    >

                        Đăng xuất

                    </a>


                <?php else: ?>


                    <a
                        href="dang-nhap.php"
                        class="
                            btn
                            btn-outline-light
                            btn-sm
                            me-2
                        "
                    >

                        Đăng nhập

                    </a>


                    <a
                        href="dang-ky.php"
                        class="
                            btn
                            btn-primary
                            btn-sm
                        "
                    >

                        Đăng ký

                    </a>


                <?php endif; ?>

            </div>

        </div>

    </div>

</nav>



<!-- =====================================================
     PAGE HEADER
===================================================== -->

<section class="page-header">

    <div class="container">

        <div class="breadcrumb-custom">

            <a href="index.php">
                Trang chủ
            </a>

            <span class="mx-2">
                /
            </span>

            <a href="san-pham.php">
                Sản phẩm
            </a>

            <span class="mx-2">
                /
            </span>

            Chi tiết

        </div>

    </div>

</section>



<!-- =====================================================
     PRODUCT
===================================================== -->

<div class="container py-5">

    <div class="product-detail-card">

        <div class="row g-5 align-items-center">


            <!-- IMAGE -->

            <div class="col-lg-6">

                <div class="product-image-box">


                    <span class="image-badge">

                        <i class="bi bi-stars"></i>

                        SẢN PHẨM

                    </span>


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
                                text-center
                                text-muted
                            "
                        >

                            <i
                                class="bi bi-image"
                                style="
                                    font-size:70px;
                                "
                            ></i>

                            <div class="mt-2">

                                Không có hình ảnh

                            </div>

                        </div>

                    <?php endif; ?>


                </div>

            </div>



            <!-- INFORMATION -->

            <div class="col-lg-6">


                <span class="category-badge">

                    <i class="bi bi-tag"></i>

                    <?= htmlspecialchars(
                        $product["category_name"]
                        ?? "Sản phẩm"
                    ) ?>

                </span>


                <h1 class="product-title">

                    <?= htmlspecialchars(
                        $product["name"]
                    ) ?>

                </h1>


                <div class="my-4">

                    <div class="price">

                        <?= number_format(
                            $product["price"],
                            0,
                            ",",
                            "."
                        ) ?>

                        ₫

                    </div>

                    <small class="text-muted">

                        Giá bán đã bao gồm thông tin
                        sản phẩm trên website.

                    </small>

                </div>


                <hr>


                <!-- DESCRIPTION -->

                <div class="my-4">

                    <h5 class="fw-bold mb-3">

                        Mô tả sản phẩm

                    </h5>


                    <div class="description">

                        <?= nl2br(
                            htmlspecialchars(
                                $product["description"]
                            )
                        ) ?>

                    </div>

                </div>


                <!-- STOCK -->

                <div class="mb-4">


                    <?php if (
                        $product["stock"] > 0
                    ): ?>

                        <span
                            class="
                                stock-box
                                stock-available
                            "
                        >

                            <i
                                class="
                                    bi
                                    bi-check-circle-fill
                                "
                            ></i>

                            Còn hàng

                        </span>


                        <span
                            class="text-muted ms-2"
                        >

                            Còn
                            <strong>
                                <?= $product["stock"] ?>
                            </strong>
                            sản phẩm

                        </span>


                    <?php else: ?>


                        <span
                            class="
                                stock-box
                                stock-empty
                            "
                        >

                            <i
                                class="
                                    bi
                                    bi-x-circle-fill
                                "
                            ></i>

                            Hết hàng

                        </span>


                    <?php endif; ?>


                </div>



                <!-- CART -->

                <?php if (
                    $product["stock"] > 0
                ): ?>


                    <form
                        method="POST"
                        class="mb-4"
                    >


                        <div class="quantity-label">

                            Số lượng

                        </div>


                        <div
                            class="
                                d-flex
                                gap-3
                                flex-wrap
                            "
                        >


                            <input
                                type="number"
                                name="quantity"
                                value="1"
                                min="1"
                                max="<?= $product["stock"] ?>"
                                class="quantity-input"
                            >


                            <button
                                type="submit"
                                class="
                                    btn
                                    btn-primary
                                    cart-button
                                "
                            >

                                <i
                                    class="
                                        bi
                                        bi-cart-plus
                                    "
                                ></i>

                                Thêm vào giỏ hàng

                            </button>


                        </div>

                    </form>


                <?php else: ?>


                    <button
                        class="
                            btn
                            btn-secondary
                            btn-lg
                            w-100
                        "
                        disabled
                    >

                        Sản phẩm đã hết hàng

                    </button>


                <?php endif; ?>


                <a
                    href="san-pham.php"
                    class="
                        btn
                        btn-outline-secondary
                    "
                >

                    <i
                        class="bi bi-arrow-left"
                    ></i>

                    Tiếp tục mua sắm

                </a>


            </div>

        </div>


        <!-- =================================================
             BENEFITS
        ================================================= -->

        <div class="row g-3 mt-5 pt-4 border-top">


            <div class="col-md-4">

                <div class="benefit-box">

                    <div class="benefit-icon">

                        <i
                            class="bi bi-truck"
                        ></i>

                    </div>

                    <h6>

                        Giao hàng nhanh

                    </h6>

                    <p>

                        Hỗ trợ giao hàng
                        tận nơi nhanh chóng.

                    </p>

                </div>

            </div>


            <div class="col-md-4">

                <div class="benefit-box">

                    <div class="benefit-icon">

                        <i
                            class="
                                bi
                                bi-shield-check
                            "
                        ></i>

                    </div>

                    <h6>

                        Sản phẩm chất lượng

                    </h6>

                    <p>

                        Thông tin sản phẩm
                        rõ ràng và minh bạch.

                    </p>

                </div>

            </div>


            <div class="col-md-4">

                <div class="benefit-box">

                    <div class="benefit-icon">

                        <i
                            class="bi bi-headset"
                        ></i>

                    </div>

                    <h6>

                        Hỗ trợ khách hàng

                    </h6>

                    <p>

                        Hỗ trợ tư vấn
                        trong quá trình mua hàng.

                    </p>

                </div>

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

                <div
                    class="footer-title fs-4"
                >

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


                <?php if (
                    isset($_SESSION["user_id"])
                ): ?>

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

                    <i
                        class="bi bi-telephone"
                    ></i>

                    0900 000 000

                </p>


                <p>

                    <i
                        class="bi bi-envelope"
                    ></i>

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