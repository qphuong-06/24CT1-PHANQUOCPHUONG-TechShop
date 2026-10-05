<?php

session_start();

require_once "config/database.php";


// =====================================================
// XÓA MỘT SẢN PHẨM
// =====================================================

if (isset($_GET["xoa"])) {

    $id = (int) $_GET["xoa"];

    if (isset($_SESSION["cart"][$id])) {

        unset($_SESSION["cart"][$id]);

    }

    header("Location: gio-hang.php");

    exit;
}


// =====================================================
// XÓA TOÀN BỘ GIỎ HÀNG
// =====================================================

if (isset($_GET["xoa_tat_ca"])) {

    unset($_SESSION["cart"]);

    header("Location: gio-hang.php");

    exit;
}


// =====================================================
// CẬP NHẬT SỐ LƯỢNG
// =====================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["quantity"])) {

        foreach (
            $_POST["quantity"]
            as $id => $quantity
        ) {

            $id = (int) $id;

            $quantity = (int) $quantity;


            if ($quantity <= 0) {

                unset($_SESSION["cart"][$id]);

                continue;
            }


            $stmt = $conn->prepare(
                "SELECT stock FROM products WHERE id = ?"
            );


            $stmt->bind_param(
                "i",
                $id
            );


            $stmt->execute();


            $stock = $stmt
                ->get_result()
                ->fetch_assoc();


            $stmt->close();


            if ($stock) {

                $_SESSION["cart"][$id] =
                    min(
                        $quantity,
                        $stock["stock"]
                    );

            }

        }

    }


    header("Location: gio-hang.php");

    exit;
}


// =====================================================
// LẤY GIỎ HÀNG
// =====================================================

$cart = $_SESSION["cart"] ?? [];

$total = 0;

$products = [];


