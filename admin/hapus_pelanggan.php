<?php

include '../koneksi.php';


// ===============================
// CEK ID PELANGGAN
// ===============================

if (!isset($_GET['id_pelanggan']) || $_GET['id_pelanggan'] == '') {

    die('ID pelanggan tidak ditemukan.');

}

$id_pelanggan = (int) $_GET['id_pelanggan'];


// ===============================
// CEK DATA PELANGGAN
// ===============================

$cek = mysqli_query(
    $koneksi,
    "SELECT id_pelanggan
     FROM pelanggan
     WHERE id_pelanggan = $id_pelanggan"
);

if (!$cek || mysqli_num_rows($cek) == 0) {

    die('Data pelanggan tidak ditemukan.');

}


// ===============================
// AMBIL SEMUA ID PENJUALAN
// MILIK PELANGGAN
// ===============================

$penjualan = mysqli_query(
    $koneksi,
    "SELECT id_penjualan
     FROM penjualan
     WHERE id_pelanggan = $id_pelanggan"
);


// ===============================
// MULAI TRANSAKSI DATABASE
// ===============================

mysqli_begin_transaction($koneksi);

try {


    // ===============================
    // HAPUS DATA BERDASARKAN
    // SETIAP PENJUALAN
    // ===============================

    while ($row = mysqli_fetch_assoc($penjualan)) {

        $id_penjualan = (int) $row['id_penjualan'];


        // ===============================
        // AMBIL DETAIL PRODUK
        // ===============================

        $detail = mysqli_query(
            $koneksi,
            "SELECT id_produk, jumlah
             FROM detail_penjualan
             WHERE id_penjualan = $id_penjualan"
        );


        // ===============================
        // KEMBALIKAN STOK
        // ===============================

        while ($item = mysqli_fetch_assoc($detail)) {

            $id_produk = (int) $item['id_produk'];
            $jumlah = (int) $item['jumlah'];

            $update_stok = mysqli_query(
                $koneksi,
                "UPDATE produk
                 SET stok = stok + $jumlah
                 WHERE id_produk = $id_produk"
            );

            if (!$update_stok) {

                throw new Exception(
                    'Gagal mengembalikan stok: '
                    . mysqli_error($koneksi)
                );

            }

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

    }


    // ===============================
    // HAPUS PELANGGAN
    // ===============================

    $hapus_pelanggan = mysqli_query(
        $koneksi,
        "DELETE FROM pelanggan
         WHERE id_pelanggan = $id_pelanggan"
    );

    if (!$hapus_pelanggan) {

        throw new Exception(
            'Gagal menghapus pelanggan: '
            . mysqli_error($koneksi)
        );

    }


    // ===============================
    // SIMPAN PERUBAHAN
    // ===============================

    mysqli_commit($koneksi);


    // ===============================
    // KEMBALI KE PELANGGAN
    // ===============================

    header('Location: pelanggan.php');
    exit;


} catch (Exception $e) {


    // ===============================
    // BATALKAN SEMUA PERUBAHAN
    // ===============================

    mysqli_rollback($koneksi);


    die(
        'Data pelanggan gagal dihapus.<br><br>'
        . htmlspecialchars($e->getMessage())
    );

}

?>
