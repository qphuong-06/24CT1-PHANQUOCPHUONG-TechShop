<?php

session_start();

require_once "config/database.php";


/*
|--------------------------------------------------------------------------
| KIỂM TRA GIỎ HÀNG
|--------------------------------------------------------------------------
*/

$cart = $_SESSION["cart"] ?? [];

if (empty($cart)) {

    header("Location: gio-hang.php");

    exit;
}


/*
|--------------------------------------------------------------------------
| BIẾN
|--------------------------------------------------------------------------
*/

$success = false;

$orderId = 0;

$total = 0;

$error = "";

$payment_method =
    $_POST["payment_method"] ?? "cod";


/*
|--------------------------------------------------------------------------
| KIỂM TRA PHƯƠNG THỨC THANH TOÁN
|--------------------------------------------------------------------------
*/

if (
    $payment_method !== "cod"
    && $payment_method !== "bank"
) {

    $payment_method = "cod";

}


/*
|--------------------------------------------------------------------------
| LẤY SẢN PHẨM TRONG GIỎ
|--------------------------------------------------------------------------
*/

$products = [];


foreach ($cart as $id => $quantity) {

    $stmt = $conn->prepare("
        SELECT *
        FROM products
        WHERE id = ?
    ");

    $stmt->bind_param(
        "i",
        $id
    );

    $stmt->execute();

    $product =
        $stmt
        ->get_result()
        ->fetch_assoc();

    $stmt->close();


    if ($product) {

        $product["quantity"] =
            (int) $quantity;

        $product["subtotal"] =
            $product["price"]
            * $quantity;

        $total +=
            $product["subtotal"];

        $products[] =
            $product;

    }

}


/*
|--------------------------------------------------------------------------
| XỬ LÝ ĐẶT HÀNG
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullname =
        trim($_POST["fullname"] ?? "");

    $phone =
        trim($_POST["phone"] ?? "");

    $address =
        trim($_POST["address"] ?? "");


    /*
    |--------------------------------------------------------------------------
    | KIỂM TRA THÔNG TIN
    |--------------------------------------------------------------------------
    */

    if (
        $fullname === ""
        || $phone === ""
        || $address === ""
    ) {

        $error =
            "Vui lòng nhập đầy đủ thông tin.";

    } else {

        $conn->begin_transaction();


        try {


            /*
            |--------------------------------------------------------------------------
            | TẠO TÀI KHOẢN KHÁCH TẠM THỜI
            |--------------------------------------------------------------------------
            */

            $username =
                "guest_" . time() . "_" . rand(100, 999);


            $password =
                password_hash(
                    "123456",
                    PASSWORD_DEFAULT
                );


            $email =
                $username
                . "@techshop.local";


            $role =
                "customer";


            $stmt = $conn->prepare("
                INSERT INTO users
                (
                    username,
                    password,
                    fullname,
                    email,
                    phone,
                    role
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ");


            $stmt->bind_param(
                "ssssss",
                $username,
                $password,
                $fullname,
                $email,
                $phone,
                $role
            );


            $stmt->execute();


            $userId =
                $conn->insert_id;


            $stmt->close();


            /*
            |--------------------------------------------------------------------------
            | CHUẨN BỊ THANH TOÁN
            |--------------------------------------------------------------------------
            */

            $payment_status =
                "unpaid";

            $payment_content =
                "";


            /*
            |--------------------------------------------------------------------------
            | TẠO ĐƠN HÀNG
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare("
                INSERT INTO orders
                (
                    user_id,
                    fullname,
                    phone,
                    address,
                    total_amount,
                    payment_method,
                    payment_status,
                    payment_content
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");


            $stmt->bind_param(
                "isssdsss",
                $userId,
                $fullname,
                $phone,
                $address,
                $total,
                $payment_method,
                $payment_status,
                $payment_content
            );


            $stmt->execute();


            $orderId =
                $conn->insert_id;


            $stmt->close();


            /*
            |--------------------------------------------------------------------------
            | TẠO NỘI DUNG CHUYỂN KHOẢN
            |--------------------------------------------------------------------------
            */

            $payment_content =
                "TECHSHOP DH" . $orderId;


            $stmt = $conn->prepare("
                UPDATE orders
                SET payment_content = ?
                WHERE id = ?
            ");


            $stmt->bind_param(
                "si",
                $payment_content,
                $orderId
            );


            $stmt->execute();

            $stmt->close();


            /*
            |--------------------------------------------------------------------------
            | CHI TIẾT ĐƠN HÀNG
            |--------------------------------------------------------------------------
            */

            foreach ($products as $product) {

                $productId =
                    (int) $product["id"];

                $quantity =
                    (int) $product["quantity"];

                $price =
                    $product["price"];


                /*
                |--------------------------------------------------------------------------
                | KIỂM TRA TỒN KHO
                |--------------------------------------------------------------------------
                */

                $stmt = $conn->prepare("
                    SELECT stock
                    FROM products
                    WHERE id = ?
                    FOR UPDATE
                ");


                $stmt->bind_param(
                    "i",
                    $productId
                );


                $stmt->execute();


                $stockData =
                    $stmt
                    ->get_result()
                    ->fetch_assoc();


                $stmt->close();


                if (
                    !$stockData
                    || $stockData["stock"] < $quantity
                ) {

                    throw new Exception(
                        "Sản phẩm "
                        . $product["name"]
                        . " không đủ tồn kho."
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | THÊM ORDER ITEM
                |--------------------------------------------------------------------------
                */

                $stmt = $conn->prepare("
                    INSERT INTO order_items
                    (
                        order_id,
                        product_id,
                        quantity,
                        price
                    )
                    VALUES (?, ?, ?, ?)
                ");


                $stmt->bind_param(
                    "iiid",
                    $orderId,
                    $productId,
                    $quantity,
                    $price
                );


                $stmt->execute();


                $stmt->close();


                /*
                |--------------------------------------------------------------------------
                | TRỪ TỒN KHO
                |--------------------------------------------------------------------------
                */

                $stmt = $conn->prepare("
                    UPDATE products
                    SET stock = stock - ?
                    WHERE id = ?
                ");


                $stmt->bind_param(
                    "ii",
                    $quantity,
                    $productId
                );


                $stmt->execute();


                $stmt->close();

            }


            /*
            |--------------------------------------------------------------------------
            | HOÀN TẤT TRANSACTION
            |--------------------------------------------------------------------------
            */

            $conn->commit();


            /*
            |--------------------------------------------------------------------------
            | XÓA GIỎ HÀNG
            |--------------------------------------------------------------------------
            */

            unset($_SESSION["cart"]);


            /*
            |--------------------------------------------------------------------------
            | NẾU CHUYỂN KHOẢN
            |--------------------------------------------------------------------------
            */

            if ($payment_method === "bank") {

                header(
                    "Location: thanh-toan.php?id="
                    . $orderId
                );

                exit;

            }


            /*
            |--------------------------------------------------------------------------
            | NẾU COD
            |--------------------------------------------------------------------------
            */

            $success =
                true;


        } catch (Exception $e) {

            $conn->rollback();


            $error =
                $e->getMessage();

        }

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
        Đặt hàng | TechShop
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
           CHECKOUT CARD
        ================================================= */

        .checkout-card {

            background: white;

            border-radius: 20px;

            border: 1px solid #edf0f5;

            box-shadow:
                0 12px 35px
                rgba(23,32,51,0.07);

            padding: 30px;

        }


        .section-title {

            font-size: 21px;

            font-weight: 800;

            margin-bottom: 25px;

        }


        /* =================================================
           FORM
        ================================================= */

        .form-label {

            font-weight: 700;

            margin-bottom: 8px;

        }


        .form-control {

            min-height: 50px;

            border-radius: 11px;

            border: 1px solid #dce2ea;

        }


        .form-control:focus {

            border-color: #0d6efd;

            box-shadow:
                0 0 0 3px
                rgba(13,110,253,0.1);

        }


        textarea.form-control {

            min-height: 120px;

            resize: vertical;

        }


        .input-icon {

            position: relative;

        }


        .input-icon i {

            position: absolute;

            left: 16px;

            top: 17px;

            color: #8b95a5;

        }


        .input-icon input {

            padding-left: 45px;

        }


        /* =================================================
           PAYMENT
        ================================================= */

        .payment-options {

            display: flex;

            flex-direction: column;

            gap: 12px;

        }


        .payment-option {

            display: flex;

            align-items: center;

            gap: 15px;

            padding: 16px;

            border: 2px solid #e1e6ee;

            border-radius: 14px;

            background: #fff;

            cursor: pointer;

            transition: 0.2s;

        }


        .payment-option:hover {

            border-color: #0d6efd;

            background: #f8fbff;

        }


        .payment-option:has(input:checked) {

            border-color: #0d6efd;

            background: #f1f7ff;

        }


        .payment-option input {

            width: 18px;

            height: 18px;

            flex-shrink: 0;

        }


        .payment-icon {

            width: 44px;

            height: 44px;

            border-radius: 12px;

            display: flex;

            align-items: center;

            justify-content: center;

            color: white;

            font-size: 20px;

            flex-shrink: 0;

        }


        .cod-icon {

            background: #198754;

        }


        .bank-icon {

            background:
                linear-gradient(
                    135deg,
                    #007bff,
                    #6f42c1
                );

        }


        .payment-content {

            flex: 1;

        }


        .payment-content strong {

            display: block;

            margin-bottom: 3px;

        }


        /* =================================================
           ORDER SUMMARY
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

            margin-bottom: 22px;

        }


        .summary-product {

            display: flex;

            align-items: center;

            gap: 12px;

            padding: 12px 0;

            border-bottom:
                1px solid #edf0f5;

        }


        .summary-image {

            width: 60px;

            height: 60px;

            object-fit: cover;

            border-radius: 10px;

            background: #f5f7fa;

        }


        .summary-product-name {

            font-size: 14px;

            font-weight: 700;

            line-height: 1.4;

        }


        .summary-product-price {

            color: #e63946;

            font-weight: 700;

            font-size: 13px;

        }


        .summary-row {

            display: flex;

            justify-content: space-between;

            margin-top: 15px;

            color: #687386;

        }


        .summary-total {

            border-top:
                1px solid #edf0f5;

            margin-top: 18px;

            padding-top: 18px;

            display: flex;

            justify-content: space-between;

            align-items: center;

        }


        .total-price {

            color: #e63946;

            font-size: 26px;

            font-weight: 900;

        }


        /* =================================================
           SECURITY
        ================================================= */

        .secure-box {

            background: #f5f9ff;

            border: 1px solid #e2edff;

            border-radius: 13px;

            padding: 14px;

            margin-top: 20px;

            color: #5d6b80;

            font-size: 13px;

        }


        .secure-box i {

            color: #198754;

            font-size: 18px;

        }


        /* =================================================
           SUCCESS
        ================================================= */

        .success-card {

            background: white;

            border-radius: 25px;

            padding: 65px 30px;

            text-align: center;

            box-shadow:
                0 15px 45px
                rgba(23,32,51,0.08);

        }


        .success-icon {

            width: 100px;

            height: 100px;

            border-radius: 50%;

            margin: 0 auto 25px;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #eaf8f0;

            color: #198754;

            font-size: 50px;

        }


        .success-title {

            font-size: 34px;

            font-weight: 900;

        }


        .order-number {

            display: inline-block;

            background: #eef5ff;

            color: #0d6efd;

            padding: 10px 18px;

            border-radius: 50px;

            font-weight: 800;

            margin: 15px 0;

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

            .checkout-card {

                padding: 20px;

            }


            .summary-card {

                position: static;

            }


            .success-card {

                padding: 45px 20px;

            }


            .success-title {

                font-size: 28px;

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



<?php if ($success): ?>


<!-- =====================================================
     SUCCESS
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

            Đặt hàng thành công

        </div>

    </div>

</section>


<div class="container py-5">

    <div class="success-card">

        <div class="success-icon">

            <i
                class="bi bi-check-lg"
            ></i>

        </div>


        <div class="success-title">

            Đặt hàng thành công!

        </div>


        <p
            class="
                text-muted
                fs-5
                mt-3
            "
        >

            Cảm ơn bạn đã mua hàng tại
            <strong>TechShop</strong>.

        </p>


        <div class="order-number">

            <i class="bi bi-receipt"></i>

            Mã đơn hàng:

            #<?= $orderId ?>

        </div>


        <div class="mt-3">

            Tổng tiền:

            <strong
                class="
                    text-danger
                    fs-4
                "
            >

                <?= number_format(
                    $total,
                    0,
                    ",",
                    "."
                ) ?>

                ₫

            </strong>

        </div>


        <p
            class="
                text-muted
                mt-3
            "
        >

            Đơn hàng của bạn đã được ghi nhận
            và đang chờ xử lý.

        </p>


        <div
            class="
                d-flex
                justify-content-center
                gap-2
                flex-wrap
                mt-4
            "
        >

            <a
                href="index.php"
                class="
                    btn
                    btn-primary
                    btn-lg
                "
            >

                <i class="bi bi-house"></i>

                Về trang chủ

            </a>


            <a
                href="san-pham.php"
                class="
                    btn
                    btn-outline-primary
                    btn-lg
                "
            >

                <i class="bi bi-bag"></i>

                Tiếp tục mua hàng

            </a>

        </div>

    </div>

</div>


<?php else: ?>


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

            <a href="gio-hang.php">
                Giỏ hàng
            </a>

            <span class="mx-2">
                /
            </span>

            Đặt hàng

        </div>


        <h1 class="page-title">

            <i class="bi bi-credit-card"></i>

            Thông tin đặt hàng

        </h1>


        <p class="page-subtitle">

            Điền thông tin nhận hàng để hoàn tất đơn hàng.

        </p>

    </div>

</section>



<!-- =====================================================
     CHECKOUT
===================================================== -->

<div class="container py-5">

    <?php if ($error !== ""): ?>

        <div
            class="
                alert
                alert-danger
                border-0
                shadow-sm
                mb-4
            "
        >

            <i
                class="bi bi-exclamation-triangle-fill"
            ></i>

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <div class="row g-4">


        <!-- CUSTOMER INFORMATION -->

        <div class="col-lg-7">

            <div class="checkout-card">

                <div class="section-title">

                    <i
                        class="bi bi-person-lines-fill"
                    ></i>

                    Thông tin nhận hàng

                </div>


                <form method="POST">


                    <!-- HỌ TÊN -->

                    <div class="mb-4">

                        <label
                            class="form-label"
                        >

                            Họ và tên

                        </label>


                        <div class="input-icon">

                            <i
                                class="
                                    bi
                                    bi-person
                                "
                            ></i>


                            <input
                                type="text"
                                name="fullname"
                                class="form-control"
                                placeholder="Nhập họ và tên"
                                value="<?= htmlspecialchars(
                                    $_POST["fullname"]
                                    ?? ""
                                ) ?>"
                                required
                            >

                        </div>

                    </div>



                    <!-- PHONE -->

                    <div class="mb-4">

                        <label
                            class="form-label"
                        >

                            Số điện thoại

                        </label>


                        <div class="input-icon">

                            <i
                                class="
                                    bi
                                    bi-telephone
                                "
                            ></i>


                            <input
                                type="text"
                                name="phone"
                                class="form-control"
                                placeholder="Nhập số điện thoại"
                                value="<?= htmlspecialchars(
                                    $_POST["phone"]
                                    ?? ""
                                ) ?>"
                                required
                            >

                        </div>

                    </div>



                    <!-- ADDRESS -->

                    <div class="mb-4">

                        <label
                            class="form-label"
                        >

                            Địa chỉ nhận hàng

                        </label>


                        <textarea
                            name="address"
                            class="form-control"
                            placeholder="Nhập địa chỉ nhận hàng đầy đủ..."
                            required
                        ><?= htmlspecialchars(
                            $_POST["address"]
                            ?? ""
                        ) ?></textarea>

                    </div>



                    <!-- PAYMENT -->

                    <div class="mb-4">

                        <label
                            class="form-label"
                        >

                            Phương thức thanh toán

                        </label>


                        <div class="payment-options">


                            <!-- COD -->

                            <label
                                class="payment-option"
                            >

                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="cod"
                                    <?= $payment_method === "cod"
                                        ? "checked"
                                        : "" ?>
                                >


                                <div
                                    class="
                                        payment-icon
                                        cod-icon
                                    "
                                >

                                    <i
                                        class="
                                            bi
                                            bi-cash-stack
                                        "
                                    ></i>

                                </div>


                                <div class="payment-content">

                                    <strong>

                                        Thanh toán khi nhận hàng

                                    </strong>


                                    <div
                                        class="
                                            text-muted
                                            small
                                        "
                                    >

                                        Thanh toán tiền mặt
                                        khi nhận được hàng.

                                    </div>

                                </div>

                            </label>



                            <!-- BANK -->

                            <label
                                class="payment-option"
                            >

                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="bank"
                                    <?= $payment_method === "bank"
                                        ? "checked"
                                        : "" ?>
                                >


                                <div
                                    class="
                                        payment-icon
                                        bank-icon
                                    "
                                >

                                    <i
                                        class="
                                            bi
                                            bi-qr-code
                                        "
                                    ></i>

                                </div>


                                <div class="payment-content">

                                    <strong>

                                        Chuyển khoản ngân hàng

                                    </strong>


                                    <div
                                        class="
                                            text-muted
                                            small
                                        "
                                    >

                                        Sau khi đặt hàng,
                                        hệ thống sẽ hiển thị
                                        mã QR Vietcombank.

                                    </div>

                                </div>

                            </label>


                        </div>

                    </div>



                    <!-- BUTTONS -->

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
                                btn-success
                                btn-lg
                                px-4
                            "
                        >

                            <i
                                class="
                                    bi
                                    bi-check-circle
                                "
                            ></i>

                            Xác nhận đặt hàng

                        </button>


                        <a
                            href="gio-hang.php"
                            class="
                                btn
                                btn-outline-secondary
                                btn-lg
                            "
                        >

                            <i
                                class="
                                    bi
                                    bi-arrow-left
                                "
                            ></i>

                            Quay lại giỏ hàng

                        </a>

                    </div>


                </form>

            </div>

        </div>



        <!-- ORDER SUMMARY -->

        <div class="col-lg-5">

            <div class="summary-card">

                <div class="summary-title">

                    <i class="bi bi-bag-check"></i>

                    Đơn hàng của bạn

                </div>



                <?php foreach (
                    $products
                    as $product
                ): ?>


                    <div class="summary-product">


                        <?php if (
                            !empty(
                                $product["image"]
                            )
                        ): ?>


                            <img
                                src="images/<?= htmlspecialchars(
                                    $product["image"]
                                ) ?>"
                                class="summary-image"
                                alt="<?= htmlspecialchars(
                                    $product["name"]
                                ) ?>"
                            >


                        <?php else: ?>


                            <div
                                class="
                                    summary-image
                                    d-flex
                                    align-items-center
                                    justify-content-center
                                    text-muted
                                "
                            >

                                <i
                                    class="
                                        bi
                                        bi-image
                                    "
                                ></i>

                            </div>


                        <?php endif; ?>


                        <div class="flex-grow-1">

                            <div
                                class="
                                    summary-product-name
                                "
                            >

                                <?= htmlspecialchars(
                                    $product["name"]
                                ) ?>

                            </div>


                            <div
                                class="
                                    text-muted
                                    small
                                "
                            >

                                Số lượng:

                                <?= $product["quantity"] ?>

                            </div>


                            <div
                                class="
                                    summary-product-price
                                "
                            >

                                <?= number_format(
                                    $product["subtotal"],
                                    0,
                                    ",",
                                    "."
                                ) ?>

                                ₫

                            </div>

                        </div>

                    </div>


                <?php endforeach; ?>



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

                        Sẽ tính khi giao hàng

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



                <div class="secure-box">

                    <div
                        class="
                            d-flex
                            gap-2
                            align-items-start
                        "
                    >

                        <i
                            class="
                                bi
                                bi-shield-check
                            "
                        ></i>


                        <div>

                            <strong>
                                Đặt hàng an toàn
                            </strong>

                            <br>

                            Thông tin của bạn được
                            sử dụng để xử lý đơn hàng.

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<?php endif; ?>



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