<?php

include '../koneksi.php';


// ===============================
// CEK ID PENJUALAN
// ===============================

if (!isset($_GET['id_penjualan']) || $_GET['id_penjualan'] == '') {

    die('ID penjualan tidak ditemukan.');

}

$id_penjualan = (int) $_GET['id_penjualan'];


// ===============================
// CEK DATA PENJUALAN
// ===============================

$cek = mysqli_query(
    $koneksi,
    "SELECT id_penjualan FROM penjualan
     WHERE id_penjualan = $id_penjualan"
);

if (!$cek || mysqli_num_rows($cek) == 0) {

    die('Data penjualan tidak ditemukan.');

}


// ===============================
// AMBIL DETAIL PRODUK
// UNTUK MENGEMBALIKAN STOK
// ===============================

$detail = mysqli_query(
    $koneksi,
    "SELECT id_produk, jumlah
     FROM detail_penjualan
     WHERE id_penjualan = $id_penjualan"
);


// ===============================
// MULAI TRANSAKSI DATABASE
// ===============================

mysqli_begin_transaction($koneksi);

try {


    // ===============================
    // KEMBALIKAN STOK PRODUK
    // ===============================

    while ($row = mysqli_fetch_assoc($detail)) {

        $id_produk = (int) $row['id_produk'];
        $jumlah = (int) $row['jumlah'];

        mysqli_query(
            $koneksi,
            "UPDATE produk
             SET stok = stok + $jumlah
             WHERE id_produk = $id_produk"
        );

    }


    // ===============================
    // HAPUS TRANSAKSI
    // ===============================

    $hapus_transaksi = mysqli_query(
        $koneksi,
        "DELETE FROM transaksi
         WHERE id_penjualan = $id_penjualan"
    );

    if (!$hapus_transaksi) {

        throw new Exception(
            'Gagal menghapus transaksi: '
            . mysqli_error($koneksi)
        );

    }


    // ===============================
    // HAPUS DETAIL PENJUALAN
    // ===============================

    $hapus_detail = mysqli_query(
        $koneksi,
        "DELETE FROM detail_penjualan
         WHERE id_penjualan = $id_penjualan"
    );

    if (!$hapus_detail) {

        throw new Exception(
            'Gagal menghapus detail penjualan: '
            . mysqli_error($koneksi)
        );

    }


    // ===============================
    // HAPUS PENJUALAN
    // ===============================

    $hapus_penjualan = mysqli_query(
        $koneksi,
        "DELETE FROM penjualan
         WHERE id_penjualan = $id_penjualan"
    );

    if (!$hapus_penjualan) {

        throw new Exception(
            'Gagal menghapus penjualan: '
            . mysqli_error($koneksi)
        );

    }


    // ===============================
    // SIMPAN PERUBAHAN
    // ===============================

    mysqli_commit($koneksi);


    // ===============================
    // KEMBALI KE HALAMAN PENJUALAN
    // ===============================

    header('Location: penjualan.php');
    exit;


} catch (Exception $e) {


    // ===============================
    // BATALKAN SEMUA PERUBAHAN
    // ===============================

    mysqli_rollback($koneksi);


    die(
        'Data penjualan gagal dihapus.<br><br>'
        . htmlspecialchars($e->getMessage())
    );

}

?>
