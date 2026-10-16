<?php
include '../koneksi.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Data penjualan tidak ditemukan.");
}

$id = (int) $_GET['id'];

$q = mysqli_query($koneksi, "
    SELECT p.*, pl.nama_pelanggan, pl.alamat, pl.no_telepon,
           t.metode_pembayaran, t.status
    FROM penjualan p
    JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
    LEFT JOIN transaksi t ON p.id_penjualan = t.id_penjualan
    WHERE p.id_penjualan = $id
");

if (!$q || mysqli_num_rows($q) == 0) {
    die("Data penjualan tidak ditemukan.");
}

$data = mysqli_fetch_assoc($q);

$detail = mysqli_query($koneksi, "
    SELECT d.jumlah, d.harga, pr.nama_produk
    FROM detail_penjualan d
    JOIN produk pr ON d.id_produk = pr.id_produk
    WHERE d.id_penjualan = $id
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Detail Penjualan - Glowé Beauty</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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
}

.label{
    color:#8A918F;
    font-size:10px;
    font-weight:bold;
    text-transform:uppercase;
    letter-spacing:1px;
}

.value{
    font-size:14px;
    font-weight:600;
}

.table thead th{
    background:#F5F2EA;
    color:var(--green);
    font-size:12px;
    border-bottom:1px solid var(--line);
}

.table td{
    padding:14px;
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

.badge-sukses{
    background:#DCEBE3;
    color:var(--green);
    padding:5px 10px;
    border-radius:20px;
    font-size:11px;
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

<nav class="navbar">
<div class="container-fluid px-lg-5">
<div class="navbar-inner">

<a href="index.php" class="logo">
    <div class="logo-main">GLOWÉ</div>
    <div class="logo-bottom">
        <div class="logo-line"></div>
        <div class="logo-sub">BEAUTY</div>
    </div>
</a>

<div class="nav-menu">
    <a href="index.php" class="nav-link-custom">Home</a>
    <a href="pelanggan.php" class="nav-link-custom">Pelanggan</a>
    <a href="produk.php" class="nav-link-custom">Produk</a>
    <a href="transaksi.php" class="nav-link-custom active">Transaksi</a>
    <a href="../logout.php" class="logout"
       onclick="return confirm('Yakin ingin logout?')">Logout</a>
</div>

</div>
</div>
</nav>

<div class="page">

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="title mb-1">Detail Penjualan</h1>
        <p class="subtitle mb-0">Rincian transaksi pelanggan.</p>
    </div>

    <a href="transaksi.php" class="btn-back">← Kembali</a>
</div>

<div class="card mb-4">
<div class="card-body p-4">

<h5 class="fw-bold mb-4" style="color:#315C4D;">
    Informasi Transaksi
</h5>

<div class="row g-4">

<div class="col-md-3">
    <div class="label">Nama Pelanggan</div>
    <div class="value">
        <?= htmlspecialchars($data['nama_pelanggan']); ?>
    </div>
</div>

<div class="col-md-3">
    <div class="label">No. Telepon</div>
    <div class="value">
        <?= htmlspecialchars($data['no_telepon']); ?>
    </div>
</div>

<div class="col-md-3">
    <div class="label">Tanggal</div>
    <div class="value">
        <?= date('d-m-Y', strtotime($data['tanggal_penjualan'])); ?>
    </div>
</div>

<div class="col-md-3">
    <div class="label">Pembayaran</div>
    <div class="value">
        <?= htmlspecialchars($data['metode_pembayaran'] ?? 'Tunai'); ?>

        <?php if (($data['status'] ?? '') == 'sukses'): ?>
            <span class="badge-sukses ms-1">Sukses</span>
        <?php endif; ?>
    </div>
</div>

<div class="col-12">
    <div class="label">Alamat</div>
    <div class="value">
        <?= htmlspecialchars($data['alamat']); ?>
    </div>
</div>

</div>
</div>
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
    <th class="text-center">Jumlah</th>
    <th class="text-end">Harga</th>
    <th class="text-end">Subtotal</th>
</tr>
</thead>

<tbody>

<?php
$no = 1;

while ($row = mysqli_fetch_assoc($detail)):
    $subtotal = $row['jumlah'] * $row['harga'];
?>

<tr>
    <td><?= $no++; ?></td>

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
        Total&nbsp;&nbsp; Rp <?= number_format($data['total_harga'],0,',','.'); ?>
    </div>
</div>

</div>
</div>

</div>

</body>
</html>