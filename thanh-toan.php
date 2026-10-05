<?php

session_start();

require_once "config/database.php";

/*
|--------------------------------------------------------------------------
| KIỂM TRA MÃ ĐƠN HÀNG
|--------------------------------------------------------------------------
*/

$order_id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;

if ($order_id <= 0) {
    die("Mã đơn hàng không hợp lệ.");
}

/*
|--------------------------------------------------------------------------
| LẤY THÔNG TIN ĐƠN HÀNG
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT *
     FROM orders
     WHERE id = ?"
);

if (!$stmt) {
    die("Không thể truy vấn đơn hàng.");
}

$stmt->bind_param("i", $order_id);

$stmt->execute();

$order = $stmt
    ->get_result()
    ->fetch_assoc();

$stmt->close();

if (!$order) {
    die("Không tìm thấy đơn hàng.");
}

/*
|--------------------------------------------------------------------------
| THÔNG TIN NGÂN HÀNG
|--------------------------------------------------------------------------
*/

$bank_id = "970436";

$account_number = "1046682956";

$account_name = "PHAN QUOC PHUONG";

/*
|--------------------------------------------------------------------------
| SỐ TIỀN
|--------------------------------------------------------------------------
*/

$amount = (int) $order["total_amount"];

/*
|--------------------------------------------------------------------------
| NỘI DUNG CHUYỂN KHOẢN
|--------------------------------------------------------------------------
*/

$transfer_content = "TECHSHOP DH" . $order_id;

/*
|--------------------------------------------------------------------------
| TẠO URL QR VIETQR
|--------------------------------------------------------------------------
*/

$qr_url =
    "https://img.vietqr.io/image/"
    . $bank_id
    . "-"
    . $account_number
    . "-compact2.png"
    . "?amount="
    . $amount
    . "&addInfo="
    . urlencode($transfer_content)
    . "&accountName="
    . urlencode($account_name);

?>

<!DOCTYPE html>

<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Thanh toán đơn hàng #<?= $order_id ?> - TechShop
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

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            color: #e5e7eb;

            background:
                radial-gradient(
                    circle at top left,
                    rgba(37, 99, 235, 0.18),
                    transparent 35%
                ),
                radial-gradient(
                    circle at bottom right,
                    rgba(124, 58, 237, 0.15),
                    transparent 35%
                ),
                #0b1120;
        }

        .navbar {
            background: rgba(15, 23, 42, 0.92);
            border-bottom: 1px solid #1e293b;
            backdrop-filter: blur(12px);
        }

        .brand {
            color: white;
            text-decoration: none;
            font-size: 23px;
            font-weight: 800;
        }

        .brand i {
            color: #60a5fa;
            margin-right: 8px;
        }

        .page {
            max-width: 1050px;
            margin: auto;
            padding: 50px 20px;
        }

        .title {
            text-align: center;
            margin-bottom: 35px;
        }

        .title h1 {
            color: white;
            font-size: 32px;
            font-weight: 800;
        }

        .title p {
            color: #94a3b8;
        }

        .payment-card {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;

            background: rgba(15, 23, 42, 0.94);

            border: 1px solid #263244;

            border-radius: 22px;

            padding: 30px;

            box-shadow:
                0 25px 70px
                rgba(0, 0, 0, 0.3);
        }

        .qr-section {
            text-align: center;
        }

        .qr-title {
            color: white;
            font-size: 21px;
            font-weight: 700;
            margin-bottom: 18px;
        }

        .qr-box {
            display: inline-flex;

            padding: 18px;

            background: white;

            border-radius: 18px;

            box-shadow:
                0 15px 40px
                rgba(0, 0, 0, 0.3);
        }

        .qr-box img {
            width: 310px;
            max-width: 100%;
            height: auto;
            display: block;
        }

        .scan-text {
            margin-top: 18px;
            color: #94a3b8;
            line-height: 1.6;
        }

        .info-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .info-title {
            color: white;
            font-size: 21px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .info-item {
            padding: 15px 0;

            border-bottom: 1px solid #1e293b;
        }

        .info-label {
            color: #64748b;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .info-value {
            color: white;
            font-size: 16px;
            font-weight: 600;
            word-break: break-word;
        }

        .amount {
            color: #60a5fa;
            font-size: 27px;
            font-weight: 800;
        }

        .transfer-content {
            color: #a78bfa;
            font-size: 19px;
            font-weight: 800;
        }

        .notice {
            margin-top: 20px;

            padding: 16px;

            border-radius: 13px;

            background: rgba(37, 99, 235, 0.10);

            border: 1px solid
                rgba(59, 130, 246, 0.25);

            color: #bfdbfe;

            line-height: 1.6;
        }

        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .btn-main {
            flex: 1;

            padding: 13px 18px;

            border-radius: 11px;

            border: none;

            color: white;

            text-decoration: none;

            text-align: center;

            font-weight: 700;

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #7c3aed
                );

            transition: 0.2s;
        }

        .btn-main:hover {
            color: white;

            transform: translateY(-2px);

            box-shadow:
                0 10px 25px
                rgba(37, 99, 235, 0.25);
        }

        .btn-back {
            flex: 1;

            padding: 13px 18px;

            border-radius: 11px;

            border: 1px solid #334155;

            color: #cbd5e1;

            text-decoration: none;

            text-align: center;

            font-weight: 600;

            background: #1e293b;
        }

        .btn-back:hover {
            color: white;
            background: #334155;
        }

        .success-note {
            margin-top: 25px;

            text-align: center;

            color: #86efac;

            font-size: 14px;
        }

        @media (max-width: 800px) {

            .payment-card {
                grid-template-columns: 1fr;
            }

            .qr-box img {
                width: 280px;
            }

        }

    </style>

