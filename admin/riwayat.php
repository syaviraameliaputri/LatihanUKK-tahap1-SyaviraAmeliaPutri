<?php

include '../koneksi.php';


// =====================================================
// FILTER
// =====================================================

$mode = isset($_GET['mode'])
    ? $_GET['mode']
    : 'hari_ini';

$tanggal_awal = isset($_GET['tanggal_awal'])
    ? $_GET['tanggal_awal']
    : date('Y-m-d');

$tanggal_akhir = isset($_GET['tanggal_akhir'])
    ? $_GET['tanggal_akhir']
    : date('Y-m-d');


// =====================================================
// PENCARIAN
// =====================================================

$keyword = trim($_GET['cari'] ?? '');

$safe = mysqli_real_escape_string(
    $koneksi,
    $keyword
);


// =====================================================
// KONDISI FILTER
// =====================================================

$where = '';

if ($mode == 'hari_ini') {

    $where = "
        WHERE penjualan.tanggal_penjualan = CURDATE()
    ";

} elseif ($mode == 'tanggal') {

    if ($tanggal_awal != '' && $tanggal_akhir != '') {

        $where = "
            WHERE penjualan.tanggal_penjualan
            BETWEEN '$tanggal_awal'
            AND '$tanggal_akhir'
        ";

    }

} elseif ($mode == 'semua') {

    $where = '';

}


// =====================================================
// TAMBAHKAN PENCARIAN
// =====================================================

if ($keyword != '') {

    if ($where == '') {

        $where = "WHERE ";

    } else {

        $where .= " AND ";

    }

    $where .= "
        (
            pelanggan.nama_pelanggan LIKE '%$safe%'
            OR transaksi.metode_pembayaran LIKE '%$safe%'
            OR transaksi.status LIKE '%$safe%'
        )
    ";

}


// =====================================================
// AMBIL DATA RIWAYAT
// =====================================================

