<?php

include '../koneksi.php';


// =====================================================
// CARI TRANSAKSI
// =====================================================

$keyword = "";

if (isset($_GET['cari'])) {

    $keyword = trim($_GET['cari']);

}

$where = "";

if ($keyword != "") {

    $keyword_safe = mysqli_real_escape_string(
        $koneksi,
        $keyword
    );

    $where = "
        WHERE pelanggan.nama_pelanggan
        LIKE '%$keyword_safe%'
    ";

}


// =====================================================
// DATA TRANSAKSI
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

            (

                SELECT SUM(detail_penjualan.jumlah)

                FROM detail_penjualan

                WHERE detail_penjualan.id_penjualan
                = transaksi.id_penjualan

            ),

            0

        ) AS jumlah


    FROM transaksi


    JOIN penjualan

        ON transaksi.id_penjualan
        = penjualan.id_penjualan


    JOIN pelanggan

        ON penjualan.id_pelanggan
        = pelanggan.id_pelanggan


    $where


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

    <title>
        Transaksi - Glowé Beauty
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <style>

        :root{
            --ivory:#F8F6EF;
            --green:#315C4B;
            --mustard:#C5A24A;
            --outerspace:#344044;
            --line:#E2E0D8;
        }


        body{
            background:#F7F7F4;
            color:var(--outerspace);
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

        .page-title{
            color:var(--green);
            font-family:Georgia,serif;
        }


        .card{
            border:1px solid #E8E7E1 !important;
        }


        .btn-dark{
            background:var(--green) !important;
            border-color:var(--green) !important;
        }


        .btn-dark:hover{
            background:#274B3D !important;
            border-color:#274B3D !important;
        }


        /* =========================
           FOOTER
        ========================= */

        footer{
            background:var(--ivory) !important;
            border-color:var(--line) !important;
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
                    href="transaksi.php"
                    class="nav-link-custom active"
                >
                    Transaksi
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


    <!-- JUDUL -->

    <div class="mb-4">

        <h2 class="fw-bold page-title">
            Data Transaksi
        </h2>

        <p class="text-secondary mb-0">
            Daftar seluruh transaksi Glowé Beauty.
        </p>

    </div>



    <!-- =================================================
         SEARCH
    ================================================== -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">


            <form method="GET">


                <div class="row g-2">


                    <div class="col-md-8">

                        <input
                            type="text"
                            name="cari"
                            class="form-control"
                            placeholder="Cari nama pelanggan..."
                            value="<?= htmlspecialchars($keyword); ?>"
                        >

                    </div>



                    <div class="col-md-2">

                        <button
                            type="submit"
                            class="btn btn-dark w-100"
                        >
                            Cari
                        </button>

                    </div>



                    <?php if ($keyword != "") { ?>

                        <div class="col-md-2">

                            <a
                                href="transaksi.php"
                                class="btn btn-outline-secondary w-100"
                            >
                                Reset
                            </a>

                        </div>

                    <?php } ?>


                </div>


            </form>


        </div>

    </div>



    <!-- =================================================
         TABEL TRANSAKSI
    ================================================== -->

    <div class="card border-0 shadow-sm">


        <div class="card-body p-4">


            <div class="table-responsive">


                <table
                    class="table table-bordered table-hover align-middle"
                >


                    <thead class="table-light">

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


                    if (mysqli_num_rows($data) > 0) {


                        while (
                            $row =
                            mysqli_fetch_assoc($data)
                        ) {


                    ?>


                        <tr>


                            <!-- NOMOR -->

                            <td>
                                <?= $no++; ?>
                            </td>



                            <!-- NAMA -->

                            <td>

                                <?= htmlspecialchars(
                                    $row['nama_pelanggan']
                                ); ?>

                            </td>



                            <!-- TANGGAL -->

                            <td>

                                <?= date(
                                    'd-m-Y',
                                    strtotime(
                                        $row['tanggal_penjualan']
                                    )
                                ); ?>

                            </td>



                            <!-- JUMLAH -->

                            <td>

                                <?= $row['jumlah']; ?>

                                item

                            </td>



                            <!-- TOTAL -->

                            <td>

                                Rp

                                <?= number_format(
                                    $row['total_harga'],
                                    0,
                                    ',',
                                    '.'
                                ); ?>

                            </td>



                            <!-- PEMBAYARAN -->

                            <td>

                                <?= htmlspecialchars(
                                    $row['metode_pembayaran']
                                ); ?>

                            </td>



                            <!-- STATUS -->

                            <td>


                                <?php if (
                                    $row['status'] == 'sukses'
                                ) { ?>


                                    <span
                                        class="badge bg-success"
                                    >
                                        Sukses
                                    </span>


                                <?php } else { ?>


                                    <span
                                        class="badge bg-warning text-dark"
                                    >
                                        Dihapus
                                    </span>


                                <?php } ?>


                            </td>



                            <!-- AKSI -->

                            <td>


                                <?php if (
                                    $row['status'] == 'sukses'
                                ) { ?>


                                    <div class="d-flex gap-1">


                                        <!-- DETAIL -->

                                        <a
    href="detail_penjualan.php?id=<?= $row['id_penjualan']; ?>"
    class="btn btn-sm btn-outline-secondary"
>
    Detail
</a>


                                        <!-- INVOICE -->

                                        <a
                                            href="invoice.php?id_penjualan=<?= $row['id_penjualan']; ?>"
                                            class="btn btn-sm btn-outline-dark"
                                        >
                                            Invoice
                                        </a>


                                    </div>


                                <?php } else { ?>


                                    <span class="text-muted">
                                        Sudah dihapus
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
                                class="text-center text-muted py-4"
                            >


                                <?php if ($keyword != "") { ?>


                                    Transaksi tidak ditemukan.


                                <?php } else { ?>


                                    Belum ada transaksi.


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

<footer class="border-top mt-5 py-4">


    <div class="container text-center">


        <p class="fw-semibold mb-1">
            Glowé Beauty
        </p>


        <p class="text-secondary mb-0">
            Sistem Pengelolaan Basis Data - Kasir
        </p>


    </div>


</footer>


</body>

</html>