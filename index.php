<?php
session_start();

if (isset($_SESSION['login'])) {

    if ($_SESSION['tipe'] == 'admin') {
        header("Location: admin/index.php");
        exit;
    }

    if ($_SESSION['tipe'] == 'kasir') {
        header("Location: kasir/index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login - Glowé Beauty</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">

<style>

:root{
    --ivory:#F8F5ED;
    --cream:#FAF9F5;
    --green:#315C4D;
    --green-light:#E8EEE9;
    --mustard:#B79A55;
    --line:#E3DED2;
    --text:#354044;
    --muted:#7A8384;
}

*{
    box-sizing:border-box;
}

body{
    margin:0;
    min-height:100vh;
    background:var(--cream);
    color:var(--text);
    font-family:Arial,sans-serif;
}


/* =========================
   LOGIN PAGE
========================= */

.login-page{
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:30px 20px;
    position:relative;
    overflow:hidden;
}


/* DEKORASI */

.login-page::before{
    content:"";
    position:absolute;
    width:430px;
    height:430px;
    border-radius:50%;
    background:#EEF2EC;
    top:-180px;
    left:-150px;
}

.login-page::after{
    content:"";
    position:absolute;
    width:330px;
    height:330px;
    border-radius:50%;
    border:1px solid #DDD7C9;
    right:-120px;
    bottom:-100px;
}


/* =========================
   CARD
========================= */

.login-card{
    width:100%;
    max-width:430px;
    background:white;
    border:1px solid var(--line);
    border-radius:14px;
    box-shadow:0 12px 35px rgba(49,92,77,.08);
    padding:42px 42px 38px;
    position:relative;
    z-index:2;
}


/* =========================
   LOGO
========================= */

.logo-area{
    text-align:center;
    margin-bottom:34px;
}

.logo-main{
    color:var(--green);
    font:600 39px Georgia,serif;
    letter-spacing:4px;
    line-height:1;
}

.logo-bottom{
    display:flex;
    align-items:center;
    justify-content:center;
    margin-top:10px;
}

.logo-line{
    width:35px;
    height:1px;
    background:var(--mustard);
    margin-right:10px;
}

.logo-sub{
    color:var(--text);
    font-size:9px;
    font-weight:700;
    letter-spacing:4px;
}

.login-title{
    color:var(--green);
    font:600 25px Georgia,serif;
    margin-top:25px;
    margin-bottom:7px;
}

.login-subtitle{
    color:var(--muted);
    font-size:13px;
    margin:0;
}


/* =========================
   FORM
========================= */

.form-label{
    color:var(--green);
    font-size:12px;
    font-weight:700;
    margin-bottom:8px;
}

.form-control{
    height:46px;
    border:1px solid #D8D4CA;
    border-radius:6px;
    font-size:13px;
    padding:10px 13px;
    background:#FCFBF8;
}

.form-control::placeholder{
    color:#A7ADAC;
}

.form-control:focus{
    border-color:#9DB5AA;
    background:white;
    box-shadow:0 0 0 .18rem rgba(49,92,77,.10);
}


/* =========================
   BUTTON
========================= */

.btn-login{
    width:100%;
    height:46px;
    border:none;
    border-radius:6px;
    background:var(--green);
    color:white;
    font-size:13px;
    font-weight:700;
    letter-spacing:.3px;
    transition:.2s;
}

.btn-login:hover{
    background:#274C40;
    color:white;
    transform:translateY(-1px);
}


/* =========================
   FOOTER
========================= */

.login-footer{
    text-align:center;
    color:#9A9F9F;
    font-size:10px;
    margin-top:25px;
    padding-top:20px;
    border-top:1px solid #EEEAE1;
}


/* =========================
   RESPONSIVE
========================= */

@media(max-width:576px){

    .login-card{
        padding:35px 25px 30px;
    }

    .logo-main{
        font-size:34px;
    }

    .login-title{
        font-size:23px;
    }

}

</style>

</head>


<body>

<div class="login-page">

    <div class="login-card">

        <!-- LOGO -->

        <div class="logo-area">

            <div class="logo-main">
                GLOWÉ
            </div>

            <div class="logo-bottom">

                <div class="logo-line"></div>

                <div class="logo-sub">
                    BEAUTY
                </div>

            </div>

            <div class="login-title">
                Selamat Datang
            </div>

            <p class="login-subtitle">
                Silakan masuk ke sistem penjualan
            </p>

        </div>


        <!-- FORM -->

        <form action="proses_login.php" method="POST">

            <div class="mb-3">

                <label class="form-label">
                    Username
                </label>

                <input
                    type="text"
                    name="username"
                    class="form-control"
                    placeholder="Masukkan username"
                    autocomplete="username"
                    required
                >

            </div>


            <div class="mb-4">

                <label class="form-label">
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Masukkan password"
                    autocomplete="current-password"
                    required
                >

            </div>


            <button
                type="submit"
                name="login"
                class="btn-login">

                Masuk

            </button>

        </form>


        <div class="login-footer">
            Sistem Penjualan Kosmetik
        </div>

    </div>

</div>

</body>

</html>