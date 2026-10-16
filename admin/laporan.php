<?php

include '../koneksi.php';


// =====================================================
// MODE FILTER
// =====================================================

$mode = isset($_GET['mode'])
    ? $_GET['mode']
    : 'bulan_ini';


// =====================================================
// TANGGAL
// =====================================================

$tanggal_awal = isset($_GET['tanggal_awal'])
    ? $_GET['tanggal_awal']
    : date('Y-m-01');

$tanggal_akhir = isset($_GET['tanggal_akhir'])
    ? $_GET['tanggal_akhir']
    : date('Y-m-t');


// =====================================================
// PENCARIAN
// =====================================================

$keyword = trim($_GET['cari'] ?? '');

$safe = mysqli_real_escape_string(
    $koneksi,
    $keyword
);


// =====================================================
// FILTER WHERE
// =====================================================

$where = "";

if ($mode == 'bulan_ini') {

    $tanggal_awal = date('Y-m-01');

    $tanggal_akhir = date('Y-m-t');

    $where .= "
        AND penjualan.tanggal_penjualan
        BETWEEN '$tanggal_awal'
        AND '$tanggal_akhir'
    ";

} elseif ($mode == 'tanggal') {

    if (
        $tanggal_awal != ''
        &&
        $tanggal_akhir != ''
    ) {

        $where .= "
            AND penjualan.tanggal_penjualan
            BETWEEN '$tanggal_awal'
            AND '$tanggal_akhir'
        ";

    }

} elseif ($mode == 'semua') {

    $where .= "";

}


// =====================================================
// PENCARIAN
// =====================================================

if ($keyword != '') {

    $where .= "
        AND pelanggan.nama_pelanggan
        LIKE '%$safe%'
    ";

}


// =====================================================
// DATA LAPORAN
// =====================================================

