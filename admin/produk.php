<?php
include '../koneksi.php';

/* SIMPAN PRODUK */
if (isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $harga = (int)$_POST['harga'];
    $stok = (int)$_POST['stok'];

    mysqli_query($koneksi, "INSERT INTO produk (nama_produk,harga,stok)
        VALUES ('$nama', $harga, $stok)")
        or die("Gagal menambahkan produk: ".mysqli_error($koneksi));

    echo "<script>alert('Produk berhasil ditambahkan!');location='produk.php';</script>";
    exit;
}

/* CARI PRODUK */
$keyword = isset($_GET['cari'])
    ? mysqli_real_escape_string($koneksi, $_GET['cari'])
    : '';

$data = mysqli_query($koneksi, "SELECT * FROM produk
    WHERE nama_produk LIKE '%$keyword%'
    ORDER BY id_produk DESC");

$total_produk = mysqli_num_rows($data);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Produk — Glowé Beauty</title>

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

*{
    box-sizing:border-box
}

body{
    margin:0;
    background:var(--ivory);
    color:var(--text);
    font-family:"Segoe UI",Arial,sans-serif;
}

/* HILANGKAN SPINNER TANDA PANAH KANAN DI INPUT NUMBER */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type=number] {
    -moz-appearance: textfield;
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

/* CONTENT */
.page{
    padding:65px 0 80px;
}

.eyebrow,
.section-label{
    color:var(--mustard);
    font-size:10px;
    font-weight:700;
    letter-spacing:2.5px;
    text-transform:uppercase;
}

.page-title{
    color:var(--outerspace);
    font:600 42px Georgia,serif;
    letter-spacing:-1px;
    margin:8px 0;
}

.page-desc{
    color:var(--muted);
    font-size:14px;
}

.card-custom{
    background:white;
    border:1px solid var(--line);
    border-radius:0;
}

.card-title{
    color:var(--outerspace);
    font-size:22px;
    font-weight:600;
}

.form-label{
    color:var(--outerspace);
    font-size:13px;
    font-weight:600;
}

.form-control{
    border-color:var(--line);
    border-radius:4px;
}

.form-control:focus{
    border-color:var(--green);
    box-shadow:0 0 0 2px rgba(0,96,56,.08);
}

.btn-green{
    background:var(--green);
    border:1px solid var(--green);
    color:white;
    border-radius:4px;
    font-size:13px;
    font-weight:600;
}

.btn-green:hover{
    background:var(--green-dark);
    color:white;
}

.btn-outline-green{
    color:var(--green);
    border:1px solid #B8C8C0;
    background:white;
}

.btn-outline-green:hover{
    background:var(--green);
    color:white;
}

/* TOTAL */
.stat-box{
    background:var(--peach);
    padding:25px;
}

.stat-number{
    color:var(--outerspace);
    font:600 32px Georgia,serif;
}

/* TABLE */
.table{
    --bs-table-bg:transparent;
}

.table thead th{
    color:var(--mustard);
    font-size:10px;
    text-transform:uppercase;
    letter-spacing:1px;
    border-bottom:1px solid var(--line);
    white-space:nowrap;
}

.table tbody td{
    color:var(--text);
    font-size:13px;
    border-color:var(--line);
}

.stock{
    color:var(--green);
    font-weight:700;
}

.stock-empty{
    color:#8B5E3C;
    font-weight:700;
}

.action-edit{
    color:var(--green);
    border:1px solid #B8C8C0;
    background:white;
}

.action-edit:hover{
    color:white;
    background:var(--green);
}

.action-delete{
    border-radius:4px;
}

footer{
    background:var(--outerspace);
    color:#AAB9BD;
    padding:25px 0;
}

.footer-brand{
    color:var(--peach);
    font-weight:700;
}

.footer-text{
    font-size:11px;
    margin-top:4px;
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

@media(max-width:576px){

    .page-title{
        font-size:34px;
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
    <div class="logo-main">GLOWÉ</div>

    <div class="logo-bottom">
        <div class="logo-line"></div>
        <div class="logo-sub">BEAUTY</div>
    </div>
</a>

<div class="nav-menu">

    <a href="index.php" class="nav-link-custom">
        Home
    </a>

    <a href="pelanggan.php" class="nav-link-custom">
        Pelanggan
    </a>

    <a href="produk.php" class="nav-link-custom active">
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

<!-- CONTENT -->
<main class="page">
<div class="container">

<div class="mb-5">

    <div class="eyebrow">
        Glowé Beauty / Product
    </div>

    <h1 class="page-title">
        Produk
    </h1>

    <p class="page-desc mb-0">
        Kelola produk kosmetik, harga, dan stok Glowé Beauty.
    </p>

</div>

<!-- TOTAL PRODUK -->
<div class="stat-box mb-4">

    <div class="section-label">
        Total Produk
    </div>

    <div class="stat-number">
        <?= $total_produk; ?>
    </div>

</div>

<!-- TAMBAH PRODUK -->
<div class="card-custom p-4 p-lg-5 mb-5">

<div class="mb-4">

    <div class="section-label">
        New Product
    </div>

    <div class="card-title mt-1">
        Tambah Produk
    </div>

    <p class="text-secondary small mb-0">
        Tambahkan produk baru ke dalam katalog Glowé Beauty.
    </p>

</div>

<form method="POST">

<div class="row g-3">

    <div class="col-md-4">

        <label class="form-label">
            Nama Produk
        </label>

        <input type="text"
               name="nama_produk"
               class="form-control"
               placeholder="Nama produk"
               required>

    </div>

    <div class="col-md-3">

        <label class="form-label">
            Harga
        </label>

        <input type="number"
               name="harga"
               class="form-control"
               placeholder="Harga"
               min="0"
               required>

    </div>

    <div class="col-md-3">

        <label class="form-label">
            Stok
        </label>

        <input type="number"
               name="stok"
               class="form-control"
               placeholder="Stok"
               min="0"
               required>

    </div>

    <div class="col-md-2 d-flex align-items-end">

        <button type="submit"
                name="simpan"
                class="btn btn-green w-100 py-2">
            Tambah
        </button>

    </div>

</div>

</form>

</div>

<!-- DAFTAR PRODUK -->
<div class="card-custom p-4 p-lg-5">

<div class="mb-4">

    <div class="section-label">
        Product Collection
    </div>

    <div class="card-title mt-1">
        Daftar Produk
    </div>

    <p class="text-secondary small mb-0">
        Semua produk, harga, dan stok yang tersedia.
    </p>

</div>

<!-- SEARCH -->
<form method="GET" class="mb-4">

<div class="input-group">

    <input type="text"
           name="cari"
           class="form-control"
           placeholder="Cari nama produk..."
           value="<?= htmlspecialchars($keyword); ?>">

    <button type="submit"
            class="btn btn-green">
        Cari
    </button>

    <?php if($keyword!=""): ?>

        <a href="produk.php"
           class="btn btn-outline-green">
            Reset
        </a>

    <?php endif; ?>

</div>

</form>

<!-- TABLE -->
<div class="table-responsive">

<table class="table table-hover align-middle">

<thead>

<tr>
    <th>No</th>
    <th>Produk</th>
    <th>Harga</th>
    <th>Stok</th>
    <th>Aksi</th>
</tr>

</thead>

<tbody>

<?php

$no = 1;

if(mysqli_num_rows($data) > 0):

while($row = mysqli_fetch_assoc($data)):

?>

<tr>

    <td>
        <?= $no++; ?>
    </td>

    <td class="fw-semibold">
        <?= htmlspecialchars($row['nama_produk']); ?>
    </td>

    <td>
        Rp <?= number_format($row['harga'],0,',','.'); ?>
    </td>

    <td>

        <?php if($row['stok'] > 0): ?>

            <span class="stock">
                <?= $row['stok']; ?>
            </span>

        <?php else: ?>

            <span class="stock-empty">
                Habis
            </span>

        <?php endif; ?>

    </td>

    <td>

        <div class="d-flex gap-1">

            <a href="edit_produk.php?id=<?= $row['id_produk']; ?>"
               class="btn btn-sm action-edit">
                Edit
            </a>

            <a href="hapus_produk.php?id=<?= $row['id_produk']; ?>"
               class="btn btn-sm btn-outline-danger action-delete"
               onclick="return confirm('Yakin ingin menghapus produk ini?')">
                Hapus
            </a>

        </div>

    </td>

</tr>

<?php

endwhile;

else:

?>

<tr>

    <td colspan="5"
        class="text-center text-muted py-5">

        <?= $keyword
            ? 'Produk tidak ditemukan.'
            : 'Belum ada produk.'; ?>

    </td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>

</div>
</main>

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