foreach (
    $cart
    as $id => $quantity
) {

    $stmt = $conn->prepare(
        "SELECT * FROM products WHERE id = ?"
    );


    $stmt->bind_param(
        "i",
        $id
    );


    $stmt->execute();


    $product = $stmt
        ->get_result()
        ->fetch_assoc();


    $stmt->close();


    if ($product) {

        $product["quantity"] =
            $quantity;


        $product["subtotal"] =
            $product["price"]
            * $quantity;


        $total +=
            $product["subtotal"];


        $products[] =
            $product;

    }

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
        Giỏ hàng | TechShop
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


        /* =================================================
           NAVBAR
        ================================================= */

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


        /* =================================================
           PAGE HEADER
        ================================================= */

        .page-header {

            padding: 50px 20px;

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


        .page-title {

            font-size:
                clamp(30px, 4vw, 42px);

            font-weight: 800;

            margin-top: 12px;

        }


        .page-subtitle {

            color: #aebbd0;

            margin: 0;

        }


        /* =================================================
           CART AREA
        ================================================= */

        .cart-card {

            background: white;

            border-radius: 20px;

            border: 1px solid #edf0f5;

            box-shadow:
                0 12px 35px
                rgba(23,32,51,0.07);

            overflow: hidden;

        }


        .cart-header {

            padding: 22px 25px;

            border-bottom:
                1px solid #edf0f5;

            display: flex;

            justify-content: space-between;

            align-items: center;

            flex-wrap: wrap;

            gap: 10px;

        }


        .cart-header h4 {

            margin: 0;

            font-weight: 800;

        }


        .product-row {

            padding: 22px 25px;

            border-bottom:
                1px solid #edf0f5;

        }


        .product-row:last-child {

            border-bottom: none;

        }


        /* =================================================
           PRODUCT IMAGE
        ================================================= */

        .cart-image {

            width: 95px;

            height: 95px;

            object-fit: cover;

            border-radius: 15px;

            background: #f5f7fa;

            border: 1px solid #edf0f5;

            transition: 0.3s;

        }


        .cart-image:hover {

            transform: scale(1.04);

        }


        .product-name {

            font-size: 17px;

            font-weight: 800;

            color: #172033;

            text-decoration: none;

        }


        .product-name:hover {

            color: #0d6efd;

        }


        .product-price {

            color: #e63946;

            font-weight: 800;

            font-size: 17px;

        }


        .product-stock {

            color: #198754;

            font-size: 13px;

            font-weight: 600;

        }


        /* =================================================
           QUANTITY
        ================================================= */

        .quantity-input {

            width: 85px;

            height: 44px;

            border: 1px solid #dce2ea;

            border-radius: 10px;

            text-align: center;

            font-weight: 700;

        }


        .quantity-input:focus {

            border-color: #0d6efd;

            box-shadow:
                0 0 0 3px
                rgba(13,110,253,0.1);

            outline: none;

        }


        /* =================================================
           SUBTOTAL
        ================================================= */

        .subtotal {

            font-size: 17px;

            font-weight: 800;

        }


        .remove-button {

            width: 38px;

            height: 38px;

            border-radius: 10px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            text-decoration: none;

            color: #dc3545;

            background: #fff0f0;

            transition: 0.2s;

        }


        .remove-button:hover {

            color: white;

            background: #dc3545;

        }


        /* =================================================
           SUMMARY
        ================================================= */

        .summary-card {

            background: white;

            border-radius: 20px;

            padding: 25px;

            border: 1px solid #edf0f5;

            box-shadow:
                0 12px 35px
                rgba(23,32,51,0.07);

            position: sticky;

            top: 100px;

        }


        .summary-title {

            font-size: 21px;

            font-weight: 800;

            margin-bottom: 25px;

        }


        .summary-row {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 16px;

            color: #687386;

        }


        .summary-total {

            display: flex;

            justify-content: space-between;

            align-items: center;

            padding-top: 18px;

            margin-top: 18px;

            border-top:
                1px solid #edf0f5;

        }


        .total-price {

            color: #e63946;

            font-size: 27px;

            font-weight: 900;

        }


        .checkout-button {

            width: 100%;

            min-height: 55px;

            border-radius: 12px;

            font-weight: 800;

            font-size: 16px;

            margin-top: 20px;

        }


        /* =================================================
           EMPTY CART
        ================================================= */

        .empty-cart {

            background: white;

            border-radius: 24px;

            padding: 70px 30px;

            text-align: center;

            box-shadow:
                0 12px 35px
                rgba(23,32,51,0.07);

        }


        .empty-icon {

            width: 90px;

            height: 90px;

            margin: 0 auto 25px;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #eef5ff;

            color: #0d6efd;

            font-size: 40px;

        }


        .empty-cart h3 {

            font-weight: 800;

        }


        .empty-cart p {

            color: #7b8494;

        }


        /* =================================================
           BENEFITS
        ================================================= */

        .benefit-box {

            background: white;

            border-radius: 15px;

            padding: 18px;

            border: 1px solid #edf0f5;

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


        /* =================================================
           FOOTER
        ================================================= */

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


        /* =================================================
           MOBILE
        ================================================= */

        @media (max-width: 768px) {

            .cart-header {

                align-items: flex-start;

            }


            .product-row {

                padding: 20px 15px;

            }


            .cart-image {

                width: 75px;

                height: 75px;

            }


            .summary-card {

                position: static;

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
                        class="
                            text-white
                            mx-2
                        "
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
                                class="
                                    bi
                                    bi-speedometer2
                                "
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
     HEADER
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

            Giỏ hàng

        </div>


        <h1 class="page-title">

            <i class="bi bi-cart3"></i>

            Giỏ hàng của bạn

        </h1>


        <p class="page-subtitle">

            Kiểm tra sản phẩm trước khi tiến hành đặt hàng.

        </p>


    </div>

</section>



<!-- =====================================================
     MAIN
===================================================== -->

<div class="container py-5">


<?php if (empty($products)): ?>


    <!-- =================================================
         EMPTY CART
    ================================================= -->

    <div class="empty-cart">


        <div class="empty-icon">

            <i class="bi bi-cart-x"></i>

        </div>


        <h3>

            Giỏ hàng đang trống

        </h3>


        <p class="mt-2 mb-4">

            Bạn chưa thêm sản phẩm nào vào giỏ hàng.

        </p>


        <a
            href="san-pham.php"
            class="
                btn
                btn-primary
                btn-lg
                px-4
            "
        >

            <i class="bi bi-bag"></i>

            Khám phá sản phẩm

        </a>


    </div>


<?php else: ?>


    <form method="POST">


        <div class="row g-4">


            <!-- =========================================
                 PRODUCT LIST
            ========================================== -->

            <div class="col-lg-8">


                <div class="cart-card">


                    <div class="cart-header">


                        <h4>

                            Sản phẩm trong giỏ

                        </h4>


                        <span class="text-muted">

                            <?= count($products) ?>

                            sản phẩm

                        </span>


                    </div>



                    <?php foreach (
                        $products
                        as $product
                    ): ?>


                        <div class="product-row">


                            <div
                                class="
                                    row
                                    align-items-center
                                    g-3
                                "
                            >


                                <!-- IMAGE -->

                                <div
                                    class="
                                        col-3
                                        col-md-2
                                    "
                                >


                                    <?php if (
                                        !empty(
                                            $product["image"]
                                        )
                                    ): ?>


                                        <img
                                            src="images/<?= htmlspecialchars(
                                                $product["image"]
                                            ) ?>"
                                            class="cart-image"
                                            alt="<?= htmlspecialchars(
                                                $product["name"]
                                            ) ?>"
                                        >


                                    <?php else: ?>


                                        <div
                                            class="
                                                cart-image
                                                d-flex
                                                align-items-center
                                                justify-content-center
                                            "
                                        >

                                            <i
                                                class="
                                                    bi
                                                    bi-image
                                                    text-muted
                                                    fs-3
                                                "
                                            ></i>

                                        </div>


                                    <?php endif; ?>


                                </div>



                                <!-- PRODUCT INFO -->

                                <div
                                    class="
                                        col-9
                                        col-md-4
                                    "
                                >


                                    <a
                                        href="chi-tiet.php?id=<?= $product["id"] ?>"
                                        class="product-name"
                                    >

                                        <?= htmlspecialchars(
                                            $product["name"]
                                        ) ?>

                                    </a>


                                    <div
                                        class="
                                            product-price
                                            mt-1
                                        "
                                    >

                                        <?= number_format(
                                            $product["price"],
                                            0,
                                            ",",
                                            "."
                                        ) ?>

                                        ₫

                                    </div>


                                    <div
                                        class="
                                            product-stock
                                            mt-1
                                        "
                                    >

                                        <i
                                            class="
                                                bi
                                                bi-check-circle
                                            "
                                        ></i>

                                        Còn
                                        <?= $product["stock"] ?>
                                        sản phẩm

                                    </div>


                                </div>



                                <!-- QUANTITY -->

                                <div
                                    class="
                                        col-5
                                        col-md-2
                                    "
                                >


                                    <label
                                        class="
                                            text-muted
                                            small
                                            mb-1
                                        "
                                    >

                                        Số lượng

                                    </label>


                                    <input
                                        type="number"
                                        name="quantity[<?= $product["id"] ?>]"
                                        value="<?= $product["quantity"] ?>"
                                        min="1"
                                        max="<?= $product["stock"] ?>"
                                        class="quantity-input"
                                    >


                                </div>



                                <!-- SUBTOTAL -->

                                <div
                                    class="
                                        col-5
                                        col-md-3
                                        text-md-end
                                    "
                                >


                                    <div
                                        class="
                                            text-muted
                                            small
                                            mb-1
                                        "
                                    >

                                        Thành tiền

                                    </div>


                                    <div class="subtotal">

                                        <?= number_format(
                                            $product["subtotal"],
                                            0,
                                            ",",
                                            "."
                                        ) ?>

                                        ₫

                                    </div>


                                </div>



                                <!-- DELETE -->

                                <div
                                    class="
                                        col-2
                                        col-md-1
                                        text-end
                                    "
                                >


                                    <a
                                        href="gio-hang.php?xoa=<?= $product["id"] ?>"
                                        class="remove-button"
                                        title="Xóa sản phẩm"
                                    >

                                        <i
                                            class="
                                                bi
                                                bi-trash3
                                            "
                                        ></i>

                                    </a>


                                </div>


                            </div>

                        </div>


                    <?php endforeach; ?>


                </div>



                <!-- CART ACTIONS -->

                <div
                    class="
                        d-flex
                        justify-content-between
                        align-items-center
                        flex-wrap
                        gap-3
                        mt-4
                    "
                >


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

                        Tiếp tục mua hàng

                    </a>


                    <div
                        class="
                            d-flex
                            gap-2
                            flex-wrap
                        "
                    >


                        <button
                            type="submit"
                            class="
                                btn
                                btn-secondary
                            "
                        >

                            <i
                                class="bi bi-arrow-repeat"
                            ></i>

                            Cập nhật

                        </button>


                        <a
                            href="gio-hang.php?xoa_tat_ca=1"
                            class="
                                btn
                                btn-outline-danger
                            "
                            onclick="
                                return confirm(
                                    'Bạn có chắc muốn xóa toàn bộ giỏ hàng?'
                                );
                            "
                        >

                            <i
                                class="bi bi-trash3"
                            ></i>

                            Xóa tất cả

                        </a>


                    </div>


                </div>


            </div>



            <!-- =========================================
                 SUMMARY
            ========================================== -->

            <div class="col-lg-4">


                <div class="summary-card">


                    <div class="summary-title">

                        <i
                            class="bi bi-receipt"
                        ></i>

                        Tóm tắt đơn hàng

                    </div>


                    <div class="summary-row">

                        <span>

                            Tạm tính

                        </span>


                        <strong>

                            <?= number_format(
                                $total,
                                0,
                                ",",
                                "."
                            ) ?>

                            ₫

                        </strong>

                    </div>


                    <div class="summary-row">

                        <span>

                            Phí vận chuyển

                        </span>


                        <span class="text-success">

                            Sẽ tính khi đặt hàng

                        </span>

                    </div>


                    <div class="summary-total">


                        <strong>

                            Tổng cộng

                        </strong>


                        <span class="total-price">

                            <?= number_format(
                                $total,
                                0,
                                ",",
                                "."
                            ) ?>

                            ₫

                        </span>


                    </div>


                    <a
                        href="dat-hang.php"
                        class="
                            btn
                            btn-primary
                            checkout-button
                        "
                    >

                        Tiến hành đặt hàng

                        <i
                            class="
                                bi
                                bi-arrow-right
                            "
                        ></i>

                    </a>


                    <div
                        class="
                            text-center
                            text-muted
                            small
                            mt-3
                        "
                    >

                        <i
                            class="
                                bi
                                bi-shield-check
                            "
                        ></i>

                        Thanh toán an toàn

                    </div>


                </div>


            </div>


        </div>


    </form>


    <!-- =================================================
         BENEFITS
    ================================================= -->

    <div class="row g-3 mt-5">


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

                    Hỗ trợ giao hàng tận nơi
                    nhanh chóng và thuận tiện.

                </p>


            </div>


        </div>



        <div class="col-md-4">


            <div class="benefit-box">


                <div class="benefit-icon">

                    <i
                        class="bi bi-shield-check"
                    ></i>

                </div>


                <h6>

                    Mua sắm an toàn

                </h6>


                <p>

                    Thông tin đơn hàng được
                    quản lý rõ ràng.

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

                    Sẵn sàng hỗ trợ khi bạn
                    cần tư vấn sản phẩm.

                </p>


            </div>


        </div>


    </div>


<?php endif; ?>


</div>



<!-- =====================================================
     FOOTER
===================================================== -->

<footer class="footer">

    <div class="container">


        <div class="row g-5">


            <div class="col-lg-5">


                <div
                    class="
                        footer-title
                        fs-4
                    "
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