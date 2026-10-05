<?php

session_start();

require_once "config/database.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $fullname = trim($_POST["fullname"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);

    if (
        $username === ""
        || $password === ""
        || $fullname === ""
        || $email === ""
    ) {

        $error =
            "Vui lòng nhập đầy đủ thông tin.";

    } else {

        $stmt = $conn->prepare(
            "SELECT id FROM users WHERE username = ? OR email = ?"
        );

        $stmt->bind_param(
            "ss",
            $username,
            $email
        );

        $stmt->execute();

        $exists =
            $stmt->get_result()
            ->num_rows > 0;

        $stmt->close();

        if ($exists) {

            $error =
                "Tên đăng nhập hoặc email đã tồn tại.";

        } else {

            $hashedPassword =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

            $role = "customer";

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
                $hashedPassword,
                $fullname,
                $email,
                $phone,
                $role
            );

            if ($stmt->execute()) {

                $success =
                    "Đăng ký thành công. Bạn có thể đăng nhập.";

            } else {

                $error =
                    "Đăng ký thất bại.";
            }

            $stmt->close();
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

<title>Đăng ký - TechShop</title>

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

    min-height: 100vh;

    font-family:
        "Segoe UI",
        Arial,
        sans-serif;

    background:
        radial-gradient(
            circle at top left,
            rgba(13, 110, 253, 0.25),
            transparent 35%
        ),
        radial-gradient(
            circle at bottom right,
            rgba(111, 66, 193, 0.22),
            transparent 35%
        ),
        #080b12;

    color: #fff;

}


/* =========================
   TOP BAR
========================= */

.top-bar {

    height: 70px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0 6%;

    border-bottom:
        1px solid rgba(255,255,255,0.08);

    background:
        rgba(8, 11, 18, 0.82);

    backdrop-filter: blur(15px);

}

.logo {

    text-decoration: none;

    color: white;

    font-size: 22px;

    font-weight: 800;

}

.logo i {

    color: #4dabff;

    margin-right: 8px;

}

.home-link {

    color: #adb5bd;

    text-decoration: none;

    font-size: 14px;

    transition: 0.3s;

}

.home-link:hover {

    color: white;

}


/* =========================
   REGISTER AREA
========================= */

.register-wrapper {

    min-height:
        calc(100vh - 70px);

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 45px 20px;

}

.register-box {

    width: 100%;

    max-width: 520px;

    padding: 40px;

    border-radius: 24px;

    background:
        rgba(255,255,255,0.055);

    border:
        1px solid rgba(255,255,255,0.10);

    box-shadow:
        0 25px 80px rgba(0,0,0,0.45);

    backdrop-filter: blur(20px);

    animation:
        registerAppear 0.6s ease;

}

@keyframes registerAppear {

    from {

        opacity: 0;

        transform:
            translateY(25px);

    }

    to {

        opacity: 1;

        transform:
            translateY(0);

    }

}


/* =========================
   REGISTER ICON
========================= */

.register-icon {

    width: 72px;

    height: 72px;

    margin:
        0 auto 20px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 20px;

    background:
        linear-gradient(
            135deg,
            #0d6efd,
            #6f42c1
        );

    box-shadow:
        0 15px 35px rgba(13,110,253,0.35);

}

.register-icon i {

    font-size: 32px;

}


/* =========================
   TITLE
========================= */

.register-title {

    text-align: center;

    font-size: 30px;

    font-weight: 800;

    margin-bottom: 8px;

}

.register-subtitle {

    text-align: center;

    color: #8f98a5;

    font-size: 14px;

    margin-bottom: 28px;

}


/* =========================
   FORM
========================= */

.form-label {

    color: #dce1e7;

    font-size: 14px;

    font-weight: 600;

    margin-bottom: 8px;

}

.input-group {

    margin-bottom: 17px;

}

.input-group-text {

    width: 48px;

    justify-content: center;

    color: #6ea8fe;

    background:
        rgba(255,255,255,0.06);

    border:
        1px solid rgba(255,255,255,0.12);

    border-right: none;

}

.form-control {

    height: 50px;

    color: white !important;

    background:
        rgba(255,255,255,0.06) !important;

    border:
        1px solid rgba(255,255,255,0.12);

    border-left: none;

    box-shadow: none !important;

}

.form-control::placeholder {

    color: #737d8b;

}

.form-control:focus {

    border-color: #4dabff;

}


/* =========================
   REGISTER BUTTON
========================= */

.register-btn {

    width: 100%;

    height: 54px;

    margin-top: 8px;

    border: none;

    border-radius: 12px;

    color: white;

    font-size: 16px;

    font-weight: 700;

    background:
        linear-gradient(
            135deg,
            #0d6efd,
            #6f42c1
        );

    box-shadow:
        0 10px 25px rgba(13,110,253,0.25);

    transition: 0.3s;

}

.register-btn:hover {

    transform:
        translateY(-2px);

    box-shadow:
        0 15px 35px rgba(13,110,253,0.40);

}


/* =========================
   ALERT ERROR
========================= */

.alert-box {

    display: flex;

    align-items: center;

    gap: 10px;

    padding: 13px 15px;

    margin-bottom: 20px;

    border-radius: 12px;

    font-size: 14px;

}

.error-box {

    color: #ffb4b4;

    background:
        rgba(220,53,69,0.12);

    border:
        1px solid rgba(220,53,69,0.25);

}

