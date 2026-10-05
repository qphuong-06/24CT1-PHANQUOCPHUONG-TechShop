<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../dang-nhap.php");
    exit;
}

if (
    $_SESSION["role"] !== "admin"
    && $_SESSION["role"] !== "manager"
) {
    die("Bạn không có quyền truy cập.");
}


$message = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $category_id =
        (int) $_POST["category_id"];

    $name =
        trim($_POST["name"]);

    $description =
        trim($_POST["description"]);

    $price =
        (float) $_POST["price"];

    $stock =
        (int) $_POST["stock"];

    $image =
        trim($_POST["image"]);


    if (
        $category_id <= 0
        || $name === ""
        || $price < 0
        || $stock < 0
    ) {

        $message =
            "Vui lòng nhập đầy đủ thông tin.";

    } else {

        $stmt = $conn->prepare("
            INSERT INTO products
            (
                category_id,
                name,
                description,
                price,
                stock,
                image
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "issdis",
            $category_id,
            $name,
            $description,
            $price,
            $stock,
            $image
        );


        if ($stmt->execute()) {

            header(
                "Location: san-pham.php"
            );

            exit;

        } else {

            $message =
                "Không thể thêm sản phẩm.";
        }

        $stmt->close();
    }
}


$categories =
    $conn->query(
        "SELECT * FROM categories ORDER BY name"
    );

?>

<!DOCTYPE html>
<html lang="vi">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>Thêm sản phẩm | TechShop</title>

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


/* =========================
   SIDEBAR
========================= */

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
    border-right: 1px solid rgba(255,255,255,0.08);
    padding: 24px 16px;
    z-index: 1000;
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 10px 25px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
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
        0 10px 30px rgba(37,99,235,0.30);
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

.user-box {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.07);
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
    padding: 4px 9px;
    border-radius: 20px;
    background: rgba(59,130,246,0.15);
    color: #93c5fd;
    font-size: 11px;
    font-weight: 700;
}

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
    padding: 12px 14px;
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
    background: rgba(255,255,255,0.06);
    color: white;
    transform: translateX(3px);
}

.sidebar a.active {
    background:
        linear-gradient(
            135deg,
            rgba(37,99,235,0.25),
            rgba(124,58,237,0.20)
        );
    color: white;
    border: 1px solid rgba(96,165,250,0.15);
}


/* =========================
   MAIN
========================= */

.main {
    margin-left: 260px;
    min-height: 100vh;
}


/* =========================
   TOPBAR
========================= */

.topbar {
    height: 72px;
    padding: 0 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(255,255,255,0.07);
    background: rgba(15,23,42,0.75);
    backdrop-filter: blur(15px);
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
        0 0 12px rgba(34,197,94,0.8);
}


/* =========================
   CONTENT
========================= */

.content {
    padding: 34px;
    max-width: 1200px;
}


/* =========================
   PAGE HEADER
========================= */

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


/* =========================
   BUTTON
========================= */

.btn-back {
    text-decoration: none;
    color: #cbd5e1;
    border: 1px solid rgba(255,255,255,0.10);
    background: rgba(255,255,255,0.04);
    padding: 11px 17px;
    border-radius: 12px;
    font-weight: 600;
    transition: 0.25s;
}

.btn-back:hover {
    color: white;
    background: rgba(255,255,255,0.08);
}


/* =========================
   FORM CARD
========================= */

.form-card {
    background:
        linear-gradient(
            145deg,
            rgba(30,41,59,0.95),
            rgba(15,23,42,0.95)
        );
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 22px;
    padding: 30px;
    box-shadow:
        0 25px 70px rgba(0,0,0,0.30);
}

.form-card-header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding-bottom: 22px;
    margin-bottom: 26px;
    border-bottom: 1px solid rgba(255,255,255,0.07);
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
            #2563eb,
            #7c3aed
        );
    font-size: 21px;
    color: white;
}

