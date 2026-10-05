<?php

session_start();

require_once "config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("
        SELECT *
        FROM users
        WHERE username = ?
    ");

    $stmt->bind_param(
        "s",
        $username
    );

    $stmt->execute();

    $user =
        $stmt
        ->get_result()
        ->fetch_assoc();

    $stmt->close();

    if (
        $user
        && password_verify(
            $password,
            $user["password"]
        )
    ) {

        $_SESSION["user_id"] =
            $user["id"];

        $_SESSION["username"] =
            $user["username"];

        $_SESSION["fullname"] =
            $user["fullname"];

        $_SESSION["role"] =
            $user["role"];

        if (
            $user["role"] === "admin"
            || $user["role"] === "manager"
        ) {

            header(
                "Location: admin/index.php"
            );

        } else {

            header(
                "Location: index.php"
            );
        }

        exit;

    } else {

        $error =
            "Tên đăng nhập hoặc mật khẩu không đúng.";
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

<title>Đăng nhập - TechShop</title>

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
   TOP NAVBAR
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

    letter-spacing: -0.5px;

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
   LOGIN AREA
========================= */

.login-wrapper {

    min-height:
        calc(100vh - 70px);

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 50px 20px;

}

.login-box {

    width: 100%;

    max-width: 460px;

    padding: 42px;

    border-radius: 24px;

    background:
        rgba(255,255,255,0.055);

    border:
        1px solid rgba(255,255,255,0.10);

    box-shadow:
        0 25px 80px rgba(0,0,0,0.45);

    backdrop-filter: blur(20px);

    animation:
        loginAppear 0.6s ease;

}

@keyframes loginAppear {

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
   ICON
========================= */

.login-icon {

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

.login-icon i {

    font-size: 32px;

}


/* =========================
   TITLE
========================= */

.login-title {

    text-align: center;

    font-size: 30px;

    font-weight: 800;

    margin-bottom: 8px;

}

.login-subtitle {

    text-align: center;

    color: #8f98a5;

    font-size: 14px;

    margin-bottom: 30px;

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

    margin-bottom: 20px;

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

    height: 52px;

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
   LOGIN BUTTON
========================= */

.login-btn {

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

.login-btn:hover {

    transform:
        translateY(-2px);

    box-shadow:
        0 15px 35px rgba(13,110,253,0.40);

}


/* =========================
   ERROR
========================= */

.error-box {

    display: flex;

    align-items: center;

    gap: 10px;

    padding: 13px 15px;

    margin-bottom: 20px;

    border-radius: 12px;

    color: #ffb4b4;

    background:
        rgba(220,53,69,0.12);

    border:
        1px solid rgba(220,53,69,0.25);

    font-size: 14px;

}


/* =========================
   REGISTER
========================= */

.register-text {

    text-align: center;

    margin-top: 25px;

    color: #8f98a5;

    font-size: 14px;

}

.register-text a {

    color: #5da9ff;

    font-weight: 700;

    text-decoration: none;

}

.register-text a:hover {

    color: white;

}


/* =========================
   BACK HOME
========================= */

.back-home {

    display: block;

    text-align: center;

    margin-top: 20px;

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

    margin-top: 25px;

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

    .login-wrapper {

        padding: 30px 15px;

    }

    .login-box {

        padding: 30px 22px;

        border-radius: 20px;

    }

    .login-title {

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
     LOGIN
========================= -->

<main class="login-wrapper">

    <div class="login-box">


        <!-- ICON -->

        <div class="login-icon">

            <i class="bi bi-person-lock"></i>

        </div>


        <!-- TITLE -->

        <h1 class="login-title">

            Chào mừng trở lại

        </h1>


        <p class="login-subtitle">

            Đăng nhập để tiếp tục mua sắm tại TechShop

        </p>


        <!-- ERROR -->

        <?php if ($error): ?>

            <div class="error-box">

                <i class="bi bi-exclamation-triangle-fill"></i>

                <span>
                    <?= htmlspecialchars($error) ?>
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
                    autocomplete="current-password"
                    required
                >

            </div>


            <!-- BUTTON -->

            <button
                type="submit"
                class="login-btn"
            >

                <i class="bi bi-box-arrow-in-right me-2"></i>

                Đăng nhập

            </button>

        </form>


        <!-- REGISTER -->

        <div class="register-text">

            Chưa có tài khoản?

            <a href="dang-ky.php">

                Đăng ký ngay

            </a>

        </div>


        <!-- BACK HOME -->

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

            Thông tin của bạn được bảo mật an toàn

        </div>

    </div>

</main>


</body>

</html>