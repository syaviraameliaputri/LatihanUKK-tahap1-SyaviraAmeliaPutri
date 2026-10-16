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
    --green:#315C4D;
    --green-dark:#274C40;
    --ivory:#F8F5ED;
    --cream:#FBFAF6;
    --mustard:#B79A55;
    --text:#354044;
    --muted:#7A8384;
    --line:#E3DED2;
}

*{
    box-sizing:border-box;
}

body{
    margin:0;
    min-height:100vh;
    font-family:Arial,sans-serif;
    background:var(--cream);
    color:var(--text);
}


/* =========================
   LAYOUT
========================= */

.login-wrapper{
    min-height:100vh;
    display:flex;
}


/* =========================
   BAGIAN KIRI
========================= */

.brand-side{
    width:52%;
    min-height:100vh;
    background:var(--green);
    position:relative;
    overflow:hidden;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:60px;
}


/* dekorasi lingkaran */

.circle-one{
    position:absolute;
    width:420px;
    height:420px;
    border:1px solid rgba(255,255,255,.12);
    border-radius:50%;
    top:-180px;
    left:-170px;
}

.circle-two{
    position:absolute;
    width:560px;
    height:560px;
    border:1px solid rgba(255,255,255,.08);
    border-radius:50%;
    bottom:-300px;
    right:-250px;
}

.circle-three{
    position:absolute;
    width:170px;
    height:170px;
    background:rgba(183,154,85,.12);
    border-radius:50%;
    top:90px;
    right:80px;
}


/* isi brand */

.brand-content{
    position:relative;
    z-index:2;
    max-width:520px;
    color:white;
}

.brand-small{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:25px;
}

.brand-small-line{
    width:45px;
    height:1px;
    background:var(--mustard);
}

.brand-small-text{
    font-size:10px;
    letter-spacing:4px;
    font-weight:700;
}

.brand-title{
    font:600 72px Georgia,serif;
    letter-spacing:7px;
    line-height:.95;
    margin:0;
}

.brand-subtitle{
    font-size:11px;
    font-weight:700;
    letter-spacing:7px;
    margin-top:15px;
    color:#E8E4D9;
}

.brand-description{
    margin-top:45px;
    max-width:380px;
    color:#D8E1DC;
    font-size:14px;
    line-height:1.8;
}

.brand-bottom{
    margin-top:60px;
    color:#BFCBC5;
    font-size:11px;
    letter-spacing:1px;
}


/* =========================
   BAGIAN KANAN
========================= */

.form-side{
    width:48%;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:50px;
    background:var(--cream);
}

.login-box{
    width:100%;
    max-width:390px;
}


/* judul */

.form-heading{
    margin-bottom:38px;
}

.form-heading h2{
    color:var(--green);
    font:600 32px Georgia,serif;
    margin:0 0 8px;
}

.form-heading p{
    color:var(--muted);
    font-size:13px;
    margin:0;
}


/* garis kecil */

.heading-line{
    width:45px;
    height:2px;
    background:var(--mustard);
    margin-top:18px;
}


/* =========================
   FORM
========================= */

.form-group{
    margin-bottom:22px;
}

.form-label{
    display:block;
    color:var(--green);
    font-size:12px;
    font-weight:700;
    margin-bottom:9px;
}

.form-control{
    width:100%;
    height:49px;
    border:1px solid var(--line);
    border-radius:5px;
    background:white;
    padding:0 14px;
    font-size:13px;
    color:var(--text);
}

.form-control::placeholder{
    color:#A9AEAC;
}

.form-control:focus{
    outline:none;
    border-color:#91A99F;
    box-shadow:0 0 0 3px rgba(49,92,77,.08);
}


/* =========================
   BUTTON
========================= */

.btn-login{
    width:100%;
    height:49px;
    margin-top:7px;
    border:none;
    border-radius:5px;
    background:var(--green);
    color:white;
    font-size:13px;
    font-weight:700;
    letter-spacing:.4px;
    cursor:pointer;
    transition:.2s;
}

.btn-login:hover{
    background:var(--green-dark);
}


/* =========================
   FOOTER
========================= */

.form-footer{
    margin-top:30px;
    padding-top:20px;
    border-top:1px solid var(--line);
    text-align:center;
    color:#9A9F9F;
    font-size:10px;
}


/* =========================
   RESPONSIVE
========================= */

@media(max-width:850px){

    .login-wrapper{
        display:block;
    }

    .brand-side{
        width:100%;
        min-height:330px;
        padding:45px 30px;
    }

    .brand-title{
        font-size:52px;
    }

    .brand-description,
    .brand-bottom{
        display:none;
    }

    .circle-three{
        right:-30px;
        top:-50px;
    }

    .form-side{
        width:100%;
        min-height:auto;
        padding:45px 25px 60px;
    }

}

@media(max-width:500px){

    .brand-side{
        min-height:270px;
    }

    .brand-title{
        font-size:42px;
        letter-spacing:5px;
    }

    .brand-subtitle{
        font-size:9px;
        letter-spacing:5px;
    }

    .form-side{
        padding:40px 22px;
    }

}

</style>

</head>


<body>

<div class="login-wrapper">


    <!-- =========================
         BRANDING
    ========================= -->

    <section class="brand-side">

        <div class="circle-one"></div>
        <div class="circle-two"></div>
        <div class="circle-three"></div>


        <div class="brand-content">

            <div class="brand-small">

                <div class="brand-small-line"></div>

                <div class="brand-small-text">
                    WELCOME TO
                </div>

            </div>


            <h1 class="brand-title">
                GLOWÉ
            </h1>


            <div class="brand-subtitle">
                BEAUTY
            </div>


            <p class="brand-description">
                Kelola penjualan kosmetik dengan lebih
                mudah, rapi, dan nyaman melalui sistem
                Glowé Beauty.
            </p>


            <div class="brand-bottom">
                BEAUTY · CARE · CONFIDENCE
            </div>

        </div>

    </section>



    <!-- =========================
         FORM LOGIN
    ========================= -->

    <section class="form-side">

        <div class="login-box">


            <div class="form-heading">

                <h2>
                    Selamat Datang
                </h2>

                <p>
                    Masuk untuk melanjutkan ke sistem.
                </p>

                <div class="heading-line"></div>

            </div>


            <form action="proses_login.php" method="POST">


                <div class="form-group">

                    <label class="form-label">
                        USERNAME
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


                <div class="form-group">

                    <label class="form-label">
                        PASSWORD
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

                    MASUK

                </button>


            </form>


            <div class="form-footer">
                Sistem Penjualan Kosmetik · Glowé Beauty
            </div>


        </div>

    </section>

</div>

</body>

</html>