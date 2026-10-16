<?php

include '../koneksi.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;


/* =========================
   AMBIL TRANSAKSI
========================= */

$data = mysqli_query($koneksi, "
    SELECT 
        t.id_transaksi,
        t.id_penjualan,
        t.status,
        p.id_pelanggan,
        p.nama_pelanggan,
        pj.total_harga
    FROM transaksi t
    JOIN penjualan pj ON t.id_penjualan = pj.id_penjualan
    JOIN pelanggan p ON pj.id_pelanggan = p.id_pelanggan
    WHERE t.id_transaksi = $id
");

$row = mysqli_fetch_assoc($data);

if (!$row) {
    echo "<script>
        alert('Data transaksi tidak ditemukan!');
        window.location='transaksi.php';
    </script>";
    exit;
}

if ($row['status'] == 'dihapus') {
    echo "<script>
        alert('Transaksi yang sudah dihapus tidak dapat diedit!');
        window.location='transaksi.php';
    </script>";
    exit;
}


/* =========================
   SIMPAN PERUBAHAN
========================= */

if (isset($_POST['update'])) {

    mysqli_begin_transaction($koneksi);

    try {

        $id_penjualan = (int) $row['id_penjualan'];
        $id_pelanggan = (int) $row['id_pelanggan'];

        $nama = mysqli_real_escape_string(
            $koneksi,
            trim($_POST['nama_pelanggan'])
        );

        if ($nama == '') {
            throw new Exception('Nama pelanggan wajib diisi.');
        }


        /* UPDATE NAMA PELANGGAN */

        mysqli_query($koneksi, "
            UPDATE pelanggan
            SET nama_pelanggan = '$nama'
            WHERE id_pelanggan = $id_pelanggan
        ");


        /* AMBIL DETAIL LAMA */

        $detail = mysqli_query($koneksi, "
            SELECT id_detail, id_produk, jumlah
            FROM detail_penjualan
            WHERE id_penjualan = $id_penjualan
        ");

        $detail_lama = [];

        while ($d = mysqli_fetch_assoc($detail)) {
            $detail_lama[$d['id_detail']] = $d;
        }


        $jumlah_post = $_POST['jumlah'] ?? [];
        $hapus = $_POST['hapus_produk'] ?? [];

        $produk_baru = $_POST['produk_baru'] ?? [];
        $jumlah_baru = $_POST['jumlah_baru'] ?? [];

        $total = 0;
        $produk_terpakai = [];


        /* =========================
           PRODUK LAMA
        ========================= */

        foreach ($detail_lama as $id_detail => $item) {

            $id_detail = (int) $id_detail;
            $id_produk = (int) $item['id_produk'];
            $jumlah_lama = (int) $item['jumlah'];

            /* HAPUS PRODUK */

            if (in_array($id_detail, $hapus)) {

                mysqli_query($koneksi, "
                    UPDATE produk
                    SET stok = stok + $jumlah_lama
                    WHERE id_produk = $id_produk
                ");

                mysqli_query($koneksi, "
                    DELETE FROM detail_penjualan
                    WHERE id_detail = $id_detail
                ");

                continue;
            }


            /* JUMLAH BARU */

            $jumlah = isset($jumlah_post[$id_detail])
                ? (int) $jumlah_post[$id_detail]
                : 0;

            if ($jumlah < 1) {
                throw new Exception('Jumlah produk minimal 1.');
            }


            /* AMBIL HARGA + STOK */

            $q = mysqli_query($koneksi, "
                SELECT harga, stok
                FROM produk
                WHERE id_produk = $id_produk
                FOR UPDATE
            ");

            if (!$q || mysqli_num_rows($q) == 0) {
                throw new Exception('Produk tidak ditemukan.');
            }

            $p = mysqli_fetch_assoc($q);

            $harga = (int) $p['harga'];
            $stok = (int) $p['stok'];


            /* KEMBALIKAN STOK LAMA, LALU KURANGI STOK BARU */

            $stok_akhir = $stok + $jumlah_lama - $jumlah;

            if ($stok_akhir < 0) {
                throw new Exception('Stok produk tidak mencukupi.');
            }

            mysqli_query($koneksi, "
                UPDATE produk
                SET stok = $stok_akhir
                WHERE id_produk = $id_produk
            ");


            /* UPDATE DETAIL */

            mysqli_query($koneksi, "
                UPDATE detail_penjualan
                SET jumlah = $jumlah,
                    harga = $harga
                WHERE id_detail = $id_detail
            ");

            $total += $harga * $jumlah;
            $produk_terpakai[] = $id_produk;
        }


        /* =========================
           PRODUK BARU
        ========================= */

        foreach ($produk_baru as $i => $id_produk) {

            $id_produk = (int) $id_produk;
            $jumlah = isset($jumlah_baru[$i])
                ? (int) $jumlah_baru[$i]
                : 0;

            if ($id_produk <= 0) {
                continue;
            }

            if ($jumlah < 1) {
                throw new Exception('Jumlah produk baru minimal 1.');
            }

            if (in_array($id_produk, $produk_terpakai)) {
                throw new Exception(
                    'Produk yang sama tidak boleh ditambahkan dua kali.'
                );
            }


            $q = mysqli_query($koneksi, "
                SELECT nama_produk, harga, stok
                FROM produk
                WHERE id_produk = $id_produk
                FOR UPDATE
            ");

            if (!$q || mysqli_num_rows($q) == 0) {
                throw new Exception('Produk baru tidak ditemukan.');
            }

            $p = mysqli_fetch_assoc($q);

            $harga = (int) $p['harga'];
            $stok = (int) $p['stok'];


            if ($stok < $jumlah) {
                throw new Exception(
                    'Stok produk "' . $p['nama_produk'] . '" tidak mencukupi.'
                );
            }


            mysqli_query($koneksi, "
                UPDATE produk
                SET stok = stok - $jumlah
                WHERE id_produk = $id_produk
            ");


            mysqli_query($koneksi, "
                INSERT INTO detail_penjualan
                (id_penjualan, id_produk, jumlah, harga)
                VALUES
                ($id_penjualan, $id_produk, $jumlah, $harga)
            ");

            $total += $harga * $jumlah;
            $produk_terpakai[] = $id_produk;
        }


        /* =========================
           CEK PRODUK
        ========================= */

        $cek = mysqli_query($koneksi, "
            SELECT COUNT(*) AS jumlah
            FROM detail_penjualan
            WHERE id_penjualan = $id_penjualan
        ");

        $cek_row = mysqli_fetch_assoc($cek);

        if ((int) $cek_row['jumlah'] == 0) {
            throw new Exception(
                'Minimal harus ada satu produk dalam transaksi.'
            );
        }


        /* =========================
           UPDATE TOTAL
        ========================= */

        mysqli_query($koneksi, "
            UPDATE penjualan
            SET total_harga = $total
            WHERE id_penjualan = $id_penjualan
        ");


        mysqli_commit($koneksi);

        echo "<script>
            alert('Transaksi berhasil diperbarui!');
            window.location='transaksi.php';
        </script>";
        exit;


    } catch (Exception $e) {

        mysqli_rollback($koneksi);

        echo "<script>
            alert(" . json_encode(
                'Transaksi gagal diperbarui: ' . $e->getMessage()
            ) . ");
            window.history.back();
        </script>";
        exit;
    }
}


/* =========================
   DETAIL PRODUK
========================= */

$detail = mysqli_query($koneksi, "
    SELECT
        d.id_detail,
        d.id_produk,
        p.nama_produk,
        d.jumlah,
        d.harga
    FROM detail_penjualan d
    JOIN produk p ON d.id_produk = p.id_produk
    WHERE d.id_penjualan = {$row['id_penjualan']}
");


/* =========================
   SEMUA PRODUK
========================= */

$produk = mysqli_query($koneksi, "
    SELECT id_produk, nama_produk, harga, stok
    FROM produk
    ORDER BY nama_produk ASC
");

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Edit Transaksi - Glowé Beauty</title>

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

.form-label{
    color:var(--green);
    font-size:13px;
    font-weight:600;
}

.form-control,
.form-select{
    border:1px solid #D8D4CA;
    border-radius:6px;
    padding:10px 12px;
    font-size:13px;
}

.form-control:focus,
.form-select:focus{
    border-color:#9DB5AA;
    box-shadow:0 0 0 .15rem rgba(49,92,77,.10);
}

.table thead th{
    background:#F5F2EA;
    color:var(--green);
    font-size:12px;
    border-bottom:1px solid var(--line);
    padding:13px;
}

.table td{
    padding:13px;
    font-size:13px;
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

    <a href="penjualan.php" class="nav-link-custom">Penjualan</a>

    <a href="transaksi.php" class="nav-link-custom active">Transaksi</a>

    <a href="laporan.php" class="nav-link-custom">Laporan</a>

    <a href="../logout.php"
       class="logout"
       onclick="return confirm('Yakin ingin logout?')">
        Logout
    </a>

</div>

</div>

</div>

</nav>


<div class="page">

<div class="mb-4">

    <h1 class="title mb-1">
        Edit Transaksi
    </h1>

    <p class="subtitle mb-0">
        Ubah pelanggan dan produk pada transaksi.
    </p>

</div>


<div class="card">

<div class="card-body p-4 p-md-5">

<form method="POST">

<div class="mb-4">

<label class="form-label">
    Nama Pelanggan
</label>

<input
    type="text"
    name="nama_pelanggan"
    class="form-control"
    value="<?= htmlspecialchars($row['nama_pelanggan']); ?>"
    required
>

</div>


<h5 class="fw-bold mb-3" style="color:#315C4D;">
    Produk
</h5>


<div class="table-responsive">

<table class="table align-middle">

<thead>

<tr>
    <th>Produk</th>
    <th>Harga</th>
    <th style="width:150px;">Jumlah</th>
    <th>Subtotal</th>
    <th style="width:100px;">Aksi</th>
</tr>

</thead>

<tbody id="produk-container">

<?php while ($item = mysqli_fetch_assoc($detail)): ?>

<tr>

<td class="fw-semibold">
    <?= htmlspecialchars($item['nama_produk']); ?>
</td>

<td>
    Rp <?= number_format($item['harga'],0,',','.'); ?>
</td>

<td>

<input
    type="number"
    name="jumlah[<?= $item['id_detail']; ?>]"
    class="form-control jumlah"
    data-harga="<?= $item['harga']; ?>"
    value="<?= $item['jumlah']; ?>"
    min="1"
    required
>

</td>

<td class="subtotal fw-semibold">
    Rp <?= number_format(
        $item['harga'] * $item['jumlah'],
        0,
        ',',
        '.'
    ); ?>
</td>

<td>

<button
    type="button"
    class="btn btn-sm btn-outline-danger hapus-produk">
    Hapus
</button>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>


<button
    type="button"
    id="tambah-produk"
    class="btn btn-outline-dark btn-sm mb-4">
    + Tambah Produk
</button>


<div class="mb-4">

<label class="form-label">
    Total Penjualan
</label>

<input
    type="text"
    id="total_harga"
    class="form-control fw-bold"
    value="Rp <?= number_format($row['total_harga'],0,',','.'); ?>"
    readonly
>

</div>


<button
    type="submit"
    name="update"
    class="btn-simpan">
    Simpan Perubahan
</button>

<a href="transaksi.php" class="btn-batal">
    Batal
</a>

</form>

</div>

</div>

</div>


<script>

function formatRupiah(angka){
    return 'Rp ' + angka.toLocaleString('id-ID');
}


function hitungTotal(){

    let total = 0;

    document.querySelectorAll('#produk-container tr').forEach(row => {

        let input = row.querySelector('.jumlah, .jumlah-baru');

        if (!input) return;

        let jumlah = parseInt(input.value) || 0;
        let harga = 0;

        if (input.classList.contains('jumlah-baru')) {

            let select = row.querySelector('.produk-baru');

            if (select && select.value) {
                harga = parseInt(
                    select.options[select.selectedIndex].dataset.harga
                ) || 0;
            }

        } else {
            harga = parseInt(input.dataset.harga) || 0;
        }

        let subtotal = harga * jumlah;
        total += subtotal;

        let subtotalEl = row.querySelector('.subtotal');

        if (subtotalEl) {
            subtotalEl.innerText = formatRupiah(subtotal);
        }
    });

    document.getElementById('total_harga').value =
        formatRupiah(total);
}


/* JUMLAH BERUBAH */

document.addEventListener('input', function(e){

    if (
        e.target.classList.contains('jumlah') ||
        e.target.classList.contains('jumlah-baru')
    ) {
        hitungTotal();
    }

});


/* HAPUS PRODUK */

document.addEventListener('click', function(e){

    if (e.target.classList.contains('hapus-produk')) {

        let row = e.target.closest('tr');
        let input = row.querySelector('.jumlah');

        if (input) {

            let match = input.name.match(/\[(.*?)\]/);

            if (match) {

                let hidden = document.createElement('input');

                hidden.type = 'hidden';
                hidden.name = 'hapus_produk[]';
                hidden.value = match[1];

                document.querySelector('form').appendChild(hidden);
            }
        }

        row.remove();
        hitungTotal();
    }


    if (e.target.classList.contains('hapus-baru')) {

        e.target.closest('tr').remove();
        hitungTotal();
    }

});


/* TAMBAH PRODUK */

document.getElementById('tambah-produk').addEventListener(
    'click',
    function(){

        let row = document.createElement('tr');

        row.innerHTML = `
            <td>
                <select name="produk_baru[]"
                        class="form-select produk-baru"
                        required>

                    <option value="">
                        -- Pilih Produk --
                    </option>

                    <?php
                    mysqli_data_seek($produk, 0);

                    while ($p = mysqli_fetch_assoc($produk)):
                    ?>

                    <option
                        value="<?= $p['id_produk']; ?>"
                        data-harga="<?= $p['harga']; ?>"
                        data-stok="<?= $p['stok']; ?>">

                        <?= htmlspecialchars($p['nama_produk']); ?>
                        - Rp <?= number_format($p['harga'],0,',','.'); ?>

                    </option>

                    <?php endwhile; ?>

                </select>
            </td>

            <td class="harga-baru">
                -
            </td>

            <td>
                <input
                    type="number"
                    name="jumlah_baru[]"
                    class="form-control jumlah-baru"
                    value="1"
                    min="1"
                    required>
            </td>

            <td class="subtotal">
                Rp 0
            </td>

            <td>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-danger hapus-baru">
                    Hapus
                </button>
            </td>
        `;

        document
            .getElementById('produk-container')
            .appendChild(row);
    }
);


/* PILIH PRODUK BARU */

document.addEventListener('change', function(e){

    if (!e.target.classList.contains('produk-baru')) {
        return;
    }

    let select = e.target;
    let row = select.closest('tr');
    let option = select.options[select.selectedIndex];

    let harga = parseInt(option.dataset.harga) || 0;
    let stok = parseInt(option.dataset.stok) || 0;

    row.querySelector('.harga-baru').innerText =
        harga ? formatRupiah(harga) : '-';

    row.querySelector('.jumlah-baru').max = stok;

    hitungTotal();

});


hitungTotal();

</script>

</body>
</html>