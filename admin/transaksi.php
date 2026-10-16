<?php
include '../koneksi.php';

$keyword = trim($_GET['cari'] ?? '');
$safe = mysqli_real_escape_string($koneksi, $keyword);

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
        COALESCE(SUM(detail_penjualan.jumlah), 0) AS jumlah

    FROM transaksi

    JOIN penjualan
        ON transaksi.id_penjualan = penjualan.id_penjualan

    JOIN pelanggan
        ON penjualan.id_pelanggan = pelanggan.id_pelanggan

    LEFT JOIN detail_penjualan
        ON penjualan.id_penjualan = detail_penjualan.id_penjualan

    WHERE penjualan.tanggal_penjualan = CURDATE()

    AND (
        pelanggan.nama_pelanggan LIKE '%$safe%'
        OR transaksi.metode_pembayaran LIKE '%$safe%'
    )

    GROUP BY
        transaksi.id_transaksi,
        transaksi.id_penjualan,
        pelanggan.nama_pelanggan,
        penjualan.tanggal_penjualan,
        penjualan.total_harga,
        transaksi.metode_pembayaran,
        transaksi.status,
        transaksi.tanggal_transaksi

    ORDER BY transaksi.id_transaksi DESC
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

<title>Transaksi - Glowé Beauty</title>

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


/* =========================
   NAVBAR
========================= */

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


/* =========================
   CONTENT
========================= */

.page{
    padding:65px 0 80px;
}

.section-label{
    color:var(--mustard);
    font-size:10px;
    font-weight:700;
    letter-spacing:2.5px;
    text-transform:uppercase;
    margin-bottom:8px;
}

.page-title{
    color:var(--outerspace);
    font:600 42px Georgia,serif;
    letter-spacing:-1px;
    margin:0 0 8px;
}

.page-desc{
    color:var(--muted);
    font-size:14px;
}

.card-custom{
    background:#fff;
    border:1px solid var(--line);
    border-radius:0;
}


/* =========================
   BUTTON
========================= */

.btn-green{
    background:var(--green);
    color:#fff;
    border:1px solid var(--green);
    border-radius:4px;
    font-size:12px;
    padding:10px 18px;
    text-decoration:none;
}

.btn-green:hover{
    background:var(--green-dark);
    color:#fff;
}

.btn-history{
    color:var(--green);
    background:#fff;
    border:1px solid var(--green);
    border-radius:4px;
    padding:10px 18px;
    font-size:12px;
    text-decoration:none;
}

.btn-history:hover{
    background:var(--green);
    color:#fff;
}

.btn-reset{
    color:var(--green);
    background:#fff;
    border:1px solid var(--green);
    border-radius:4px;
    padding:10px 18px;
    font-size:12px;
    text-decoration:none;
}

.btn-reset:hover{
    background:var(--green);
    color:#fff;
}


/* =========================
   SEARCH
========================= */

.form-control{
    border:1px solid var(--line);
    border-radius:4px;
    padding:10px 13px;
    font-size:13px;
}

.form-control:focus{
    border-color:var(--green);
    box-shadow:0 0 0 2px rgba(0,96,56,.08);
}


/* =========================
   TABLE
========================= */

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


/* =========================
   STATUS
========================= */

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


/* =========================
   ACTION
========================= */

.btn-action{
    display:inline-block;
    border-radius:4px;
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

.btn-edit{
    color:#fff;
    background:var(--mustard);
    border:1px solid var(--mustard);
}

.btn-edit:hover{
    background:#6d7438;
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


/* =========================
   EMPTY
========================= */

.empty{
    color:var(--muted);
    padding:45px!important;
}


/* =========================
   FOOTER
========================= */

footer{
    background:var(--outerspace);
    color:#fff;
    margin-top:0;
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


/* =========================
   RESPONSIVE
========================= */

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


<!-- =========================
     NAVBAR
========================= -->

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


<!-- =========================
     CONTENT
========================= -->

<main class="page">

<div class="container">


<div class="mb-5">

    <div class="section-label">
        Payment Records
    </div>

    <h1 class="page-title">
        Transaksi Hari Ini
    </h1>

    <p class="page-desc mb-0">
        Data transaksi pembayaran pelanggan hari ini.
    </p>

</div>


<!-- MENU -->

<div class="d-flex flex-wrap gap-2 mb-4">

    <a
        href="transaksi.php"
        class="btn-green"
    >
        Transaksi Hari Ini
    </a>

    <a
        href="riwayat.php"
        class="btn-history"
    >
        Riwayat Transaksi →
    </a>

</div>


<!-- SEARCH -->

<div class="card-custom p-4 mb-4">

    <div class="section-label">
        Search
    </div>

    <form method="GET">

        <div class="input-group">

            <input
                type="text"
                name="cari"
                class="form-control"
                placeholder="Cari nama pelanggan atau metode pembayaran..."
                value="<?= htmlspecialchars($keyword); ?>"
            >

            <button
                type="submit"
                class="btn-green"
            >
                Cari
            </button>

            <?php if($keyword != ''): ?>

                <a
                    href="transaksi.php"
                    class="btn-reset"
                >
                    Reset
                </a>

            <?php endif; ?>

        </div>

    </form>

</div>


<!-- TABLE -->

<div class="card-custom">

<div class="p-4">

    <div class="section-label">
        Today's Transactions
    </div>

    <h4
        class="mb-4"
        style="font-family:Georgia,serif;color:var(--outerspace);"
    >
        Daftar Transaksi
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

            if(mysqli_num_rows($data) > 0):

                while($row = mysqli_fetch_assoc($data)):

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

                        <?php if(
                            $row['status'] == 'sukses'
                        ): ?>

                            <span class="badge-success">
                                Sukses
                            </span>

                        <?php elseif(
                            $row['status'] == 'dihapus'
                        ): ?>

                            <span class="badge-deleted">
                                Dihapus
                            </span>

                        <?php endif; ?>

                    </td>


                    <td>

                        <?php if(
                            $row['status'] == 'sukses'
                        ): ?>

                            <a
                                href="invoice.php?id_penjualan=<?= $row['id_penjualan']; ?>"
                                class="btn-action btn-invoice"
                            >
                                Invoice
                            </a>

                            <a
                                href="edit_transaksi.php?id=<?= $row['id_transaksi']; ?>"
                                class="btn-action btn-edit"
                            >
                                Edit
                            </a>

                            <a
                                href="hapus_transaksi.php?id_transaksi=<?= $row['id_transaksi']; ?>"
                                class="btn-action btn-delete"
                                onclick="return confirm('Yakin ingin membatalkan transaksi ini? Transaksi akan berstatus Dihapus dan stok akan dikembalikan.')"
                            >
                                Hapus
                            </a>

                        <?php else: ?>

                            <span class="text-muted small">
                                Sudah dibatalkan
                            </span>

                        <?php endif; ?>

                    </td>

                </tr>

            <?php

                endwhile;

            else:

            ?>

                <tr>

                    <td
                        colspan="8"
                        class="text-center empty"
                    >

                        <?php if($keyword != ''): ?>

                            Transaksi tidak ditemukan.

                        <?php else: ?>

                            Belum ada transaksi hari ini.

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</div>

</div>

</main>


<!-- =========================
     FOOTER
========================= -->

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