$data = mysqli_query($koneksi, "

    SELECT

        transaksi.id_transaksi,

        transaksi.id_penjualan,

        pelanggan.nama_pelanggan,

        penjualan.tanggal_penjualan,

        penjualan.total_harga,

        transaksi.metode_pembayaran,

        transaksi.status,

        transaksi.tanggal_transaksi,

        COALESCE(
            SUM(detail_penjualan.jumlah),
            0
        ) AS jumlah

    FROM transaksi

    INNER JOIN penjualan
        ON transaksi.id_penjualan =
           penjualan.id_penjualan

    INNER JOIN pelanggan
        ON penjualan.id_pelanggan =
           pelanggan.id_pelanggan

    LEFT JOIN detail_penjualan
        ON penjualan.id_penjualan =
           detail_penjualan.id_penjualan

    $where

    GROUP BY

        transaksi.id_transaksi,

        transaksi.id_penjualan,

        pelanggan.nama_pelanggan,

        penjualan.tanggal_penjualan,

        penjualan.total_harga,

        transaksi.metode_pembayaran,

        transaksi.status,

        transaksi.tanggal_transaksi

    ORDER BY
        transaksi.id_transaksi DESC

");

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Riwayat Transaksi - Glowé Beauty</title>

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

    text-decoration:none;

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
   FILTER BUTTON
===================================================== */

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

.customer-name{

    font-weight:600;

}

.price{

    color:var(--green);

    font-weight:600;

}


/* =====================================================
   STATUS
===================================================== */

.badge-success{

    display:inline-block;

    color:var(--green);

    border:1px solid #9bb9a9;

    background:#edf5f0;

    padding:6px 10px;

    font-size:10px;

    font-weight:600;

    letter-spacing:.5px;

}

.badge-deleted{

    display:inline-block;

    color:#8B5E3C;

    border:1px solid #d5bba7;

    background:#f8eee7;

    padding:6px 10px;

    font-size:10px;

    font-weight:600;

    letter-spacing:.5px;

}


/* =====================================================
   AKSI
===================================================== */

.btn-action{

    display:inline-block;

    border-radius:0;

    font-size:10px;

    padding:7px 11px;

    text-decoration:none;

    margin:2px;

}

.btn-invoice{

    color:var(--green);

    border:1px solid var(--green);

    background:#fff;

}

.btn-invoice:hover{

    background:var(--green);

    color:#fff;

}

.btn-delete{

    color:#9b3d35;

    border:1px solid #b96b64;

    background:#fff;

}

.btn-delete:hover{

    background:#9b3d35;

    color:#fff;

}


/* =====================================================
   EMPTY
===================================================== */

.empty{

    color:var(--muted);

    padding:45px!important;

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
        class="nav-link-custom active"
    >
        Transaksi
    </a>


    <a
        href="laporan.php"
        class="nav-link-custom"
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


    <div class="section-label">
        Transaction Archive
    </div>


    <h1 class="page-title">
        Riwayat Transaksi
    </h1>


    <p class="page-desc mb-4">
        Lihat seluruh riwayat transaksi berdasarkan tanggal.
    </p>


    <!-- =================================================
         MENU
    ================================================== -->

    <div class="d-flex flex-wrap gap-2 mb-4">


        <a
            href="transaksi.php"
            class="btn-outline-green"
        >
            ← Transaksi Hari Ini
        </a>


        <a
            href="riwayat.php?mode=hari_ini"
            class="filter-btn
            <?= $mode == 'hari_ini' ? 'active' : ''; ?>"
        >
            Hari Ini
        </a>


        <a
            href="riwayat.php?mode=semua"
            class="filter-btn
            <?= $mode == 'semua' ? 'active' : ''; ?>"
        >
            Semua Transaksi
        </a>


    </div>


    <!-- =================================================
         FILTER TANGGAL
    ================================================== -->

    <div class="card-editorial p-4 mb-4">


        <div class="section-label">
            Filter
        </div>


        <h5
            style="font-family:Georgia,serif;color:var(--outer);"
            class="mb-4"
        >
            Pilih Rentang Tanggal
        </h5>


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
                        value="<?= htmlspecialchars($tanggal_awal); ?>"
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
                        value="<?= htmlspecialchars($tanggal_akhir); ?>"
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


    </div>


    <!-- =================================================
         PENCARIAN
    ================================================== -->

    <div class="card-editorial p-4 mb-4">


        <div class="section-label">
            Search
        </div>


        <form method="GET">


            <input
                type="hidden"
                name="mode"
                value="<?= htmlspecialchars($mode); ?>"
            >


            <?php if ($mode == 'tanggal') { ?>


                <input
                    type="hidden"
                    name="tanggal_awal"
                    value="<?= htmlspecialchars($tanggal_awal); ?>"
                >


                <input
                    type="hidden"
                    name="tanggal_akhir"
                    value="<?= htmlspecialchars($tanggal_akhir); ?>"
                >


            <?php } ?>


            <div class="input-group">


                <input
                    type="text"
                    name="cari"
                    class="form-control"
                    placeholder="Cari nama pelanggan, pembayaran, atau status..."
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
                        href="riwayat.php?mode=<?= htmlspecialchars($mode); ?><?php

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


    <!-- =================================================
         TABEL RIWAYAT
    ================================================== -->

    <div class="card-editorial">


        <div class="p-4">


            <div class="section-label">
                Transaction History
            </div>


            <h4
                style="font-family:Georgia,serif;color:var(--outer);"
                class="mb-4"
            >
                Daftar Riwayat Transaksi
            </h4>


            <div class="table-responsive">


                <table class="table align-middle">


                    <thead>

                        <tr>

                            <th>No</th>

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
                                Total
                            </th>

                            <th>
                                Metode Pembayaran
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    $no = 1;

                    if (
                        $data &&
                        mysqli_num_rows($data) > 0
                    ) {

                        while (
                            $row = mysqli_fetch_assoc($data)
                        ) {

                    ?>


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
                                        $row['tanggal_penjualan']
                                    )
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

                                <?= htmlspecialchars(
                                    $row['metode_pembayaran']
                                ); ?>

                            </td>


                            <td>


                                <?php if (
                                    $row['status'] == 'sukses'
                                ) { ?>


                                    <span class="badge-success">
                                        Sukses
                                    </span>


                                <?php } elseif (
                                    $row['status'] == 'dihapus'
                                ) { ?>


                                    <span class="badge-deleted">
                                        Dihapus
                                    </span>


                                <?php } ?>


                            </td>


                            <td>


                                <a
                                    href="invoice.php?id_penjualan=<?= $row['id_penjualan']; ?>"
                                    class="btn-action btn-invoice"
                                >
                                    Invoice
                                </a>


                                <?php if (
                                    $row['status'] == 'sukses'
                                ) { ?>


                                    <a
                                        href="hapus_transaksi.php?id_transaksi=<?= $row['id_transaksi']; ?>"
                                        class="btn-action btn-delete"
                                        onclick="return confirm('Yakin ingin membatalkan transaksi ini? Transaksi akan berstatus Dihapus dan stok akan dikembalikan.')"
                                    >
                                        Hapus
                                    </a>


                                <?php } else { ?>


                                    <span class="text-muted small">
                                        Sudah dibatalkan
                                    </span>


                                <?php } ?>


                            </td>


                        </tr>


                    <?php

                        }

                    } else {

                    ?>


                        <tr>

                            <td
                                colspan="8"
                                class="text-center empty"
                            >


                                <?php if ($keyword != '') { ?>


                                    Transaksi tidak ditemukan.


                                <?php } else { ?>


                                    Belum ada riwayat transaksi.


                                <?php } ?>


                            </td>

                        </tr>


                    <?php

                    }

                    ?>


                    </tbody>

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