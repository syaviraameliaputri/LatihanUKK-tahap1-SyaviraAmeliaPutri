<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['tipe'] != 'admin') {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Glowé Beauty — Admin</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
:root{
    --green:#006038;
    --green-dark:#00492b;
    --mustard:#808847;
    --outerspace:#2B4C59;
    --ivory:#F9F7F2;
    --peach:#EFD8C9;
    --text:#263336;
    --muted:#788083;
    --line:#DDDCD5;
}

*{box-sizing:border-box}

body{
    margin:0;
    background:var(--ivory);
    color:var(--text);
    font-family:"Segoe UI",Arial,sans-serif;
}

/* NAVBAR */
.navbar{
    background:var(--ivory);
    border-bottom:1px solid var(--line);
}

.navbar-inner{
    min-height:88px;
    display:flex;
    align-items:center;
    width:100%;
}

.logo{
    text-decoration:none;
    display:flex;
    flex-direction:column;
    line-height:1;
    min-width:185px;
    flex-shrink:0;
}

.logo-main{
    color:var(--green);
    font:600 31px Georgia,serif;
    letter-spacing:2.8px;
}

.logo-bottom{
    display:flex;
    align-items:center;
    margin-top:8px;
}

.logo-line{
    width:30px;
    height:1px;
    background:var(--mustard);
    margin-right:9px;
}

.logo-sub{
    color:var(--outerspace);
    font-size:9px;
    font-weight:700;
    letter-spacing:3.5px;
}

.nav-menu{
    flex:1;
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-left:55px;
}

.nav-link-custom{
    position:relative;
    color:#606A6D;
    text-decoration:none;
    font-size:13px;
    font-weight:500;
    padding:34px 10px 31px;
    white-space:nowrap;
}

.nav-link-custom:hover,
.nav-link-custom.active{
    color:var(--green);
}

.nav-link-custom.active{
    font-weight:700;
}

.nav-link-custom.active:after{
    content:"";
    position:absolute;
    left:10px;
    right:10px;
    bottom:20px;
    height:2px;
    background:var(--green);
}

.logout{
    color:var(--green);
    border:1px solid #B8C8C0;
    border-radius:5px;
    padding:10px 20px;
    text-decoration:none;
    font-size:12px;
    font-weight:700;
    margin-left:10px;
}

.logout:hover{
    color:white;
    background:var(--green);
}

/* HERO */
.hero{
    padding:95px 0 85px;
    border-bottom:1px solid var(--line);
}

.hero-eyebrow{
    color:var(--mustard);
    font-size:11px;
    font-weight:700;
    letter-spacing:3px;
    text-transform:uppercase;
    margin-bottom:22px;
}

.hero-title{
    color:var(--outerspace);
    font-size:61px;
    line-height:1.02;
    font-weight:600;
    letter-spacing:-3px;
    margin:0;
}

.hero-title span{
    color:var(--green);
}

.hero-description{
    color:var(--muted);
    font-size:15px;
    line-height:1.8;
    max-width:500px;
    margin-top:28px;
}

.username{
    color:var(--outerspace);
    font-weight:600;
}

.hero-actions{
    margin-top:32px;
}

.btn-green{
    background:var(--green);
    color:white;
    border:1px solid var(--green);
    border-radius:5px;
    padding:11px 20px;
    font-size:13px;
    font-weight:600;
    text-decoration:none;
}

.btn-green:hover{
    background:var(--green-dark);
    color:white;
}

.btn-text{
    color:var(--outerspace);
    text-decoration:none;
    font-size:13px;
    font-weight:600;
    margin-left:20px;
    border-bottom:1px solid var(--outerspace);
    padding-bottom:3px;
}

.btn-text:hover{
    color:var(--green);
    border-color:var(--green);
}

/* BRAND PANEL */
.brand-panel{
    background:var(--peach);
    min-height:370px;
    display:flex;
    align-items:center;
    justify-content:center;
}

.brand-panel-inner{
    text-align:center;
}

.brand-monogram{
    color:var(--green);
    font-family:Georgia,serif;
    font-size:105px;
    line-height:1;
    font-weight:400;
}

.brand-panel-line{
    width:45px;
    height:1px;
    background:var(--mustard);
    margin:20px auto;
}

.brand-panel-name{
    color:var(--outerspace);
    font-size:14px;
    font-weight:700;
    letter-spacing:3px;
    text-transform:uppercase;
}

.brand-panel-text{
    color:#697276;
    font-size:12px;
    margin-top:8px;
}

/* MANAGEMENT */
.management{
    padding:75px 0;
}

.section-top{
    display:flex;
    align-items:end;
    justify-content:space-between;
    padding-bottom:22px;
    border-bottom:1px solid var(--line);
}

.section-label{
    color:var(--mustard);
    font-size:10px;
    font-weight:700;
    letter-spacing:2.5px;
    text-transform:uppercase;
}

.section-title{
    color:var(--outerspace);
    font-size:30px;
    font-weight:600;
    margin:7px 0 0;
    letter-spacing:-1px;
}

.section-note{
    color:var(--muted);
    font-size:12px;
}

/* MENU ITEM */
.menu-item{
    padding:30px 5px 30px 0;
    border-bottom:1px solid var(--line);
    transition:.2s;
}

.menu-item:hover{
    padding-left:10px;
}

.menu-number{
    color:var(--mustard);
    font-size:11px;
    font-weight:700;
    letter-spacing:1px;
}

.menu-name{
    color:var(--outerspace);
    font-size:22px;
    font-weight:600;
    margin-top:7px;
}

.menu-description{
    color:var(--muted);
    font-size:13px;
    line-height:1.7;
    max-width:450px;
    margin:7px 0 0;
}

.menu-action{
    color:var(--green);
    text-decoration:none;
    font-size:13px;
    font-weight:700;
    white-space:nowrap;
}

.menu-action:hover{
    color:var(--green-dark);
}

.arrow{
    font-size:17px;
    margin-left:7px;
}

/* BANNER */
.beauty-banner{
    background:var(--peach);
    padding:27px 30px;
}

.beauty-banner-title{
    color:var(--outerspace);
    font-family:Georgia,serif;
    font-size:24px;
    line-height:1.35;
}

/* QUOTE */
.quote-section{
    background:var(--outerspace);
    padding:45px 0;
}

.quote{
    color:white;
    font-family:Georgia,serif;
    font-size:27px;
    line-height:1.4;
    margin:0;
}

.quote-small{
    color:#C9D2D4;
    font-size:11px;
    letter-spacing:2px;
    text-transform:uppercase;
    margin-top:15px;
}

/* FOOTER */
footer{
    background:#213A44;
    padding:25px 0;
}

.footer-brand{
    color:var(--peach);
    font-size:14px;
    font-weight:700;
}

.footer-text{
    color:#AAB9BD;
    font-size:11px;
    margin-top:4px;
}

/* RESPONSIVE */
@media(max-width:1100px){

    .logo{
        min-width:150px;
    }

    .nav-menu{
        margin-left:25px;
    }

    .nav-link-custom{
        padding-left:7px;
        padding-right:7px;
    }

    .nav-link-custom.active:after{
        left:7px;
        right:7px;
    }

    .logout{
        padding-left:14px;
        padding-right:14px;
    }
}

@media(max-width:991px){

    .navbar-inner{
        flex-wrap:wrap;
        padding:15px 0;
    }

    .nav-menu{
        width:100%;
        flex:none;
        margin:15px 0 0;
        padding-top:10px;
        border-top:1px solid var(--line);
        flex-wrap:wrap;
        justify-content:flex-start;
        gap:3px;
    }

    .nav-link-custom{
        padding:9px 11px;
    }

    .nav-link-custom.active:after{
        display:none;
    }

    .logout{
        margin-left:5px;
    }

    .hero{
        padding:65px 0;
    }

    .hero-title{
        font-size:48px;
    }

    .brand-panel{
        margin-top:40px;
    }
}

@media(max-width:576px){

    .hero-title{
        font-size:39px;
        letter-spacing:-2px;
    }

    .section-top{
        display:block;
    }

    .section-note{
        margin-top:10px;
    }

    .menu-action{
        display:inline-block;
        margin-top:15px;
    }
}
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="container-fluid px-lg-5">

        <div class="navbar-inner">

            <a href="index.php" class="logo">

                <div class="logo-main">
                    GLOWÉ
                </div>

                <div class="logo-bottom">
                    <div class="logo-line"></div>
                    <div class="logo-sub">
                        BEAUTY
                    </div>
                </div>

            </a>


            <div class="nav-menu">

                <a href="index.php" class="nav-link-custom active">
                    Home
                </a>

                <a href="pelanggan.php" class="nav-link-custom">
                    Pelanggan
                </a>

                <a href="produk.php" class="nav-link-custom">
                    Produk
                </a>

                <a href="penjualan.php" class="nav-link-custom">
                    Penjualan
                </a>

                <a href="transaksi.php" class="nav-link-custom">
                    Transaksi
                </a>

                <a href="laporan.php" class="nav-link-custom">
                    Laporan
                </a>

                <a href="../logout.php"
                   class="logout"
                   onclick="return confirm('Yakin ingin logout?')">
                    Logout
                </a>

            </div>

        </div>

    </div>
</nav>


<!-- HERO -->
<section class="hero">

    <div class="container">

        <div class="row align-items-center">

            <div class="col-lg-7">

                <div class="hero-eyebrow">
                    Glowé Beauty / Administration
                </div>

                <h1 class="hero-title">
                    Your beauty.<br>
                    <span>Your business.</span>
                </h1>

                <p class="hero-description">

                    Selamat datang,

                    <span class="username">
                        <?= htmlspecialchars($_SESSION['username']); ?>
                    </span>.

                    Kelola produk, pelanggan,
                    penjualan, dan transaksi Glowé
                    dalam satu ruang kerja.

                </p>


                <div class="hero-actions">

                    <a href="pelanggan.php" class="btn-green">
                        Mulai Kelola
                    </a>

                    <a href="produk.php" class="btn-text">
                        Lihat Produk
                    </a>

                </div>

            </div>


            <div class="col-lg-5">

                <div class="brand-panel">

                    <div class="brand-panel-inner">

                        <div class="brand-monogram">
                            G
                        </div>

                        <div class="brand-panel-line"></div>

                        <div class="brand-panel-name">
                            Glowé Beauty
                        </div>

                        <div class="brand-panel-text">
                            Beauty Store Management
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- MANAGEMENT -->
<section class="management">

    <div class="container">

        <div class="section-top">

            <div>

                <div class="section-label">
                    Management
                </div>

                <h2 class="section-title">
                    Manage your store
                </h2>

            </div>

            <div class="section-note">
                Glowé Beauty Administration
            </div>

        </div>


        <!-- PELANGGAN -->
        <div class="menu-item">

            <div class="row align-items-center">

                <div class="col-md-1">

                    <div class="menu-number">
                        01
                    </div>

                </div>

                <div class="col-md-7">

                    <div class="menu-name">
                        Pelanggan
                    </div>

                    <p class="menu-description">
                        Kelola data pelanggan dan
                        riwayat pembelian.
                    </p>

                </div>

                <div class="col-md-4 text-md-end">

                    <a href="pelanggan.php" class="menu-action">

                        Kelola pelanggan

                        <span class="arrow">
                            →
                        </span>

                    </a>

                </div>

            </div>

        </div>


        <!-- PRODUK -->
        <div class="menu-item">

            <div class="row align-items-center">

                <div class="col-md-1">

                    <div class="menu-number">
                        02
                    </div>

                </div>

                <div class="col-md-7">

                    <div class="menu-name">
                        Produk
                    </div>

                    <p class="menu-description">
                        Kelola produk makeup,
                        harga, dan stok.
                    </p>

                </div>

                <div class="col-md-4 text-md-end">

                    <a href="produk.php" class="menu-action">

                        Kelola produk

                        <span class="arrow">
                            →
                        </span>

                    </a>

                </div>

            </div>

        </div>


        <!-- PENJUALAN -->
        <div class="menu-item">

            <div class="row align-items-center">

                <div class="col-md-1">

                    <div class="menu-number">
                        03
                    </div>

                </div>

                <div class="col-md-7">

                    <div class="menu-name">
                        Penjualan
                    </div>

                    <p class="menu-description">
                        Lihat penjualan dan
                        perkembangan omset.
                    </p>

                </div>

                <div class="col-md-4 text-md-end">

                    <a href="penjualan.php" class="menu-action">

                        Lihat penjualan

                        <span class="arrow">
                            →
                        </span>

                    </a>

                </div>

            </div>

        </div>


        <!-- BANNER -->
        <div class="row mt-5">

            <div class="col-lg-8">

                <div class="beauty-banner">

                    <div class="section-label">
                        Glowé Beauty
                    </div>

                    <div class="beauty-banner-title mt-2">

                        Simple systems.<br>
                        Beautiful business.

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- QUOTE -->
<section class="quote-section">

    <div class="container">

        <p class="quote">
            "Beauty is not just what you sell,
            but how you make people feel."
        </p>

        <div class="quote-small">
            Glowé Beauty
        </div>

    </div>

</section>


<!-- FOOTER -->
<footer>

    <div class="container text-center">

        <div class="footer-brand">
            Glowé Beauty
        </div>

        <div class="footer-text">
            Beauty Store Management System
        </div>

    </div>

</footer>

</body>
</html>