.form-heading {
    margin: 0;
    color: white;
    font-size: 19px;
    font-weight: 750;
}

.form-description {
    margin: 4px 0 0;
    color: #64748b;
    font-size: 13px;
}


/* =========================
   FORM
========================= */

.form-label {
    color: #cbd5e1;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 8px;
}

.form-control,
.form-select {
    background: rgba(15,23,42,0.85);
    border: 1px solid rgba(255,255,255,0.10);
    color: #f8fafc;
    border-radius: 12px;
    min-height: 48px;
    padding: 12px 14px;
}

.form-control::placeholder {
    color: #64748b;
}

.form-control:focus,
.form-select:focus {
    background: rgba(15,23,42,0.95);
    color: white;
    border-color: #3b82f6;
    box-shadow:
        0 0 0 4px rgba(59,130,246,0.12);
}

.form-select option {
    background: #0f172a;
    color: white;
}

textarea.form-control {
    min-height: 120px;
    resize: vertical;
}

.input-group-text {
    background: rgba(15,23,42,0.9);
    color: #64748b;
    border: 1px solid rgba(255,255,255,0.10);
    border-radius: 12px 0 0 12px;
}

.input-group .form-control {
    border-radius: 0 12px 12px 0;
}


/* =========================
   ALERT
========================= */

.custom-alert {
    background: rgba(239,68,68,0.10);
    border: 1px solid rgba(239,68,68,0.25);
    color: #fca5a5;
    border-radius: 13px;
    padding: 14px 16px;
    margin-bottom: 24px;
}


/* =========================
   IMAGE INFO
========================= */

.image-help {
    margin-top: 9px;
    color: #64748b;
    font-size: 12px;
    line-height: 1.7;
}

.image-help strong {
    color: #94a3b8;
}


/* =========================
   PREVIEW
========================= */

.preview-box {
    margin-top: 12px;
    display: none;
    background: rgba(255,255,255,0.03);
    border: 1px dashed rgba(255,255,255,0.12);
    border-radius: 14px;
    padding: 12px;
}

.preview-box img {
    width: 120px;
    height: 90px;
    object-fit: cover;
    border-radius: 10px;
}


/* =========================
   ACTIONS
========================= */

.form-actions {
    display: flex;
    gap: 12px;
    padding-top: 25px;
    margin-top: 10px;
    border-top: 1px solid rgba(255,255,255,0.07);
}

.btn-save {
    border: none;
    color: white;
    padding: 13px 24px;
    border-radius: 12px;
    font-weight: 700;
    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );
    box-shadow:
        0 10px 25px rgba(37,99,235,0.25);
    transition: 0.25s;
}

.btn-save:hover {
    color: white;
    transform: translateY(-2px);
    box-shadow:
        0 14px 30px rgba(37,99,235,0.35);
}

.btn-cancel {
    text-decoration: none;
    color: #cbd5e1;
    padding: 13px 22px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.10);
    background: rgba(255,255,255,0.04);
    font-weight: 700;
    transition: 0.25s;
}

.btn-cancel:hover {
    color: white;
    background: rgba(255,255,255,0.08);
}


/* =========================
   INFO CARD
========================= */

.info-card {
    margin-top: 22px;
    background: rgba(255,255,255,0.025);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 18px;
    padding: 20px;
}

.info-title {
    color: #e2e8f0;
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 10px;
}

.info-text {
    color: #64748b;
    font-size: 12px;
    line-height: 1.7;
    margin: 0;
}


/* =========================
   RESPONSIVE
========================= */

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
        padding: 0 20px;
    }

}

