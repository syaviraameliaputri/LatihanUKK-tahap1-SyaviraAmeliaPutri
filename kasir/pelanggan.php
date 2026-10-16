<?php

include '../koneksi.php';


/* =========================
   PENCARIAN PELANGGAN
========================= */

$keyword = isset($_GET['cari'])
    ? trim($_GET['cari'])
    : '';

$where = '';

if ($keyword != '') {

    $keyword_safe = mysqli_real_escape_string(
        $koneksi,
        $keyword
    );

    $where = "
        WHERE pelanggan.nama_pelanggan LIKE '%$keyword_safe%'
        OR pelanggan.alamat LIKE '%$keyword_safe%'
        OR pelanggan.no_telepon LIKE '%$keyword_safe%'
    ";
}


/* =========================
   SIMPAN TRANSAKSI
========================= */

if (isset($_POST['simpan'])) {

    $nama = mysqli_real_escape_string(
        $koneksi,
        $_POST['nama_pelanggan']
    );

    $alamat = mysqli_real_escape_string(
        $koneksi,
        $_POST['alamat']
    );

    $no_telepon = mysqli_real_escape_string(
        $koneksi,
        $_POST['no_telepon']
    );


    /* =========================
       CEK PRODUK
    ========================= */

    if (
        !isset($_POST['id_produk']) ||
        count($_POST['id_produk']) == 0
    ) {

        echo "<script>
            alert('Pilih minimal satu produk!');
            window.location='pelanggan.php';
        </script>";

        exit;
    }


    /* =========================
       SIMPAN PELANGGAN
    ========================= */

    mysqli_query($koneksi, "
        INSERT INTO pelanggan
        (
            nama_pelanggan,
            alamat,
            no_telepon
        )
        VALUES
        (
            '$nama',
            '$alamat',
            '$no_telepon'
        )
    ");

    $id_pelanggan = mysqli_insert_id($koneksi);


    /* =========================
       SIMPAN PENJUALAN
    ========================= */

    $tanggal = date('Y-m-d');

    mysqli_query($koneksi, "
        INSERT INTO penjualan
        (
            id_pelanggan,
            tanggal_penjualan,
            total_harga
        )
        VALUES
        (
            '$id_pelanggan',
            '$tanggal',
            '0'
        )
    ");

    $id_penjualan = mysqli_insert_id($koneksi);

    $total = 0;


    /* =========================
       SIMPAN DETAIL PRODUK
    ========================= */

    foreach (
        $_POST['id_produk'] as $key => $id_produk
    ) {

        $id_produk = mysqli_real_escape_string(
            $koneksi,
            $id_produk
        );

        $jumlah = (int) $_POST['jumlah'][$key];


        if (
            $id_produk == '' ||
            $jumlah <= 0
        ) {
            continue;
        }


        /* =========================
           AMBIL DATA PRODUK
        ========================= */

        $produk_query = mysqli_query(
            $koneksi,
            "
            SELECT *
            FROM produk
            WHERE id_produk = '$id_produk'
            "
        );


        $produk_data = mysqli_fetch_assoc(
            $produk_query
        );


        if (!$produk_data) {
            continue;
        }


        $harga = (int) $produk_data['harga'];
        $stok = (int) $produk_data['stok'];


        /* =========================
           CEK STOK
        ========================= */

        if ($jumlah > $stok) {

            echo "<script>
                alert('Stok produk tidak mencukupi!');
                window.location='pelanggan.php';
            </script>";

            exit;
        }


        /* =========================
           HITUNG SUBTOTAL
        ========================= */

        $subtotal = $harga * $jumlah;

        $total += $subtotal;


        /* =========================
           SIMPAN DETAIL
        ========================= */

        mysqli_query(
            $koneksi,
            "
            INSERT INTO detail_penjualan
            (
                id_penjualan,
                id_produk,
                jumlah,
                harga
            )
            VALUES
            (
                '$id_penjualan',
                '$id_produk',
                '$jumlah',
                '$harga'
            )
            "
        );


        /* =========================
           KURANGI STOK
        ========================= */

        mysqli_query(
            $koneksi,
            "
            UPDATE produk
            SET stok = stok - '$jumlah'
            WHERE id_produk = '$id_produk'
            "
        );

    }


    /* =========================
       UPDATE TOTAL PENJUALAN
    ========================= */

    mysqli_query(
        $koneksi,
        "
        UPDATE penjualan
        SET total_harga = '$total'
        WHERE id_penjualan = '$id_penjualan'
        "
    );


    /* =========================
       SIMPAN TRANSAKSI
       TUNAI + SUKSES
    ========================= */

    mysqli_query(
        $koneksi,
        "
        INSERT INTO transaksi
        (
            id_penjualan,
            metode_pembayaran,
            status,
            tanggal_transaksi
        )
        VALUES
        (
            '$id_penjualan',
            'Tunai',
            'sukses',
            NOW()
        )
        "
    );


    echo "<script>
        alert('Transaksi berhasil disimpan!');
        window.location='pelanggan.php';
    </script>";

    exit;
}


/* =========================
   DATA PRODUK
========================= */

$produk = mysqli_query(
    $koneksi,
    "
    SELECT *
    FROM produk
    ORDER BY nama_produk ASC
    "
);


/* =========================
   DATA PELANGGAN
========================= */

$data = mysqli_query(
    $koneksi,
    "
    SELECT

        pelanggan.*,

        COALESCE(
            SUM(
                CASE
                    WHEN transaksi.status = 'sukses'
                    THEN penjualan.total_harga
                    ELSE 0
                END
            ),
            0
        ) AS total_belanja,

        COALESCE(
            SUM(
                CASE
                    WHEN transaksi.status = 'sukses'
                    THEN detail_sum.jumlah
                    ELSE 0
                END
            ),
            0
        ) AS jumlah

    FROM pelanggan

    LEFT JOIN penjualan
        ON pelanggan.id_pelanggan =
           penjualan.id_pelanggan

    LEFT JOIN transaksi
        ON penjualan.id_penjualan =
           transaksi.id_penjualan

    LEFT JOIN (

        SELECT
            id_penjualan,
            SUM(jumlah) AS jumlah

        FROM detail_penjualan

        GROUP BY id_penjualan

    ) AS detail_sum

        ON penjualan.id_penjualan =
           detail_sum.id_penjualan

    $where

    GROUP BY pelanggan.id_pelanggan

    ORDER BY pelanggan.id_pelanggan DESC
    "
);


$total_pelanggan = mysqli_num_rows($data);

?>


<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Pelanggan - Glowé Beauty</title>


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
           PRODUK
        ========================= */

        .search-produk{
            height:44px;
            border-radius:10px;
            border:1px solid #ddd;
            margin-bottom:18px;
        }


        .search-produk:focus{
            border-color:#aaa;
            box-shadow:0 0 0 3px rgba(0,0,0,0.05);
        }


        .produk-pilihan{
            display:grid;
            grid-template-columns:
                repeat(auto-fill,minmax(190px,1fr));
            gap:12px;
            margin-bottom:20px;
        }


        .produk-card{
            border:1px solid #ddd;
            border-radius:12px;
            padding:15px;
            background:white;
            cursor:pointer;
            transition:0.2s;
        }


        .produk-card:hover{
            border-color:var(--green);
            transform:translateY(-2px);
        }


        .produk-card.dipilih{
            border:2px solid var(--green);
            background:#F1F5F1;
        }


        .produk-nama{
            font-weight:600;
            margin-bottom:6px;
        }


        .produk-harga{
            font-weight:600;
            margin-bottom:5px;
        }


        .produk-stok{
            font-size:13px;
            color:#777;
        }


        .produk-card.stok-habis{
            opacity:0.5;
            cursor:not-allowed;
        }


        /* =========================
           PRODUK TERPILIH
        ========================= */

        .produk-terpilih{
            border:1px solid #ddd;
            border-radius:10px;
            padding:12px;
            margin-bottom:10px;
            background:#fff;
        }


        .produk-terpilih-nama{
            font-weight:600;
        }


        .jumlah-input{
            max-width:100px;
        }


        .hapus-produk{
            white-space:nowrap;
        }


        /* =========================
           TOTAL
        ========================= */

        .total-box{
            background:#F8F9F7;
            border:1px solid #ddd;
            border-radius:10px;
            padding:15px;
        }


        /* =========================
           FOOTER
        ========================= */

        footer{
            background:var(--ivory) !important;
            border-color:var(--line) !important;
        }


        /* =========================
           RESPONSIVE NAVBAR
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
     NAVBAR KASIR
========================= -->

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
                    class="nav-link-custom active"
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
                    class="nav-link-custom"
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



<!-- =========================
     CONTENT
========================= -->

<div class="container py-5">


    <h2 class="fw-bold page-title">
        Data Pelanggan
    </h2>


    <p class="text-secondary">
        Tambahkan pelanggan dan pembelian Glowé Beauty.
    </p>



    <!-- =========================
         TOTAL PELANGGAN
    ========================= -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <p class="text-secondary mb-1">
                Total Pelanggan
            </p>


            <h3 class="fw-bold mb-0">
                <?= $total_pelanggan; ?>
            </h3>

        </div>

    </div>



    <!-- =========================
         FORM PEMBELIAN
    ========================= -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body p-4">


            <h5 class="fw-bold mb-3">
                Tambah Pelanggan dan Pembelian
            </h5>


            <form method="POST">


                <!-- DATA PELANGGAN -->

                <div class="row g-3">


                    <div class="col-md-4">

                        <label class="form-label">
                            Nama Pelanggan
                        </label>


                        <input
                            type="text"
                            name="nama_pelanggan"
                            class="form-control"
                            placeholder="Masukkan nama"
                            required
                        >

                    </div>


                    <div class="col-md-4">

                        <label class="form-label">
                            Alamat
                        </label>


                        <input
                            type="text"
                            name="alamat"
                            class="form-control"
                            placeholder="Masukkan alamat"
                            required
                        >

                    </div>


                    <div class="col-md-4">

                        <label class="form-label">
                            No. Telepon
                        </label>


                        <input
                            type="text"
                            name="no_telepon"
                            class="form-control"
                            placeholder="Masukkan nomor telepon"
                            required
                        >

                    </div>


                </div>



                <hr class="my-4">



                <!-- PRODUK -->

                <h6 class="fw-bold mb-3">
                    Pilih Produk
                </h6>


                <input
                    type="text"
                    id="cari-produk"
                    class="form-control search-produk"
                    placeholder="Cari produk..."
                    autocomplete="off"
                >


                <div
                    id="daftar-produk"
                    class="produk-pilihan"
                >


                    <?php

                    mysqli_data_seek(
                        $produk,
                        0
                    );


                    while (
                        $pr =
                        mysqli_fetch_assoc($produk)
                    ) {

                    ?>


                        <div
                            class="produk-card
                            <?= $pr['stok'] <= 0
                                ? 'stok-habis'
                                : ''; ?>"
                            data-id="<?= $pr['id_produk']; ?>"
                            data-nama="<?= htmlspecialchars(
                                strtolower(
                                    $pr['nama_produk']
                                ),
                                ENT_QUOTES
                            ); ?>"
                            data-harga="<?= $pr['harga']; ?>"
                            data-stok="<?= $pr['stok']; ?>"
                        >


                            <div class="produk-nama">

                                <?= htmlspecialchars(
                                    $pr['nama_produk']
                                ); ?>

                            </div>


                            <div class="produk-harga">

                                Rp
                                <?= number_format(
                                    $pr['harga'],
                                    0,
                                    ',',
                                    '.'
                                ); ?>

                            </div>


                            <div class="produk-stok">

                                Stok:
                                <?= $pr['stok']; ?>

                            </div>


                        </div>


                    <?php } ?>


                </div>



                <!-- PRODUK YANG DIBELI -->

                <h6 class="fw-bold mb-3">
                    Produk yang Dibeli
                </h6>


                <div id="produk-terpilih">


                    <p
                        id="belum-pilih"
                        class="text-secondary"
                    >
                        Belum ada produk yang dipilih.
                    </p>


                </div>



                <!-- TOTAL -->

                <div class="total-box mt-4">


                    <div
                        class="d-flex
                        justify-content-between
                        align-items-center"
                    >


                        <span class="fw-bold">
                            Total Harga
                        </span>


                        <span
                            class="fw-bold fs-4"
                            id="total-harga"
                        >
                            Rp 0
                        </span>


                    </div>


                </div>



                <!-- PEMBAYARAN -->

                <div class="mt-3">

                    <small class="text-secondary">

                        Pembayaran:

                        <strong>
                            Tunai
                        </strong>

                    </small>

                </div>



                <!-- SIMPAN -->

                <button
                    type="submit"
                    name="simpan"
                    class="btn btn-dark mt-3"
                >
                    Simpan Pelanggan & Pembelian
                </button>


            </form>


        </div>

    </div>



    <!-- =========================
         DAFTAR PELANGGAN
    ========================= -->

    <div class="card border-0 shadow-sm">

        <div class="card-body p-4">


            <h5 class="fw-bold mb-3">
                Daftar Pelanggan
            </h5>



            <!-- PENCARIAN PELANGGAN -->

            <form
                method="GET"
                class="mb-3"
            >


                <div class="input-group">


                    <input
                        type="text"
                        name="cari"
                        class="form-control"
                        placeholder="Cari nama pelanggan..."
                        value="<?= htmlspecialchars(
                            $keyword
                        ); ?>"
                    >


                    <button
                        type="submit"
                        class="btn btn-dark"
                    >
                        Cari
                    </button>


                    <?php if ($keyword != '') { ?>


                        <a
                            href="pelanggan.php"
                            class="btn btn-outline-secondary"
                        >
                            Reset
                        </a>


                    <?php } ?>


                </div>


            </form>



            <!-- TABEL -->

            <div class="table-responsive">


                <table
                    class="table table-hover align-middle"
                >


                    <thead>

                        <tr>

                            <th>No</th>

                            <th>Nama Pelanggan</th>

                            <th>Alamat</th>

                            <th>No. Telepon</th>

                            <th>Jumlah</th>

                            <th>Total Belanja</th>

                            <th>Aksi</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    if ($total_pelanggan > 0) {

                        $no = 1;


                        while (
                            $row =
                            mysqli_fetch_assoc($data)
                        ) {

                    ?>


                            <tr>


                                <td>
                                    <?= $no++; ?>
                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $row['nama_pelanggan']
                                    ); ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $row['alamat']
                                    ); ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $row['no_telepon']
                                    ); ?>

                                </td>


                                <td>

                                    <?= $row['jumlah']; ?>
                                    item

                                </td>


                                <td class="fw-semibold">

                                    Rp
                                    <?= number_format(
                                        $row['total_belanja'],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </td>


                                <td>

                                    <a
                                        href="transaksi.php?id_pelanggan=<?= $row['id_pelanggan']; ?>"
                                        class="btn btn-sm btn-dark"
                                    >
                                        Transaksi
                                    </a>

                                </td>


                            </tr>


                    <?php

                        }

                    } else {

                    ?>


                        <tr>

                            <td
                                colspan="7"
                                class="text-center
                                text-muted
                                py-4"
                            >

                                <?php

                                if ($keyword != '') {

                                    echo 'Pelanggan tidak ditemukan.';

                                } else {

                                    echo 'Belum ada data pelanggan.';

                                }

                                ?>

                            </td>

                        </tr>


                    <?php } ?>


                    </tbody>

                </table>


            </div>


        </div>

    </div>


</div>



<!-- =========================
     FOOTER
========================= -->

<footer
    class="border-top mt-5 py-4"
>


    <div class="container text-center">


        <p class="fw-semibold mb-1">
            Glowé Beauty
        </p>


        <p class="text-secondary mb-0">
            Sistem Pengelolaan Basis Data
        </p>


    </div>


</footer>



<!-- =========================
     JAVASCRIPT
========================= -->

<script>


/* =========================
   DATA PRODUK TERPILIH
========================= */

let produkDipilih = [];



/* =========================
   CARI PRODUK
========================= */

document
    .getElementById('cari-produk')
    .addEventListener(
        'input',
        function() {

            let keyword =
                this.value
                    .toLowerCase()
                    .trim();


            document
                .querySelectorAll(
                    '.produk-card'
                )
                .forEach(
                    function(card) {

                        let nama =
                            card.getAttribute(
                                'data-nama'
                            );


                        if (
                            nama.includes(keyword)
                        ) {

                            card.style.display =
                                'block';

                        } else {

                            card.style.display =
                                'none';

                        }

                    }
                );

        }
    );



/* =========================
   KLIK PRODUK
========================= */

document.addEventListener(
    'click',
    function(e) {

        let card =
            e.target.closest(
                '.produk-card'
            );


        if (!card) {
            return;
        }


        if (
            card.classList.contains(
                'stok-habis'
            )
        ) {

            return;

        }


        let id =
            card.getAttribute(
                'data-id'
            );


        let nama =
            card.getAttribute(
                'data-nama'
            );


        let namaAsli =
            card.querySelector(
                '.produk-nama'
            ).textContent.trim();


        let harga =
            parseInt(
                card.getAttribute(
                    'data-harga'
                )
            );


        let stok =
            parseInt(
                card.getAttribute(
                    'data-stok'
                )
            );


        let sudahAda =
            produkDipilih.find(
                function(produk) {

                    return produk.id == id;

                }
            );


        if (sudahAda) {

            if (
                sudahAda.jumlah < stok
            ) {

                sudahAda.jumlah++;

                renderProdukTerpilih();

            }

            return;
        }


        produkDipilih.push({

            id: id,

            nama: namaAsli,

            harga: harga,

            stok: stok,

            jumlah: 1

        });


        card.classList.add(
            'dipilih'
        );


        renderProdukTerpilih();

    }
);



/* =========================
   TAMPILKAN PRODUK TERPILIH
========================= */

function renderProdukTerpilih() {

    let container =
        document.getElementById(
            'produk-terpilih'
        );


    container.innerHTML = '';


    if (
        produkDipilih.length === 0
    ) {

        container.innerHTML = `

            <p
                id="belum-pilih"
                class="text-secondary"
            >
                Belum ada produk yang dipilih.
            </p>

        `;


        hitungTotal();

        return;
    }


    produkDipilih.forEach(
        function(produk, index) {

            let div =
                document.createElement(
                    'div'
                );


            div.className =
                'produk-terpilih';


            div.innerHTML = `

                <div
                    class="row
                    align-items-center
                    g-2"
                >

                    <div
                        class="col-md-5"
                    >

                        <div
                            class="produk-terpilih-nama"
                        >
                            ${produk.nama}
                        </div>

                        <small
                            class="text-secondary"
                        >
                            Rp ${produk.harga.toLocaleString('id-ID')}
                        </small>

                    </div>


                    <div
                        class="col-md-3"
                    >

                        <input
                            type="number"
                            class="form-control jumlah-input"
                            min="1"
                            max="${produk.stok}"
                            value="${produk.jumlah}"
                            data-index="${index}"
                        >

                    </div>


                    <div
                        class="col-md-2"
                    >

                        <strong>
                            Rp ${
                                (
                                    produk.harga *
                                    produk.jumlah
                                ).toLocaleString(
                                    'id-ID'
                                )
                            }
                        </strong>

                    </div>


                    <div
                        class="col-md-2"
                    >

                        <button
                            type="button"
                            class="btn
                            btn-danger
                            btn-sm
                            hapus-produk"
                            data-index="${index}"
                        >
                            Hapus
                        </button>

                    </div>

                </div>


                <input
                    type="hidden"
                    name="id_produk[]"
                    value="${produk.id}"
                >


                <input
                    type="hidden"
                    name="jumlah[]"
                    value="${produk.jumlah}"
                    class="jumlah-hidden"
                    data-index="${index}"
                >

            `;


            container.appendChild(
                div
            );

        }
    );


    hitungTotal();

}



/* =========================
   UBAH JUMLAH
========================= */

document.addEventListener(
    'input',
    function(e) {

        if (
            !e.target.classList.contains(
                'jumlah-input'
            )
        ) {

            return;

        }


        let index =
            parseInt(
                e.target.getAttribute(
                    'data-index'
                )
            );


        let jumlah =
            parseInt(
                e.target.value
            ) || 1;


        let stok =
            produkDipilih[
                index
            ].stok;


        if (jumlah < 1) {

            jumlah = 1;

        }


        if (jumlah > stok) {

            jumlah = stok;

        }


        produkDipilih[
            index
        ].jumlah = jumlah;


        e.target.value = jumlah;


        renderProdukTerpilih();

    }
);



/* =========================
   HAPUS PRODUK
========================= */

document.addEventListener(
    'click',
    function(e) {

        if (
            !e.target.classList.contains(
                'hapus-produk'
            )
        ) {

            return;

        }


        let index =
            parseInt(
                e.target.getAttribute(
                    'data-index'
                )
            );


        let id =
            produkDipilih[
                index
            ].id;


        produkDipilih.splice(
            index,
            1
        );


        document
            .querySelectorAll(
                '.produk-card'
            )
            .forEach(
                function(card) {

                    if (
                        card.getAttribute(
                            'data-id'
                        ) == id
                    ) {

                        card.classList.remove(
                            'dipilih'
                        );

                    }

                }
            );


        renderProdukTerpilih();

    }
);



/* =========================
   HITUNG TOTAL
========================= */

function hitungTotal() {

    let total = 0;


    produkDipilih.forEach(
        function(produk) {

            total +=
                produk.harga *
                produk.jumlah;

        }
    );


    document
        .getElementById(
            'total-harga'
        )
        .innerText =
        'Rp ' +
        total.toLocaleString(
            'id-ID'
        );

}


</script>


</body>

</html>