$data = mysqli_query($koneksi, "

    SELECT

        penjualan.id_penjualan,

        pelanggan.nama_pelanggan,

        penjualan.tanggal_penjualan,

        penjualan.total_harga,

        COALESCE(

            (
                SELECT
                    SUM(detail_penjualan.jumlah)

                FROM detail_penjualan

                WHERE detail_penjualan.id_penjualan =
                      penjualan.id_penjualan
            ),

            0

        ) AS jumlah

    FROM penjualan

    INNER JOIN pelanggan

        ON penjualan.id_pelanggan =
           pelanggan.id_pelanggan

    WHERE EXISTS (

        SELECT 1

        FROM transaksi

        WHERE transaksi.id_penjualan =
              penjualan.id_penjualan

        AND transaksi.status = 'sukses'

    )

    $where

    ORDER BY
        penjualan.tanggal_penjualan DESC,
        penjualan.id_penjualan DESC

");


// =====================================================
// HITUNG RINGKASAN
// =====================================================

$total_transaksi = 0;

$total_barang = 0;

$total_omset = 0;

$laporan = [];


if ($data) {

    while ($row = mysqli_fetch_assoc($data)) {

        $laporan[] = $row;

        $total_transaksi++;

        $total_barang += (int) $row['jumlah'];

        $total_omset += (float) $row['total_harga'];

    }

}

?>


<!DOCTYPE html>

<html lang="id">


<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Laporan - Glowé Beauty</title>


<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


<style>


/* =====================================================
   WARNA
===================================================== */

:root{

    --ivory:#F9F7F2;

    --green:#006038;

    --darkgreen:#00492b;

    --mustard:#808847;

    --outer:#2B4C59;

    --outerspace:#2B4C59;

    --peach:#EFD8C9;

    --text:#263336;

    --muted:#788083;

    --line:#DDDCD5;

}


/* =====================================================
   DASAR
===================================================== */

*{

    box-sizing:border-box;

}


body{

    margin:0;

    background:var(--ivory);

    color:var(--text);

    font-family:Arial,sans-serif;

}


/* =====================================================
   NAVBAR
===================================================== */

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


/* =====================================================
   JUDUL
===================================================== */

.section-label{

    color:var(--mustard);

    font-size:10px;

    font-weight:700;

    letter-spacing:2.5px;

    text-transform:uppercase;

    margin-bottom:8px;

}


.page-title{

    color:var(--outer);

    font:600 42px Georgia,serif;

    letter-spacing:-1px;

    margin:0 0 8px;

}


.page-desc{

    color:var(--muted);

    font-size:14px;

}


/* =====================================================
   CARD
===================================================== */

.card-editorial{

    background:#fff;

    border:1px solid var(--line);

    border-radius:0;

}


/* =====================================================
   FORM
===================================================== */

.form-control{

    border:1px solid var(--line);

    border-radius:0;

    padding:11px 13px;

    font-size:13px;

}


.form-control:focus{

    border-color:var(--green);

    box-shadow:
        0 0 0 2px rgba(0,96,56,.08);

}


.form-label{

    color:var(--text);

    font-size:12px;

    font-weight:600;

    margin-bottom:7px;

}


/* =====================================================
   BUTTON
===================================================== */

.btn-green{

    display:inline-block;

    background:var(--green);

    color:#fff;

    border:1px solid var(--green);

    border-radius:0;

    padding:11px 22px;

    font-size:12px;

}


.btn-green:hover{

    background:var(--darkgreen);

    color:#fff;

}


.btn-outline-green{

    display:inline-block;

    color:var(--green);

    background:#fff;

    border:1px solid var(--green);

    border-radius:0;

    padding:10px 18px;

    font-size:12px;

    text-decoration:none;

}


.btn-outline-green:hover{

    background:var(--green);

    color:#fff;

}


.filter-btn{

    display:inline-block;

    padding:10px 18px;

    font-size:12px;

    text-decoration:none;

    border:1px solid var(--green);

    color:var(--green);

    background:#fff;

}


.filter-btn:hover{

    background:var(--green);

    color:#fff;

}


.filter-btn.active{

    background:var(--green);

    color:#fff;

}


/* =====================================================
   SUMMARY
===================================================== */

.summary-card{

    background:#fff;

    border:1px solid var(--line);

    padding:25px;

    height:100%;

}


.summary-label{

    color:var(--muted);

    font-size:11px;

    text-transform:uppercase;

    letter-spacing:1.5px;

    margin-bottom:8px;

}


.summary-number{

    color:var(--outer);

    font-family:Georgia,serif;

    font-size:28px;

    font-weight:bold;

}


.summary-omset{

    color:var(--green);

    font-family:Georgia,serif;

    font-size:25px;

    font-weight:bold;

}


/* =====================================================
   TABLE
===================================================== */

.table{

    margin-bottom:0;

}


.table thead th{

    color:var(--mustard);

    background:transparent;

    border-bottom:1px solid var(--line);

    font-size:10px;

    text-transform:uppercase;

    letter-spacing:1.5px;

    padding:15px 10px;

    white-space:nowrap;

}


.table tbody td{

    border-bottom:1px solid #eeeDE8;

    padding:16px 10px;

    font-size:13px;

    color:var(--text);

}


.table tbody tr:last-child td{

    border-bottom:0;

}


.table tfoot th{

    border-top:1px solid var(--line);

    color:var(--outer);

    padding:16px 10px;

    font-size:12px;

}


.price{

    color:var(--green);

    font-weight:600;

}


.customer-name{

    font-weight:600;

}


.empty{

    color:var(--muted);

    padding:45px!important;

}


/* =====================================================
   CETAK
===================================================== */

.btn-print{

    color:var(--green);

    background:#fff;

    border:1px solid var(--green);

    border-radius:0;

    padding:9px 16px;

    font-size:11px;

}


.btn-print:hover{

    background:var(--green);

    color:#fff;

}


/* =====================================================
   FOOTER
===================================================== */

footer{

    background:var(--outer);

    color:#fff;

    margin-top:70px;

}


footer .brand{

    color:var(--peach);

    font-family:Georgia,serif;

    font-size:21px;

}


footer p{

    color:#d4dcdd;

    font-size:12px;

}


/* =====================================================
   PRINT
===================================================== */

@media print{

    body{

        background:#fff;

    }


    .navbar,
    footer,
    .filter-area,
    .no-print{

        display:none!important;

    }


    .container{

        max-width:100%!important;

    }


    .card-editorial,
    .summary-card{

        border:0;

    }


    .page-title{

        color:#000;

    }

}


/* =====================================================
   RESPONSIVE
===================================================== */

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


<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="navbar">


<div class="container-fluid px-lg-5">


<div class="navbar-inner">


<a
    href="index.php"
    class="logo"
>

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


    <a
        href="index.php"
        class="nav-link-custom"
    >
        Home
    </a>


    <a
        href="pelanggan.php"
        class="nav-link-custom"
    >
        Pelanggan
    </a>


    <a
        href="produk.php"
        class="nav-link-custom"
    >
        Produk
    </a>


    <a
        href="penjualan.php"
        class="nav-link-custom"
    >
        Penjualan
    </a>


    <a
        href="transaksi.php"
        class="nav-link-custom"
    >
        Transaksi
    </a>


    <a
        href="laporan.php"
        class="nav-link-custom active"
    >
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


<!-- =====================================================
     CONTENT
===================================================== -->

<div class="container py-5">


<!-- =====================================================
     JUDUL
===================================================== -->

<div class="section-label">
    Sales Report
</div>


<h1 class="page-title">
    Laporan Penjualan
</h1>


<p class="page-desc mb-4">
    Rekap penjualan dan omset Glowé Beauty.
</p>


<!-- =====================================================
     FILTER
===================================================== -->

<div class="card-editorial p-4 mb-4 filter-area">


    <div class="section-label">
        Filter
    </div>


    <h5
        style="
            font-family:Georgia,serif;
            color:var(--outer);
        "
        class="mb-4"
    >
        Periode Laporan
    </h5>


    <!-- MODE -->

    <div class="d-flex flex-wrap gap-2 mb-4">


        <a
            href="laporan.php?mode=bulan_ini"
            class="filter-btn
            <?= ($mode == 'bulan_ini')
                ? 'active'
                : ''; ?>"
        >
            Bulan Ini
        </a>


        <a
            href="laporan.php?mode=semua"
            class="filter-btn
            <?= ($mode == 'semua')
                ? 'active'
                : ''; ?>"
        >
            Semua Laporan
        </a>


    </div>


    <!-- TANGGAL -->

    <form method="GET">


        <input
            type="hidden"
            name="mode"
            value="tanggal"
        >


        <div class="row g-3 align-items-end">


            <div class="col-md-4">

                <label class="form-label">
                    Tanggal Awal
                </label>


                <input
                    type="date"
                    name="tanggal_awal"
                    class="form-control"
                    value="<?= htmlspecialchars(
                        $tanggal_awal
                    ); ?>"
                    required
                >

            </div>


            <div class="col-md-4">

                <label class="form-label">
                    Tanggal Akhir
                </label>


                <input
                    type="date"
                    name="tanggal_akhir"
                    class="form-control"
                    value="<?= htmlspecialchars(
                        $tanggal_akhir
                    ); ?>"
                    required
                >

            </div>


            <div class="col-md-4">

                <button
                    type="submit"
                    class="btn-green"
                >
                    Tampilkan
                </button>

            </div>


        </div>

    </form>


    <!-- PENCARIAN -->

    <form
        method="GET"
        class="mt-4"
    >


        <input
            type="hidden"
            name="mode"
            value="<?= htmlspecialchars($mode); ?>"
        >


        <?php if ($mode == 'tanggal') { ?>


            <input
                type="hidden"
                name="tanggal_awal"
                value="<?= htmlspecialchars(
                    $tanggal_awal
                ); ?>"
            >


            <input
                type="hidden"
                name="tanggal_akhir"
                value="<?= htmlspecialchars(
                    $tanggal_akhir
                ); ?>"
            >


        <?php } ?>


        <div class="input-group">


            <input
                type="text"
                name="cari"
                class="form-control"
                placeholder="Cari nama pelanggan..."
                value="<?= htmlspecialchars($keyword); ?>"
            >


            <button
                type="submit"
                class="btn-green"
            >
                Cari
            </button>


            <?php if ($keyword != '') { ?>


                <a
                    href="laporan.php?mode=<?= htmlspecialchars(
                        $mode
                    ); ?><?php

                        if ($mode == 'tanggal') {

                            echo '&tanggal_awal=' .
                                urlencode($tanggal_awal);

                            echo '&tanggal_akhir=' .
                                urlencode($tanggal_akhir);

                        }

                    ?>"
                    class="btn-outline-green"
                >
                    Reset
                </a>


            <?php } ?>


        </div>


    </form>