@media (max-width: 600px) {

    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .form-card {
        padding: 20px;
    }

    .form-actions {
        flex-direction: column;
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


<!-- =========================
     SIDEBAR
========================= -->

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
                $_SESSION["fullname"] ?? "Quản trị viên"
            ) ?>

        </div>

        <span class="user-role">

            <?= htmlspecialchars(
                strtoupper(
                    $_SESSION["role"] ?? "admin"
                )
            ) ?>

        </span>

    </div>


    <div class="menu-title">
        Quản lý
    </div>


    <a href="index.php">

        <i class="bi bi-grid-1x2-fill"></i>

        Tổng quan

    </a>


    <a
        href="san-pham.php"
        class="active"
    >

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


    <a href="tai-khoan.php">

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


<!-- =========================
     MAIN
========================= -->

<main class="main">


    <!-- TOPBAR -->

    <div class="topbar">

        <div class="topbar-title">

            <i class="bi bi-plus-circle me-2"></i>

            Thêm sản phẩm

        </div>


        <div class="topbar-right">

            <span class="status-dot"></span>

            Hệ thống đang hoạt động

        </div>

    </div>


    <!-- CONTENT -->

    <div class="content">


        <!-- HEADER -->

        <div class="page-header">

            <div>

                <h1 class="page-title">
                    Thêm sản phẩm mới
                </h1>

                <div class="page-subtitle">

                    Tạo sản phẩm mới và đưa vào cửa hàng TechShop.

                </div>

            </div>


            <a
                href="san-pham.php"
                class="btn-back"
            >

                <i class="bi bi-arrow-left me-1"></i>

                Quay lại sản phẩm

            </a>

        </div>


        <!-- FORM CARD -->

        <div class="form-card">


            <div class="form-card-header">

                <div class="form-icon">

                    <i class="bi bi-box-seam-fill"></i>

                </div>


                <div>

                    <h2 class="form-heading">
                        Thông tin sản phẩm
                    </h2>

                    <p class="form-description">
                        Nhập đầy đủ thông tin để thêm sản phẩm vào hệ thống.
                    </p>

                </div>

            </div>


            <?php if ($message): ?>

                <div class="custom-alert">

                    <i class="bi bi-exclamation-triangle-fill me-2"></i>

                    <?= htmlspecialchars($message) ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <!-- CATEGORY -->

                <div class="mb-4">

                    <label class="form-label">

                        <i class="bi bi-tags me-1"></i>

                        Danh mục

                    </label>


                    <select
                        name="category_id"
                        class="form-select"
                        required
                    >

                        <option value="">
                            -- Chọn danh mục sản phẩm --
                        </option>


                        <?php while (
                            $category =
                            $categories->fetch_assoc()
                        ): ?>

                            <option
                                value="<?= $category["id"] ?>"
                                <?= (
                                    isset($_POST["category_id"])
                                    && $_POST["category_id"]
                                    == $category["id"]
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >

                                <?= htmlspecialchars(
                                    $category["name"]
                                ) ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>


                <!-- PRODUCT NAME -->

                <div class="mb-4">

                    <label class="form-label">

                        <i class="bi bi-box me-1"></i>

                        Tên sản phẩm

                    </label>


                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        placeholder="Ví dụ: Tai nghe Bluetooth Pro"
                        value="<?= htmlspecialchars(
                            $_POST["name"] ?? ""
                        ) ?>"
                        required
                    >

                </div>


                <!-- DESCRIPTION -->

                <div class="mb-4">

                    <label class="form-label">

                        <i class="bi bi-card-text me-1"></i>

                        Mô tả sản phẩm

                    </label>


                    <textarea
                        name="description"
                        class="form-control"
                        rows="5"
                        placeholder="Nhập thông tin mô tả sản phẩm..."
                    ><?= htmlspecialchars(
                        $_POST["description"] ?? ""
                    ) ?></textarea>

                </div>


                <div class="row g-4">


                    <!-- PRICE -->

                    <div class="col-md-6">

                        <label class="form-label">

                            <i class="bi bi-cash-stack me-1"></i>

                            Giá sản phẩm

                        </label>


                        <div class="input-group">

                            <input
                                type="number"
                                name="price"
                                class="form-control"
                                min="0"
                                step="1000"
                                placeholder="499000"
                                value="<?= htmlspecialchars(
                                    $_POST["price"] ?? ""
                                ) ?>"
                                required
                            >

                            <span class="input-group-text">
                                VNĐ
                            </span>

                        </div>

                    </div>


                    <!-- STOCK -->

                    <div class="col-md-6">

                        <label class="form-label">

                            <i class="bi bi-boxes me-1"></i>

                            Số lượng tồn kho

                        </label>


                        <input
                            type="number"
                            name="stock"
                            class="form-control"
                            min="0"
                            placeholder="20"
                            value="<?= htmlspecialchars(
                                $_POST["stock"] ?? ""
                            ) ?>"
                            required
                        >

                    </div>

                </div>


                <!-- IMAGE -->

                <div class="mt-4">

                    <label class="form-label">

                        <i class="bi bi-image me-1"></i>

                        Tên hình ảnh

                    </label>


                    <input
                        type="text"
                        name="image"
                        id="imageInput"
                        class="form-control"
                        placeholder="Ví dụ: tainghe.jpg"
                        value="<?= htmlspecialchars(
                            $_POST["image"] ?? ""
                        ) ?>"
                    >


                    <div class="image-help">

                        <i class="bi bi-info-circle me-1"></i>

                        Chỉ nhập tên file hình ảnh, ví dụ:

                        <strong>tainghe.jpg</strong>

                        <br>

                        Hình ảnh phải nằm trong thư mục:

                        <strong>
                            C:\xampp\htdocs\phu-kien-cong-nghe\images
                        </strong>

                    </div>


                    <div
                        class="preview-box"
                        id="previewBox"
                    >

                        <div
                            class="small text-secondary mb-2"
                        >
                            Xem trước hình ảnh
                        </div>


                        <img
                            id="previewImage"
                            src=""
                            alt="Preview"
                        >

                    </div>

                </div>


                <!-- ACTIONS -->

                <div class="form-actions">


                    <button
                        type="submit"
                        class="btn-save"
                    >

                        <i class="bi bi-check-circle-fill me-2"></i>

                        Lưu sản phẩm

                    </button>


                    <a
                        href="san-pham.php"
                        class="btn-cancel"
                    >

                        <i class="bi bi-x-circle me-2"></i>

                        Hủy

                    </a>

                </div>


            </form>


        </div>


        <!-- INFO -->

        <div class="info-card">

            <div class="info-title">

                <i class="bi bi-lightbulb me-2"></i>

                Lưu ý khi thêm sản phẩm

            </div>


            <p class="info-text">

                • Giá sản phẩm nhập bằng VNĐ, ví dụ
                <strong>499000</strong> tương ứng 499.000 VNĐ.

                <br>

                • Tồn kho là số lượng sản phẩm hiện đang có.

                <br>

                • Tên hình ảnh phải chính xác với file nằm trong thư mục
                <strong>images</strong>.

                <br>

                • Sau khi lưu thành công, sản phẩm sẽ xuất hiện trong danh sách quản lý sản phẩm.

            </p>

        </div>


    </div>

</main>


<script>

/* =========================
   IMAGE PREVIEW
========================= */

const imageInput =
    document.getElementById("imageInput");

const previewBox =
    document.getElementById("previewBox");

const previewImage =
    document.getElementById("previewImage");


function updatePreview() {

    const fileName =
        imageInput.value.trim();


    if (fileName === "") {

        previewBox.style.display = "none";

        return;
    }


    previewImage.src =
        "../images/" + fileName;


    previewImage.onload = function () {

        previewBox.style.display = "block";

    };


    previewImage.onerror = function () {

        previewBox.style.display = "none";

    };

}


imageInput.addEventListener(
    "input",
    updatePreview
);


updatePreview();

</script>


</body>

</html>