.success-box {

    color: #8ff0b2;

    background:
        rgba(25,135,84,0.12);

    border:
        1px solid rgba(25,135,84,0.25);

}


/* =========================
   LOGIN LINK
========================= */

.login-text {

    text-align: center;

    margin-top: 24px;

    color: #8f98a5;

    font-size: 14px;

}

.login-text a {

    color: #5da9ff;

    font-weight: 700;

    text-decoration: none;

}

.login-text a:hover {

    color: white;

}


/* =========================
   HOME
========================= */

.back-home {

    display: block;

    text-align: center;

    margin-top: 18px;

    color: #737d8b;

    text-decoration: none;

    font-size: 13px;

    transition: 0.3s;

}

.back-home:hover {

    color: white;

}


/* =========================
   SECURITY
========================= */

.security {

    display: flex;

    justify-content: center;

    align-items: center;

    gap: 7px;

    margin-top: 23px;

    color: #687281;

    font-size: 12px;

}

.security i {

    color: #43d17a;

}


/* =========================
   RESPONSIVE
========================= */

@media (max-width: 576px) {

    .top-bar {

        padding: 0 20px;

    }

    .register-wrapper {

        padding: 30px 15px;

    }

    .register-box {

        padding: 30px 22px;

        border-radius: 20px;

    }

    .register-title {

        font-size: 26px;

    }

}

</style>

</head>

<body>


<!-- =========================
     TOP BAR
========================= -->

<header class="top-bar">

    <a
        href="index.php"
        class="logo"
    >

        <i class="bi bi-cpu-fill"></i>

        TechShop

    </a>


    <a
        href="index.php"
        class="home-link"
    >

        <i class="bi bi-arrow-left"></i>

        Về trang chủ

    </a>

</header>


<!-- =========================
     REGISTER
========================= -->

<main class="register-wrapper">

    <div class="register-box">


        <!-- ICON -->

        <div class="register-icon">

            <i class="bi bi-person-plus-fill"></i>

        </div>


        <!-- TITLE -->

        <h1 class="register-title">

            Tạo tài khoản

        </h1>


        <p class="register-subtitle">

            Tham gia TechShop và bắt đầu mua sắm ngay hôm nay

        </p>


        <!-- ERROR -->

        <?php if ($error): ?>

            <div class="alert-box error-box">

                <i class="bi bi-exclamation-triangle-fill"></i>

                <span>
                    <?= htmlspecialchars($error) ?>
                </span>

            </div>

        <?php endif; ?>


        <!-- SUCCESS -->

        <?php if ($success): ?>

            <div class="alert-box success-box">

                <i class="bi bi-check-circle-fill"></i>

                <span>
                    <?= htmlspecialchars($success) ?>
                </span>

            </div>

        <?php endif; ?>


        <!-- FORM -->

        <form method="POST">


            <!-- USERNAME -->

            <label class="form-label">

                Tên đăng nhập

            </label>

            <div class="input-group">

                <span class="input-group-text">

                    <i class="bi bi-person"></i>

                </span>

                <input
                    type="text"
                    name="username"
                    class="form-control"
                    placeholder="Nhập tên đăng nhập"
                    autocomplete="username"
                    required
                >

            </div>


            <!-- PASSWORD -->

            <label class="form-label">

                Mật khẩu

            </label>

            <div class="input-group">

                <span class="input-group-text">

                    <i class="bi bi-lock"></i>

                </span>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Nhập mật khẩu"
                    autocomplete="new-password"
                    required
                >

            </div>


            <!-- FULL NAME -->

            <label class="form-label">

                Họ và tên

            </label>

            <div class="input-group">

                <span class="input-group-text">

                    <i class="bi bi-person-vcard"></i>

                </span>

                <input
                    type="text"
                    name="fullname"
                    class="form-control"
                    placeholder="Nhập họ và tên"
                    required
                >

            </div>


            <!-- EMAIL -->

            <label class="form-label">

                Email

            </label>

            <div class="input-group">

                <span class="input-group-text">

                    <i class="bi bi-envelope"></i>

                </span>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="example@gmail.com"
                    autocomplete="email"
                    required
                >

            </div>


            <!-- PHONE -->

            <label class="form-label">

                Số điện thoại

            </label>

            <div class="input-group">

                <span class="input-group-text">

                    <i class="bi bi-telephone"></i>

                </span>

                <input
                    type="text"
                    name="phone"
                    class="form-control"
                    placeholder="Nhập số điện thoại"
                    autocomplete="tel"
                >

            </div>


            <!-- BUTTON -->

            <button
                type="submit"
                class="register-btn"
            >

                <i class="bi bi-person-plus-fill me-2"></i>

                Tạo tài khoản

            </button>

        </form>


        <!-- LOGIN -->

        <div class="login-text">

            Đã có tài khoản?

            <a href="dang-nhap.php">

                Đăng nhập ngay

            </a>

        </div>


        <!-- HOME -->

        <a
            href="index.php"
            class="back-home"
        >

            <i class="bi bi-house me-1"></i>

            Quay lại trang chủ

        </a>


        <!-- SECURITY -->

        <div class="security">

            <i class="bi bi-shield-check"></i>

            Thông tin tài khoản được bảo mật an toàn

        </div>

    </div>

</main>


</body>

</html>