</div>


<!-- =====================================================
     RINGKASAN
===================================================== -->

<div class="row g-3 mb-4">


    <!-- TOTAL TRANSAKSI -->

    <div class="col-md-4">


        <div class="summary-card">


            <div class="summary-label">
                Total Transaksi
            </div>


            <div class="summary-number">
                <?= $total_transaksi; ?>
            </div>


        </div>


    </div>


    <!-- TOTAL BARANG -->

    <div class="col-md-4">


        <div class="summary-card">


            <div class="summary-label">
                Total Barang Terjual
            </div>


            <div class="summary-number">
                <?= $total_barang; ?> item
            </div>


        </div>


    </div>


    <!-- TOTAL OMSET -->

    <div class="col-md-4">


        <div class="summary-card">


            <div class="summary-label">
                Total Omset
            </div>


            <div class="summary-omset">

                Rp <?= number_format(
                    $total_omset,
                    0,
                    ',',
                    '.'
                ); ?>

            </div>


        </div>


    </div>


</div>


<!-- =====================================================
     TABEL
===================================================== -->

<div class="card-editorial">


    <div class="p-4">


        <div
            class="d-flex
                   justify-content-between
                   align-items-center
                   mb-4"
        >


            <div>


                <div class="section-label">
                    Sales Details
                </div>


                <h4
                    style="
                        font-family:Georgia,serif;
                        color:var(--outer);
                    "
                    class="mb-0"
                >
                    Detail Laporan
                </h4>


            </div>


            <button
                onclick="window.print()"
                class="btn-print no-print"
            >
                Cetak Laporan
            </button>


        </div>


        <div class="table-responsive">


            <table class="table align-middle">


                <thead>

                    <tr>

                        <th>
                            No
                        </th>

                        <th>
                            Nama Pelanggan
                        </th>

                        <th>
                            Tanggal
                        </th>

                        <th>
                            Jumlah
                        </th>

                        <th>
                            Total Penjualan
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php if (count($laporan) > 0) { ?>


                    <?php $no = 1; ?>


                    <?php foreach (
                        $laporan as $row
                    ) { ?>


                        <tr>


                            <td>
                                <?= $no++; ?>
                            </td>


                            <td class="customer-name">

                                <?= htmlspecialchars(
                                    $row['nama_pelanggan']
                                ); ?>

                            </td>


                            <td>

                                <?= date(
                                    'd-m-Y',
                                    strtotime(
                                        $row[
                                            'tanggal_penjualan'
                                        ]
                                    )
                                ); ?>

                            </td>


                            <td>

                                <?= $row['jumlah']; ?>
                                item

                            </td>


                            <td class="price">

                                Rp <?= number_format(
                                    $row['total_harga'],
                                    0,
                                    ',',
                                    '.'
                                ); ?>

                            </td>


                        </tr>


                    <?php } ?>


                <?php } else { ?>


                    <tr>


                        <td
                            colspan="5"
                            class="text-center empty"
                        >


                            <?php if (
                                $keyword != ''
                            ) { ?>

                                Data laporan tidak
                                ditemukan.

                            <?php } else { ?>

                                Tidak ada data
                                penjualan.

                            <?php } ?>


                        </td>


                    </tr>


                <?php } ?>


                </tbody>


                <?php if (
                    count($laporan) > 0
                ) { ?>


                <tfoot>


                    <tr>


                        <th
                            colspan="3"
                            class="text-end"
                        >
                            Total
                        </th>


                        <th>

                            <?= $total_barang; ?>
                            item

                        </th>


                        <th class="price">

                            Rp <?= number_format(
                                $total_omset,
                                0,
                                ',',
                                '.'
                            ); ?>

                        </th>


                    </tr>


                </tfoot>


                <?php } ?>


            </table>


        </div>


    </div>


</div>


</div>


<!-- =====================================================
     FOOTER
===================================================== -->

<footer>


<div class="container py-4 text-center">


    <div class="brand mb-1">
        GLOWÉ BEAUTY
    </div>


    <p class="mb-0">
        Sistem Pengelolaan Basis Data
    </p>


</div>


</footer>


</body>

</html>