</head>

<body>

<nav class="navbar">

    <div class="container py-3">

        <a
            href="index.php"
            class="brand"
        >

            <i class="bi bi-cpu-fill"></i>

            TechShop

        </a>

    </div>

</nav>

<main class="page">

    <div class="title">

        <h1>
            <i class="bi bi-qr-code-scan"></i>
            Thanh toán đơn hàng
        </h1>

        <p>
            Quét mã QR bằng ứng dụng ngân hàng để thanh toán
        </p>

    </div>

    <div class="payment-card">

        <!-- QR -->

        <section class="qr-section">

            <div class="qr-title">
                Quét mã QR để thanh toán
            </div>

            <div class="qr-box">

                <img
                    src="<?= htmlspecialchars($qr_url) ?>"
                    alt="QR thanh toán TechShop"
                >

            </div>

            <div class="scan-text">

                Mở ứng dụng ngân hàng của bạn
                <br>

                và quét mã QR bên trên.

            </div>

        </section>

        <!-- THÔNG TIN -->

        <section class="info-section">

            <div class="info-title">
                Thông tin chuyển khoản
            </div>

            <div class="info-item">

                <div class="info-label">
                    Mã đơn hàng
                </div>

                <div class="info-value">
                    #<?= $order_id ?>
                </div>

            </div>

            <div class="info-item">

                <div class="info-label">
                    Ngân hàng
                </div>

                <div class="info-value">
                    Vietcombank
                </div>

            </div>

            <div class="info-item">

                <div class="info-label">
                    Số tài khoản
                </div>

                <div class="info-value">
                    <?= $account_number ?>
                </div>

            </div>

            <div class="info-item">

                <div class="info-label">
                    Chủ tài khoản
                </div>

                <div class="info-value">
                    <?= $account_name ?>
                </div>

            </div>

            <div class="info-item">

                <div class="info-label">
                    Số tiền cần thanh toán
                </div>

                <div class="info-value amount">

                    <?= number_format(
                        $amount,
                        0,
                        ",",
                        "."
                    ) ?>

                    ₫

                </div>

            </div>

            <div class="info-item">

                <div class="info-label">
                    Nội dung chuyển khoản
                </div>

                <div class="info-value transfer-content">

                    <?= htmlspecialchars($transfer_content) ?>

                </div>

            </div>

            <div class="notice">

                <i class="bi bi-info-circle-fill"></i>

                Khi chuyển khoản, vui lòng giữ nguyên
                <strong>
                    số tiền
                </strong>
                và
                <strong>
                    nội dung chuyển khoản
                </strong>
                để cửa hàng dễ dàng đối soát.

            </div>

            <div class="buttons">

                <a
                    href="lich-su-don-hang.php"
                    class="btn-back"
                >
                    <i class="bi bi-clock-history"></i>
                    Lịch sử đơn hàng
                </a>

                <a
                    href="index.php"
                    class="btn-main"
                >
                    <i class="bi bi-house"></i>
                    Về trang chủ
                </a>

            </div>

        </section>

    </div>

    <div class="success-note">

        <i class="bi bi-shield-check"></i>

        QR được tạo riêng cho đơn hàng #<?= $order_id ?>

    </div>

</main>

</body>

</html>