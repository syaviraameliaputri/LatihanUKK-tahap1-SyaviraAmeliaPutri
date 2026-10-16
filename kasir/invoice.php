<?php
include '../koneksi.php';


// CEK ID PENJUALAN
if (!isset($_GET['id_penjualan'])) {
    header("Location: transaksi.php");
    exit;
}

$id_penjualan = $_GET['id_penjualan'];


// DATA PENJUALAN
$data = mysqli_query($koneksi, "
    SELECT
        penjualan.id_penjualan,
        penjualan.tanggal_penjualan,
        penjualan.total_harga,
        pelanggan.nama_pelanggan,
        pelanggan.alamat,
        pelanggan.no_telepon,
        transaksi.metode_pembayaran,
        transaksi.status
    FROM penjualan

    JOIN pelanggan
        ON penjualan.id_pelanggan = pelanggan.id_pelanggan

    LEFT JOIN transaksi
        ON penjualan.id_penjualan = transaksi.id_penjualan

    WHERE penjualan.id_penjualan = '$id_penjualan'
");


// AMBIL DATA PENJUALAN
$penjualan = mysqli_fetch_assoc($data);


// CEK DATA
if (!$penjualan) {

    echo "Data invoice tidak ditemukan.";
    exit;

}


// DATA DETAIL PRODUK
$detail = mysqli_query($koneksi, "
    SELECT
        produk.nama_produk,
        detail_penjualan.jumlah,
        detail_penjualan.harga,
        (
            detail_penjualan.jumlah *
            detail_penjualan.harga
        ) AS subtotal

    FROM detail_penjualan

    JOIN produk
        ON detail_penjualan.id_produk = produk.id_produk

    WHERE detail_penjualan.id_penjualan = '$id_penjualan'
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

    <title>Invoice - Glowé Beauty</title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {
            margin: 0;
            padding: 30px 0;
            background: #f1f1f1;
            font-family: Arial, sans-serif;
        }


        .struk {
            width: 320px;
            margin: auto;
            background: white;
            padding: 18px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.12);
        }


        .header {
            text-align: center;
            border-bottom: 1px dashed #555;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }


        .header h2 {
            margin: 0;
            font-size: 20px;
        }


        .header p {
            margin: 4px 0 0;
            font-size: 12px;
            color: #555;
        }


        .info {
            font-size: 12px;
            margin-bottom: 12px;
        }


        .info p {
            margin: 4px 0;
        }


        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }


        th {
            border-bottom: 1px dashed #555;
            padding-bottom: 6px;
        }


        td {
            padding: 6px 0;
            vertical-align: top;
        }


        .produk {
            width: 45%;
        }


        .qty {
            width: 15%;
            text-align: center;
        }


        .harga {
            width: 40%;
            text-align: right;
        }


        .total {
            border-top: 1px dashed #555;
            margin-top: 8px;
            padding-top: 10px;
            font-size: 13px;
        }


        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 7px;
        }


        .total-harga {
            font-size: 16px;
            font-weight: bold;
        }


        .footer {
            text-align: center;
            border-top: 1px dashed #555;
            margin-top: 12px;
            padding-top: 12px;
            font-size: 11px;
            color: #555;
        }


        .buttons {
            width: 320px;
            margin: 15px auto 0;
            text-align: center;
        }


        .buttons button,
        .buttons a {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            cursor: pointer;
        }


        .print {
            background: #212529;
            color: white;
            border: none;
        }


        .back {
            background: white;
            color: #212529;
            border: 1px solid #aaa;
        }


        @media print {

            @page {
                size: 80mm auto;
                margin: 0;
            }


            body {
                background: white;
                padding: 0;
            }


            .struk {
                width: 80mm;
                margin: 0;
                padding: 10mm 5mm;
                box-shadow: none;
            }


            .buttons {
                display: none;
            }

        }

    </style>

</head>


<body>


<div class="struk">


    <!-- HEADER -->

    <div class="header">

        <h2>
            Glowé Beauty
        </h2>


        <p>
            Beauty & Skincare
        </p>


        <p>
            Invoice Pembelian
        </p>

    </div>


    <!-- INFORMASI -->

    <div class="info">

        <p>

            <strong>No:</strong>

            INV-<?= str_pad(
                $penjualan['id_penjualan'],
                4,
                '0',
                STR_PAD_LEFT
            ); ?>

        </p>


        <p>

            <strong>Tanggal:</strong>

            <?= date(
                'd-m-Y',
                strtotime(
                    $penjualan['tanggal_penjualan']
                )
            ); ?>

        </p>


        <p>

            <strong>Pelanggan:</strong>

            <?= htmlspecialchars(
                $penjualan['nama_pelanggan']
            ); ?>

        </p>


        <p>

            <strong>No. Telepon:</strong>

            <?= htmlspecialchars(
                $penjualan['no_telepon']
            ); ?>

        </p>

    </div>


    <!-- PRODUK -->

    <table>


        <thead>

            <tr>

                <th class="produk">
                    Produk
                </th>


                <th class="qty">
                    Qty
                </th>


                <th class="harga">
                    Harga
                </th>

            </tr>

        </thead>


        <tbody>


        <?php

        if (mysqli_num_rows($detail) > 0) {

            while ($row = mysqli_fetch_assoc($detail)) {

        ?>

            <tr>


                <td class="produk">

                    <?= htmlspecialchars(
                        $row['nama_produk']
                    ); ?>

                </td>


                <td class="qty">

                    <?= $row['jumlah']; ?>

                </td>


                <td class="harga">

                    Rp <?= number_format(
                        $row['subtotal'],
                        0,
                        ',',
                        '.'
                    ); ?>

                </td>


            </tr>


        <?php

            }

        } else {

        ?>

            <tr>

                <td
                    colspan="3"
                    style="text-align:center;"
                >
                    Tidak ada produk.
                </td>

            </tr>

        <?php

        }

        ?>


        </tbody>

    </table>


    <!-- TOTAL -->

    <div class="total">


        <div class="total-row">

            <span>
                Total
            </span>


            <span class="total-harga">

                Rp <?= number_format(
                    $penjualan['total_harga'],
                    0,
                    ',',
                    '.'
                ); ?>

            </span>

        </div>


        <div class="total-row">

            <span>
                Pembayaran
            </span>


            <span>

                <?= htmlspecialchars(
                    $penjualan['metode_pembayaran'] ?? 'Tunai'
                ); ?>

            </span>

        </div>


        <div class="total-row">

            <span>
                Status
            </span>


            <span>

                <?php if (
                    $penjualan['status'] == 'sukses'
                ) { ?>

                    Sukses

                <?php } elseif (
                    $penjualan['status'] == 'dihapus'
                ) { ?>

                    Dihapus

                <?php } else { ?>

                    -

                <?php } ?>

            </span>

        </div>


    </div>


    <!-- FOOTER -->

    <div class="footer">

        <p>
            Terima kasih sudah berbelanja
        </p>


        <p>
            di Glowé Beauty ♡
        </p>

    </div>


</div>


<!-- BUTTON -->

<div class="buttons">


    <button
        onclick="window.print()"
        class="print"
    >
        Cetak Invoice
    </button>


    <a
        href="transaksi.php"
        class="back"
    >
        Kembali
    </a>


</div>


</body>

</html>