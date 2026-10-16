<?php

include '../koneksi.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Produk tidak ditemukan.");
}

$id = (int) $_GET['id'];

$data = mysqli_query($koneksi, "
    SELECT * FROM produk
    WHERE id_produk = '$id'
");

if (!$data || mysqli_num_rows($data) == 0) {
    die("Produk tidak ditemukan.");
}

$row = mysqli_fetch_assoc($data);

if (isset($_POST['update'])) {

    $nama  = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $harga = (int) $_POST['harga'];
    $stok  = (int) $_POST['stok'];

    mysqli_query($koneksi, "
        UPDATE produk SET
            nama_produk = '$nama',
            harga = '$harga',
            stok = '$stok'
        WHERE id_produk = '$id'
    ");

    echo "
    <script>
        alert('Produk berhasil diperbarui!');
        window.location='produk.php';
    </script>
    ";

    exit;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Edit Produk - Glowé Beauty</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<style>

:root{
    --ivory:#F8F5ED;
    --green:#315C4D;
    --mustard:#B79A55;
    --line:#E3DED2;
    --text:#354044;
}

body{
    background:#FAF9F5;
    color:var(--text);
    font-family:Arial,sans-serif;
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
    color:var(--text);
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

/* CONTENT */

.page{
    max-width:800px;
    margin:auto;
    padding:50px 20px;
}

.title{
    color:var(--green);
    font:600 38px Georgia,serif;
}

.subtitle{
    color:#7A8384;
    font-size:14px;
}

.card{
    border:1px solid var(--line);
    border-radius:10px;
    box-shadow:0 3px 15px rgba(0,0,0,.04);
    background:white;
}

.form-label{
    color:var(--green);
    font-size:13px;
    font-weight:600;
}

.form-control{
    border:1px solid #D8D4CA;
    border-radius:6px;
    padding:11px 13px;
    font-size:14px;
}

.form-control:focus{
    border-color:#9DB5AA;
    box-shadow:0 0 0 .15rem rgba(49,92,77,.10);
}

.btn-simpan{
    background:var(--green);
    color:white;
    border:none;
    padding:10px 20px;
    border-radius:5px;
    font-size:13px;
    font-weight:600;
}

.btn-simpan:hover{
    background:#274C40;
    color:white;
}

.btn-batal{
    color:var(--green);
    background:white;
    border:1px solid #B8C8C0;
    padding:10px 20px;
    border-radius:5px;
    text-decoration:none;
    font-size:13px;
    font-weight:600;
}

.btn-batal:hover{
    background:var(--green);
    color:white;
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

}

</style>

</head>

<body>

<!-- NAVBAR ADMIN -->

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

    <a href="index.php"
       class="nav-link-custom">
        Home
    </a>

    <a href="pelanggan.php"
       class="nav-link-custom">
        Pelanggan
    </a>

    <a href="produk.php"
       class="nav-link-custom active">
        Produk
    </a>

    <a href="penjualan.php"
       class="nav-link-custom">
        Penjualan
    </a>

    <a href="transaksi.php"
       class="nav-link-custom">
        Transaksi
    </a>

    <a href="laporan.php"
       class="nav-link-custom">
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


<!-- CONTENT -->

<div class="page">

<div class="mb-4">

    <h1 class="title mb-1">
        Edit Produk
    </h1>

    <p class="subtitle mb-0">
        Perbarui informasi produk Glowé Beauty.
    </p>

</div>


<div class="card">

<div class="card-body p-4 p-md-5">

<form method="POST">

    <div class="mb-4">

        <label class="form-label">
            Nama Produk
        </label>

        <input
            type="text"
            name="nama_produk"
            class="form-control"
            value="<?= htmlspecialchars($row['nama_produk']); ?>"
            required
        >

    </div>


    <div class="mb-4">

        <label class="form-label">
            Harga
        </label>

        <input
            type="number"
            name="harga"
            class="form-control"
            value="<?= $row['harga']; ?>"
            min="0"
            required
        >

    </div>


    <div class="mb-4">

        <label class="form-label">
            Stok
        </label>

        <input
            type="number"
            name="stok"
            class="form-control"
            value="<?= $row['stok']; ?>"
            min="0"
            required
        >

    </div>


    <div class="d-flex gap-2">

        <button
            type="submit"
            name="update"
            class="btn-simpan">
            Simpan Perubahan
        </button>

        <a
            href="produk.php"
            class="btn-batal">
            Batal
        </a>

    </div>

</form>

</div>

</div>

</div>

</body>
</html>