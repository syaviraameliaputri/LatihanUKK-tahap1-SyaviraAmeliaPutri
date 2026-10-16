<?php
include '../koneksi.php';

$mode = $_GET['mode'] ?? 'bulan_ini';
$periode = $_GET['periode'] ?? date('Y-m');

if (!preg_match('/^\d{4}-\d{2}$/', $periode)) {
    $periode = date('Y-m');
}

$tahun = (int) substr($periode, 0, 4);
$bulan = (int) substr($periode, 5, 2);

$where_bulan = '';

if ($mode == 'bulan_ini') {

    $periode = date('Y-m');

    $tanggal_awal = $periode . '-01';
    $tanggal_akhir = date('Y-m-t', strtotime($tanggal_awal));

    $where_bulan = "
        AND penjualan.tanggal_penjualan
        BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
    ";

} elseif ($mode == 'bulan') {

    $tanggal_awal = $periode . '-01';
    $tanggal_akhir = date('Y-m-t', strtotime($tanggal_awal));

    $where_bulan = "
        AND penjualan.tanggal_penjualan
        BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
    ";
}

$keyword = isset($_GET['cari'])
    ? trim($_GET['cari'])
    : '';

$where_cari = '';

if ($keyword != '') {

    $keyword = mysqli_real_escape_string(
        $koneksi,
        $keyword
    );

    $where_cari = "
        AND (
            pelanggan.nama_pelanggan LIKE '%$keyword%'
            OR penjualan.tanggal_penjualan LIKE '%$keyword%'
        )
    ";
}

$data = mysqli_query($koneksi, "

    SELECT
        penjualan.id_penjualan,
        penjualan.id_pelanggan,
        penjualan.tanggal_penjualan,
        penjualan.total_harga,
        pelanggan.nama_pelanggan,
        COALESCE(SUM(detail_penjualan.jumlah), 0) AS jumlah

    FROM penjualan

    INNER JOIN pelanggan
        ON penjualan.id_pelanggan = pelanggan.id_pelanggan

    INNER JOIN transaksi
        ON penjualan.id_penjualan = transaksi.id_penjualan

    LEFT JOIN detail_penjualan
        ON penjualan.id_penjualan = detail_penjualan.id_penjualan

    WHERE transaksi.status = 'sukses'

    $where_bulan
    $where_cari

    GROUP BY
        penjualan.id_penjualan,
        penjualan.id_pelanggan,
        penjualan.tanggal_penjualan,
        penjualan.total_harga,
        pelanggan.nama_pelanggan

    ORDER BY penjualan.id_penjualan DESC

");

$total_penjualan = mysqli_num_rows($data);

$omset_query = mysqli_query($koneksi, "

    SELECT COALESCE(SUM(penjualan.total_harga), 0) AS omset

    FROM penjualan

    INNER JOIN transaksi
        ON penjualan.id_penjualan = transaksi.id_penjualan

    WHERE transaksi.status = 'sukses'

    $where_bulan

");

$omset_data = mysqli_fetch_assoc($omset_query);
$omset = $omset_data['omset'] ?? 0;

$nama_bulan = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

if ($mode == 'bulan_ini') {

    $judul_periode =
        'Bulan Ini - ' .
        $nama_bulan[(int) date('m')] .
        ' ' .
        date('Y');

} elseif ($mode == 'bulan') {

    $judul_periode =
        $nama_bulan[$bulan] .
        ' ' .
        $tahun;

} else {

    $judul_periode = 'Semua Omset';

}
?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>Penjualan - Glowé Beauty</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

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
    box-sizing:border-box;
}

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

/* FORM */

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

/* BUTTON */

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
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:0 15px;
    border-radius:4px;
}

.btn-outline-green:hover{
    background:var(--green);
    color:white;
}

/* FILTER */

.filter-btn{
    border:1px solid #B8C8C0;
    color:var(--green);
    background:white;
    border-radius:4px;
    font-size:13px;
}

.filter-btn:hover{
    background:var(--green);
    color:white;
}

