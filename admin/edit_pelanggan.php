<?php

include '../koneksi.php';

$id = $_GET['id'];

$data = mysqli_query(
    $koneksi,
    "SELECT * FROM pelanggan WHERE id_pelanggan = '$id'"
);

$row = mysqli_fetch_assoc($data);


if (isset($_POST['update'])) {

    $nama = $_POST['nama_pelanggan'];
    $alamat = $_POST['alamat'];
    $no_telepon = $_POST['no_telepon'];

    mysqli_query($koneksi, "UPDATE pelanggan SET
        nama_pelanggan = '$nama',
        alamat = '$alamat',
        no_telepon = '$no_telepon'
        WHERE id_pelanggan = '$id'
    ");

    echo "
        <script>
            alert('Data pelanggan berhasil diperbarui!');
            window.location='pelanggan.php';
        </script>
    ";

    exit;
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

    <title>Edit Pelanggan - Glowé Beauty</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<div class="container py-5">

    <div class="card border-0 shadow-sm">

        <div class="card-body p-4">

            <h3 class="fw-bold mb-4">
                Edit Pelanggan
            </h3>

            <form method="POST">

                <!-- NAMA -->

                <div class="mb-3">

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


                <!-- ALAMAT -->

                <div class="mb-3">

                    <label class="form-label">
                        Alamat
                    </label>

                    <input
                        type="text"
                        name="alamat"
                        class="form-control"
                        value="<?= htmlspecialchars($row['alamat']); ?>"
                        required
                    >

                </div>


                <!-- NO TELEPON -->

                <div class="mb-3">

                    <label class="form-label">
                        No. Telepon
                    </label>

                    <input
                        type="text"
                        name="no_telepon"
                        class="form-control"
                        value="<?= htmlspecialchars($row['no_telepon']); ?>"
                        required
                    >

                </div>


                <!-- TOMBOL -->

                <button
                    type="submit"
                    name="update"
                    class="btn btn-dark"
                >
                    Simpan Perubahan
                </button>

                <a
                    href="pelanggan.php"
                    class="btn btn-secondary"
                >
                    Batal
                </a>

            </form>

        </div>

    </div>

</div>

</body>

</html>