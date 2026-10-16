<?php

session_start();

include 'koneksi.php';

$username = $_POST['username'];
$password = $_POST['password'];


// CEK ADMIN
$admin = mysqli_query($koneksi, "
    SELECT * FROM admin
    WHERE username = '$username'
    AND password = MD5('$password')
");

if (mysqli_num_rows($admin) > 0) {

    $data = mysqli_fetch_assoc($admin);

    $_SESSION['login'] = true;
    $_SESSION['tipe'] = 'admin';
    $_SESSION['username'] = $data['username'];

    header("Location: admin/index.php");
    exit;
}


// CEK KASIR
$kasir = mysqli_query($koneksi, "
    SELECT * FROM kasir
    WHERE username = '$username'
    AND password = MD5('$password')
");

if (mysqli_num_rows($kasir) > 0) {

    $data = mysqli_fetch_assoc($kasir);

    $_SESSION['login'] = true;
    $_SESSION['tipe'] = 'kasir';
    $_SESSION['username'] = $data['username'];

    header("Location: kasir/index.php");
    exit;
}


// LOGIN GAGAL
header("Location: login.php?pesan=gagal");
exit;

?>