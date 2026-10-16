<?php
include '../koneksi.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Data penjualan tidak ditemukan.");
}

$id_penjualan = (int) $_GET['id'];

$data = mysqli_query($koneksi, "
    SELECT detail_penjualan.*, produk.nama_produk
    FROM detail_penjualan
    JOIN produk ON detail_penjualan.id_produk = produk.id_produk
    WHERE detail_penjualan.id_penjualan = '$id_penjualan'
");

if (!$data || mysqli_num_rows($data) == 0) {
    die("Detail penjualan tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Detail Penjualan - Glowé Beauty</title>

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
    max-width:1150px;
    margin:auto;
    padding:45px 20px;
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

.table thead th{
    background:#F5F2EA;
    color:var(--green);
    font-size:12px;
    border-bottom:1px solid var(--line);
    padding:14px;
}

.table td{
    padding:14px;
    font-size:13px;
}

.total{
    background:#F5F2EA;
    border-radius:7px;
    padding:15px 20px;
    color:var(--green);
    font:600 22px Georgia,serif;
}

.btn-back{
    color:var(--green);
    border:1px solid #B8C8C0;
    background:white;
    padding:9px 16px;
    border-radius:5px;
    text-decoration:none;
    font-size:13px;
}

.btn-back:hover{
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
       class="nav-link-custom">
        Produk
    </a>

    <a href="penjualan.php"
       class="nav-link-custom active">
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

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h1 class="title mb-1">
            Detail Penjualan
        </h1>

        <p class="subtitle mb-0">
            Rincian produk yang dibeli pelanggan.
        </p>

    </div>

    <a href="penjualan.php"
       class="btn-back">
        ← Kembali
    </a>

</div>


<div class="card">

<div class="card-body p-4">

<h5 class="fw-bold mb-4" style="color:#315C4D;">
    Produk yang Dibeli
</h5>

<div class="table-responsive">

<table class="table align-middle">

<thead>

<tr>

    <th>No</th>

    <th>Produk</th>

    <th class="text-center">
        Jumlah
    </th>

    <th class="text-end">
        Harga
    </th>

    <th class="text-end">
        Subtotal
    </th>

</tr>

</thead>

<tbody>

<?php

$no = 1;
$total = 0;

while ($row = mysqli_fetch_assoc($data)):

    $subtotal = $row['jumlah'] * $row['harga'];

    $total += $subtotal;

?>

<tr>

    <td>
        <?= $no++; ?>
    </td>

    <td class="fw-semibold">
        <?= htmlspecialchars($row['nama_produk']); ?>
    </td>

    <td class="text-center">
        <?= $row['jumlah']; ?>
    </td>

    <td class="text-end">
        Rp <?= number_format($row['harga'],0,',','.'); ?>
    </td>

    <td class="text-end fw-semibold">
        Rp <?= number_format($subtotal,0,',','.'); ?>
    </td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>


<div class="d-flex justify-content-end mt-3">

    <div class="total">

        Total&nbsp;&nbsp;
        Rp <?= number_format($total,0,',','.'); ?>

    </div>

</div>

</div>

</div>

</div>

</body>

</html>