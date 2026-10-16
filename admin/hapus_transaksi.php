<?php

include '../koneksi.php';


// CEK ID TRANSAKSI
if (!isset($_GET['id_transaksi'])) {

    echo "<script>
        alert('ID transaksi tidak ditemukan!');
        window.location='transaksi.php';
    </script>";

    exit;
}


$id_transaksi = (int) $_GET['id_transaksi'];


// MULAI TRANSAKSI DATABASE
mysqli_begin_transaction($koneksi);


try {


    // =========================================
    // 1. AMBIL DATA TRANSAKSI
    // =========================================

    $query_transaksi = mysqli_query($koneksi, "
        SELECT
            id_transaksi,
            id_penjualan,
            status
        FROM transaksi
        WHERE id_transaksi = '$id_transaksi'
        FOR UPDATE
    ");


    if (
        !$query_transaksi ||
        mysqli_num_rows($query_transaksi) == 0
    ) {

        throw new Exception(
            'Transaksi tidak ditemukan.'
        );

    }


    $transaksi = mysqli_fetch_assoc(
        $query_transaksi
    );


    $id_penjualan = (int) $transaksi['id_penjualan'];
    $status = $transaksi['status'];


    // =========================================
    // 2. CEK STATUS
    // =========================================

    if ($status == 'dihapus') {

        throw new Exception(
            'Transaksi sudah dibatalkan sebelumnya.'
        );

    }


    // =========================================
    // 3. AMBIL DETAIL PRODUK
    // =========================================

    $query_detail = mysqli_query($koneksi, "
        SELECT
            id_produk,
            jumlah
        FROM detail_penjualan
        WHERE id_penjualan = '$id_penjualan'
        FOR UPDATE
    ");


    if (!$query_detail) {

        throw new Exception(
            'Gagal mengambil detail penjualan.'
        );

    }


    // =========================================
    // 4. KEMBALIKAN STOK
    // =========================================

    while (
        $detail = mysqli_fetch_assoc(
            $query_detail
        )
    ) {

        $id_produk = (int) $detail['id_produk'];
        $jumlah = (int) $detail['jumlah'];


        $update_stok = mysqli_query($koneksi, "
            UPDATE produk
            SET stok = stok + '$jumlah'
            WHERE id_produk = '$id_produk'
        ");


        if (!$update_stok) {

            throw new Exception(
                'Gagal mengembalikan stok produk.'
            );

        }

    }


    // =========================================
    // 5. UBAH STATUS TRANSAKSI
    // =========================================

    $update_status = mysqli_query($koneksi, "
        UPDATE transaksi
        SET status = 'dihapus'
        WHERE id_transaksi = '$id_transaksi'
    ");


    if (!$update_status) {

        throw new Exception(
            'Gagal mengubah status transaksi.'
        );

    }


    // =========================================
    // 6. SIMPAN PERUBAHAN
    // =========================================

    mysqli_commit($koneksi);


    echo "<script>

        alert(
            'Transaksi berhasil dibatalkan! Stok telah dikembalikan.'
        );

        window.location='transaksi.php';

    </script>";

    exit;


} catch (Exception $e) {


    // =========================================
    // JIKA ERROR
    // =========================================

    mysqli_rollback($koneksi);


    echo "<script>

        alert(
            'Transaksi gagal dibatalkan: "
            . addslashes($e->getMessage()) .
            "'
        );

        window.location='transaksi.php';

    </script>";

    exit;

}

?>