.filter-btn.active{
    background:var(--green);
    color:white;
    border-color:var(--green);
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

.price{
    color:var(--green);
    font-weight:700;
}

.action-detail{
    color:var(--green);
    border:1px solid #B8C8C0;
    background:white;
    border-radius:4px;
    font-size:12px;
}

.action-detail:hover{
    color:white;
    background:var(--green);
}

/* FOOTER */

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

/* RESPONSIVE */

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

    <a href="index.php" class="nav-link-custom">
        Home
    </a>

    <a href="pelanggan.php" class="nav-link-custom">
        Pelanggan
    </a>

    <a href="produk.php" class="nav-link-custom">
        Produk
    </a>

    <a href="penjualan.php" class="nav-link-custom active">
        Penjualan
    </a>

    <a href="transaksi.php" class="nav-link-custom">
        Transaksi
    </a>

    <a href="laporan.php" class="nav-link-custom">
        Laporan
    </a>

    <a
        href="../logout.php"
        class="logout"
        onclick="return confirm('Yakin ingin logout?')"
    >
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
        Glowé Beauty / Sales
    </div>

    <h1 class="page-title">
        Penjualan
    </h1>

    <p class="page-desc mb-0">
        Pantau seluruh penjualan dan omset Glowé Beauty.
    </p>

</div>

<!-- FILTER -->

<div class="card-custom p-4 p-lg-5 mb-4">

    <div class="mb-4">

        <div class="section-label">
            Sales Filter
        </div>

        <div class="card-title mt-1">
            Filter Penjualan
        </div>

        <p class="text-secondary small mb-0">
            Pilih periode untuk melihat data penjualan.
        </p>

    </div>

    <div class="d-flex flex-wrap gap-2 mb-4">

        <a
            href="penjualan.php?mode=bulan_ini"
            class="btn filter-btn <?= $mode == 'bulan_ini' ? 'active' : ''; ?>"
        >
            Bulan Ini
        </a>

        <a
            href="penjualan.php?mode=semua"
            class="btn filter-btn <?= $mode == 'semua' ? 'active' : ''; ?>"
        >
            Semua
        </a>

    </div>

    <form method="GET" class="row g-3 align-items-end">

        <input
            type="hidden"
            name="mode"
            value="bulan"
        >

        <div class="col-md-8">

            <label class="form-label">
                Pilih Bulan
            </label>

            <input
                type="month"
                name="periode"
                class="form-control"
                value="<?= htmlspecialchars($periode); ?>"
                required
            >

        </div>

        <div class="col-md-4">

            <button
                type="submit"
                class="btn btn-green w-100 py-2"
            >
                Tampilkan
            </button>

        </div>

    </form>

</div>

<!-- STATISTIK -->

<div class="stat-box mb-4">

    <div class="section-label">
        Ringkasan
    </div>

    <div class="row mt-2">

        <div class="col-md-6 mb-3 mb-md-0">

            <div class="small text-secondary mb-1">
                Total Penjualan
            </div>

            <div class="stat-number">
                <?= $total_penjualan; ?>
            </div>

            <div class="small text-secondary">
                <?= htmlspecialchars($judul_periode); ?>
            </div>

        </div>

        <div class="col-md-6">

            <div class="small text-secondary mb-1">
                Total Omset
            </div>

            <div class="stat-number">
                Rp <?= number_format($omset, 0, ',', '.'); ?>
            </div>

            <div class="small text-secondary">
                <?= htmlspecialchars($judul_periode); ?>
            </div>

        </div>

    </div>

</div>

<!-- DATA PENJUALAN -->

<div class="card-custom p-4 p-lg-5">

    <div class="mb-4">

        <div class="section-label">
            Sales Collection
        </div>

        <div class="card-title mt-1">
            Daftar Penjualan
        </div>

        <p class="text-secondary small mb-0">
            Penjualan yang tampil hanya transaksi dengan status sukses.
        </p>

    </div>

    <!-- SEARCH -->

    <form method="GET" class="mb-4">

        <input
            type="hidden"
            name="mode"
            value="<?= htmlspecialchars($mode); ?>"
        >

        <?php if($mode == 'bulan'): ?>

            <input
                type="hidden"
                name="periode"
                value="<?= htmlspecialchars($periode); ?>"
            >

        <?php endif; ?>

        <div class="input-group">

            <input
                type="text"
                name="cari"
                class="form-control"
                placeholder="Cari nama pelanggan atau tanggal..."
                value="<?= htmlspecialchars($keyword); ?>"
            >

            <button
                type="submit"
                class="btn btn-green px-4"
            >
                Cari
            </button>

            <?php if($keyword != ''): ?>

                <a
                    href="penjualan.php?mode=<?= htmlspecialchars($mode); ?><?php
                        if($mode == 'bulan'){
                            echo '&periode=' . urlencode($periode);
                        }
                    ?>"
                    class="btn-outline-green"
                >
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
                    <th>Nama Pelanggan</th>
                    <th>Tanggal</th>
                    <th>Jumlah</th>
                    <th>Total Harga</th>
                    <th>Aksi</th>

                </tr>

            </thead>

            <tbody>

            <?php

            $no = 1;

            if(
                $data &&
                mysqli_num_rows($data) > 0
            ):

                while($row = mysqli_fetch_assoc($data)):

            ?>

                <tr>

                    <td>
                        <?= $no++; ?>
                    </td>

                    <td class="fw-semibold">
                        <?= htmlspecialchars($row['nama_pelanggan']); ?>
                    </td>

                    <td>
                        <?= date(
                            'd-m-Y',
                            strtotime($row['tanggal_penjualan'])
                        ); ?>
                    </td>

                    <td>
                        <?= $row['jumlah']; ?> item
                    </td>

                    <td class="price">
                        Rp <?= number_format(
                            $row['total_harga'],
                            0,
                            ',',
                            '.'
                        ); ?>
                    </td>

                    <td>

                        <a
                            href="detail_penjualan.php?id=<?= $row['id_penjualan']; ?>"
                            class="btn btn-sm action-detail"
                        >
                            Detail
                        </a>

                    </td>

                </tr>

            <?php

                endwhile;

            else:

            ?>

                <tr>

                    <td
                        colspan="6"
                        class="text-center text-muted py-5"
                    >

                        <?php if($keyword != ''): ?>

                            Penjualan tidak ditemukan.

                        <?php else: ?>

                            Belum ada data penjualan.

                        <?php endif; ?>

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