<?php
include '../koneksi.php';

/* =====================================================
   SIMPAN TRANSAKSI
===================================================== */
if (isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_pelanggan']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $no_telepon = mysqli_real_escape_string($koneksi, $_POST['no_telepon']);

    if (empty($_POST['id_produk'])) {
        echo "<script>alert('Silakan pilih minimal satu produk!');location='pelanggan.php';</script>";
        exit;
    }

    mysqli_query($koneksi, "INSERT INTO pelanggan (nama_pelanggan,alamat,no_telepon)
        VALUES ('$nama','$alamat','$no_telepon')")
        or die("Gagal menyimpan pelanggan: ".mysqli_error($koneksi));

    $id_pelanggan = mysqli_insert_id($koneksi);

    mysqli_query($koneksi, "INSERT INTO penjualan (id_pelanggan,tanggal_penjualan,total_harga)
        VALUES ('$id_pelanggan',CURDATE(),0)")
        or die("Gagal menyimpan penjualan: ".mysqli_error($koneksi));

    $id_penjualan = mysqli_insert_id($koneksi);
    $total = 0;
    $ada_produk = false;

    foreach ($_POST['id_produk'] as $key => $id_produk) {
        $id_produk = (int)$id_produk;
        $jumlah = isset($_POST['jumlah'][$key]) ? (int)$_POST['jumlah'][$key] : 0;

        if ($id_produk <= 0 || $jumlah <= 0) continue;

        $q = mysqli_query($koneksi, "SELECT * FROM produk WHERE id_produk='$id_produk'");
        $pr = mysqli_fetch_assoc($q);

        if (!$pr) {
            echo "<script>alert('Produk tidak ditemukan!');location='pelanggan.php';</script>";
            exit;
        }

        if ($jumlah > $pr['stok']) {
            echo "<script>alert('Stok {$pr['nama_produk']} tidak mencukupi! Stok tersedia: {$pr['stok']}');location='pelanggan.php';</script>";
            exit;
        }

        $harga = (int)$pr['harga'];
        $total += $harga * $jumlah;
        $ada_produk = true;

        mysqli_query($koneksi, "INSERT INTO detail_penjualan
            (id_penjualan,id_produk,jumlah,harga)
            VALUES ('$id_penjualan','$id_produk','$jumlah','$harga')")
            or die("Gagal menyimpan detail produk: ".mysqli_error($koneksi));

        mysqli_query($koneksi, "UPDATE produk SET stok=stok-$jumlah
            WHERE id_produk='$id_produk'");
    }

    if (!$ada_produk) {
        echo "<script>alert('Silakan pilih produk terlebih dahulu!');location='pelanggan.php';</script>";
        exit;
    }

    mysqli_query($koneksi, "UPDATE penjualan SET total_harga='$total'
        WHERE id_penjualan='$id_penjualan'");

    mysqli_query($koneksi, "INSERT INTO transaksi
        (id_penjualan,metode_pembayaran,status)
        VALUES ('$id_penjualan','Tunai','sukses')")
        or die("Transaksi gagal: ".mysqli_error($koneksi));

    echo "<script>alert('Transaksi berhasil disimpan!');location='pelanggan.php';</script>";
    exit;
}

/* =====================================================
   CARI PELANGGAN
===================================================== */
$keyword = isset($_GET['cari'])
    ? mysqli_real_escape_string($koneksi, $_GET['cari'])
    : '';

/* =====================================================
   DATA PRODUK
===================================================== */
$produk = mysqli_query($koneksi, "SELECT id_produk,nama_produk,harga,stok
    FROM produk ORDER BY nama_produk ASC");

/* =====================================================
   DATA PELANGGAN
===================================================== */
$data = mysqli_query($koneksi, "
    SELECT pelanggan.*,
        COALESCE(SUM(
            CASE WHEN transaksi.status='sukses'
            THEN penjualan.total_harga ELSE 0 END
        ),0) AS total_belanja,
        COALESCE(SUM(
            CASE WHEN transaksi.status='sukses'
            THEN detail_sum.jumlah ELSE 0 END
        ),0) AS jumlah
    FROM pelanggan
    LEFT JOIN penjualan
        ON pelanggan.id_pelanggan=penjualan.id_pelanggan
    LEFT JOIN transaksi
        ON penjualan.id_penjualan=transaksi.id_penjualan
    LEFT JOIN (
        SELECT id_penjualan,SUM(jumlah) AS jumlah
        FROM detail_penjualan
        GROUP BY id_penjualan
    ) detail_sum
        ON penjualan.id_penjualan=detail_sum.id_penjualan
    WHERE (
        pelanggan.nama_pelanggan LIKE '%$keyword%'
        OR pelanggan.alamat LIKE '%$keyword%'
        OR pelanggan.no_telepon LIKE '%$keyword%'
    )
    GROUP BY pelanggan.id_pelanggan
    ORDER BY pelanggan.id_pelanggan DESC
");

$total_pelanggan = mysqli_num_rows($data);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Pelanggan — Glowé Beauty</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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
*{box-sizing:border-box}
body{
    margin:0;
    background:var(--ivory);
    color:var(--text);
    font-family:"Segoe UI",Arial,sans-serif;
}
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
.nav-link-custom.active{color:var(--green)}
.nav-link-custom.active{font-weight:700}
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
.page{
    padding:65px 0 80px;
}
.eyebrow,.section-label{
    color:var(--mustard);
    font-size:10px;
    font-weight:700;
    letter-spacing:2.5px;
    text-transform:uppercase;
}
.page-title{
    color:var(--outerspace);
    font:600 42px Georgia,serif;
    letter-spacing:-1px;
    margin:8px 0;
}
.page-desc{
    color:var(--muted);
    font-size:14px;
}
.card-custom{
    background:white;
    border:1px solid var(--line);
    border-radius:0;
}
.card-title{
    color:var(--outerspace);
    font-size:22px;
    font-weight:600;
}
.form-label{
    color:var(--outerspace);
    font-size:13px;
    font-weight:600;
}
.form-control{
    border-color:var(--line);
    border-radius:4px;
}
.form-control:focus{
    border-color:var(--green);
    box-shadow:0 0 0 2px rgba(0,96,56,.08);
}
.btn-green{
    background:var(--green);
    border:1px solid var(--green);
    color:white;
    border-radius:4px;
    font-size:13px;
    font-weight:600;
}
.btn-green:hover{
    background:var(--green-dark);
    color:white;
}
.btn-outline-green{
    color:var(--green);
    border:1px solid #B8C8C0;
    background:white;
}
.btn-outline-green:hover{
    background:var(--green);
    color:white;
}
.stat-box{
    background:var(--peach);
    border:0;
    padding:25px;
}
.stat-number{
    color:var(--outerspace);
    font:600 32px Georgia,serif;
}
.search-produk{
    height:46px;
    border-radius:4px;
}
.produk-card{
    height:100%;
    padding:20px;
    background:white;
    border:1px solid var(--line);
    cursor:pointer;
    transition:.2s;
}
.produk-card:hover{
    border-color:var(--green);
    transform:translateY(-2px);
}
.produk-card.tidak-tersedia{
    opacity:.45;
    cursor:not-allowed;
}
.produk-icon{
    width:42px;
    height:42px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:var(--peach);
    color:var(--green);
    font:600 18px Georgia,serif;
    margin-bottom:14px;
}
.nama-produk{
    color:var(--outerspace);
    font-size:14px;
    font-weight:600;
}
.harga-produk{
    color:var(--green);
    font-size:14px;
    font-weight:700;
    margin-top:6px;
}
.stok-produk{
    color:var(--muted);
    font-size:11px;
    margin-top:4px;
}
.pesanan-row{
    background:#FCFBF8;
    border:1px solid var(--line);
    padding:14px;
    margin-bottom:8px;
}
.qty-box{
    display:flex;
    align-items:center;
    gap:6px;
}
.qty-btn,.qty-input{
    width:34px;
    height:34px;
    border:1px solid var(--line);
    background:white;
    text-align:center;
}
.qty-input{border-radius:3px}
.qty-btn:hover{
    background:var(--green);
    color:white;
}
.total-box{
    background:var(--outerspace);
    color:white;
    padding:22px;
}
.total-label{font-size:12px;letter-spacing:1px}
.total-price{
    font:600 25px Georgia,serif;
}
.table{
    --bs-table-bg:transparent;
}
.table thead th{
    color:var(--mustard);
    font-size:10px;
    text-transform:uppercase;
    letter-spacing:1px;
    border-bottom:1px solid var(--line);
}
.table tbody td{
    color:var(--text);
    font-size:13px;
    border-color:var(--line);
}
.not-found{
    display:none;
    color:var(--muted);
    text-align:center;
    padding:25px;
    border:1px dashed var(--line);
}
footer{
    background:var(--outerspace);
    color:#AAB9BD;
    padding:25px 0;
}
.footer-brand{
    color:var(--peach);
    font-weight:700;
}
.footer-text{font-size:11px;margin-top:4px}

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
    .nav-link-custom{padding:9px 11px}
    .nav-link-custom.active:after{display:none}
    .logout{margin-left:5px}
}
@media(max-width:576px){
    .page-title{font-size:34px}
}
</style>
</head>

<body>

<!-- NAVBAR -->
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
    <a href="pelanggan.php" class="nav-link-custom active">Pelanggan</a>
    <a href="produk.php" class="nav-link-custom">Produk</a>
    <a href="penjualan.php" class="nav-link-custom">Penjualan</a>
    <a href="transaksi.php" class="nav-link-custom">Transaksi</a>
    <a href="laporan.php" class="nav-link-custom">Laporan</a>
    <a href="../logout.php" class="logout"
       onclick="return confirm('Yakin ingin logout?')">Logout</a>
</div>

</div>
</div>
</nav>

<!-- CONTENT -->
<main class="page">
<div class="container">

<div class="mb-5">
    <div class="eyebrow">Glowé Beauty / Customer</div>
    <h1 class="page-title">Pelanggan</h1>
    <p class="page-desc mb-0">
        Tambahkan pelanggan sekaligus melakukan pembelian.
    </p>
</div>

<!-- TOTAL -->
<div class="stat-box mb-4">
    <div class="section-label">Total Pelanggan</div>
    <div class="stat-number"><?= $total_pelanggan; ?></div>
</div>

<!-- FORM -->
<div class="card-custom p-4 p-lg-5 mb-5">

<div class="mb-4">
    <div class="section-label">New Customer</div>
    <div class="card-title mt-1">Tambah Pelanggan & Pembelian</div>
    <p class="text-secondary small mb-0">
        Isi data pelanggan, lalu klik produk yang ingin dibeli.
    </p>
</div>

<form method="POST">

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Nama Pelanggan</label>
        <input type="text" name="nama_pelanggan" class="form-control"
               placeholder="Masukkan nama" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Alamat</label>
        <input type="text" name="alamat" class="form-control"
               placeholder="Masukkan alamat" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">No. Telepon</label>
        <input type="text" name="no_telepon" class="form-control"
               placeholder="Masukkan nomor telepon" required>
    </div>
</div>

<hr class="my-4">

<div class="mb-3">
    <div class="card-title fs-5 mb-1">Pilih Produk</div>
    <small class="text-secondary">
        Cari produk lalu klik kartu produk untuk menambahkannya.
    </small>
</div>

<input type="text" id="cari-produk" class="form-control search-produk mb-3"
       placeholder="Cari produk..." autocomplete="off">

<div class="row g-3" id="daftar-produk">
<?php while($pr=mysqli_fetch_assoc($produk)):
    $stok=(int)$pr['stok'];
?>
<div class="col-6 col-md-4 col-lg-3 produk-item"
     data-nama-produk="<?= strtolower(htmlspecialchars($pr['nama_produk'],ENT_QUOTES)); ?>">

    <div class="produk-card <?= $stok<=0?'tidak-tersedia':''; ?>"
         data-id="<?= $pr['id_produk']; ?>"
         data-nama="<?= htmlspecialchars($pr['nama_produk'],ENT_QUOTES); ?>"
         data-harga="<?= $pr['harga']; ?>"
         data-stok="<?= $stok; ?>"
         onclick="pilihProduk(this)">

        <div class="produk-icon">
            <?= strtoupper(substr($pr['nama_produk'],0,1)); ?>
        </div>

        <div class="nama-produk">
            <?= htmlspecialchars($pr['nama_produk']); ?>
        </div>

        <div class="harga-produk">
            Rp <?= number_format($pr['harga'],0,',','.'); ?>
        </div>

        <div class="stok-produk">
            <?= $stok>0 ? "Stok: $stok" : "Stok habis"; ?>
        </div>
    </div>
</div>
<?php endwhile; ?>
</div>

<div id="produk-tidak-ditemukan" class="not-found mt-3">
    Produk tidak ditemukan.
</div>

<div class="mt-5">
    <div class="card-title fs-5 mb-3">Pesanan</div>

    <div id="pesanan-container">
        <div id="pesanan-kosong"
             class="text-center text-secondary border p-4">
            Belum ada produk yang dipilih.
        </div>
    </div>
</div>

<div class="total-box mt-4 d-flex justify-content-between align-items-center">
    <div class="total-label">TOTAL HARGA</div>
    <div class="total-price" id="total-harga">Rp 0</div>
</div>

<div class="mt-3 small text-secondary">
    Pembayaran: <strong>Tunai</strong>
    &nbsp;•&nbsp;
    Status: <strong>Sukses</strong>
</div>

<button type="submit" name="simpan" class="btn btn-green mt-3 px-4 py-2">
    Simpan Transaksi
</button>

</form>
</div>

<!-- DAFTAR PELANGGAN -->
<div class="card-custom p-4 p-lg-5">

<div class="mb-4">
    <div class="section-label">Customer List</div>
    <div class="card-title mt-1">Daftar Pelanggan</div>
    <p class="text-secondary small mb-0">
        Data pelanggan dan total pembelian.
    </p>
</div>

<form method="GET" class="mb-4">
<div class="input-group">
    <input type="text" name="cari" class="form-control"
           placeholder="Cari nama, alamat, atau nomor telepon..."
           value="<?= htmlspecialchars($keyword); ?>">
    <button class="btn btn-green">Cari</button>

    <?php if($keyword!=""): ?>
        <a href="pelanggan.php" class="btn btn-outline-green">Reset</a>
    <?php endif; ?>
</div>
</form>

<div class="table-responsive">
<table class="table table-hover align-middle">
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
$no=1;
if(mysqli_num_rows($data)>0):
while($row=mysqli_fetch_assoc($data)):
?>
<tr>
    <td><?= $no++; ?></td>

    <td class="fw-semibold">
        <?= htmlspecialchars($row['nama_pelanggan']); ?>
    </td>

    <td><?= htmlspecialchars($row['alamat']); ?></td>

    <td><?= htmlspecialchars($row['no_telepon']); ?></td>

    <td><?= $row['jumlah']; ?> item</td>

    <td class="fw-semibold">
        Rp <?= number_format($row['total_belanja'],0,',','.'); ?>
    </td>

    <td>
        <div class="d-flex gap-1">
            <a href="transaksi.php?id_pelanggan=<?= $row['id_pelanggan']; ?>"
               class="btn btn-sm btn-green">
                Transaksi
            </a>

            <a href="hapus_pelanggan.php?id_pelanggan=<?= $row['id_pelanggan']; ?>"
               class="btn btn-sm btn-outline-danger"
               onclick="return confirm('Yakin ingin menghapus pelanggan ini?')">
                Hapus
            </a>
        </div>
    </td>
</tr>

<?php endwhile; else: ?>

<tr>
<td colspan="7" class="text-center text-muted py-4">
    <?= $keyword ? 'Pelanggan tidak ditemukan.' : 'Belum ada pelanggan.'; ?>
</td>
</tr>

<?php endif; ?>
</tbody>
</table>
</div>

</div>
</div>
</main>

<footer>
<div class="container text-center">
    <div class="footer-brand">Glowé Beauty</div>
    <div class="footer-text">Beauty Store Management System</div>
</div>
</footer>

<script>
let pesanan={};

const rupiah=n=>'Rp '+Number(n).toLocaleString('id-ID');

document.getElementById('cari-produk').addEventListener('input',function(){
    let key=this.value.toLowerCase().trim(), tampil=0;

    document.querySelectorAll('.produk-item').forEach(item=>{
        let cocok=item.dataset.namaProduk.includes(key);
        item.style.display=cocok?'':'none';
        if(cocok)tampil++;
    });

    document.getElementById('produk-tidak-ditemukan').style.display=
        tampil?'none':'block';
});

function pilihProduk(card){
    let id=card.dataset.id,
        nama=card.dataset.nama,
        harga=parseInt(card.dataset.harga),
        stok=parseInt(card.dataset.stok);

    if(stok<=0){
        alert('Produk '+nama+' sedang habis.');
        return;
    }

    if(pesanan[id]){
        if(pesanan[id].jumlah>=stok){
            alert('Jumlah '+nama+' sudah mencapai stok yang tersedia.');
            return;
        }
        pesanan[id].jumlah++;
    }else{
        pesanan[id]={id,nama,harga,stok,jumlah:1};
    }

    tampilkanPesanan();
}

function tampilkanPesanan(){
    let box=document.getElementById('pesanan-container'),
        ids=Object.keys(pesanan);

    if(!ids.length){
        box.innerHTML=`
            <div class="text-center text-secondary border p-4">
                Belum ada produk yang dipilih.
            </div>`;
        hitungTotal();
        return;
    }

    box.innerHTML='';

    ids.forEach(id=>{
        let x=pesanan[id], row=document.createElement('div');
        row.className='pesanan-row';

        row.innerHTML=`
            <input type="hidden" name="id_produk[]" value="${x.id}">
            <input type="hidden" name="jumlah[]" value="${x.jumlah}">

            <div class="row align-items-center g-2">
                <div class="col-md-5">
                    <div class="fw-semibold">${x.nama}</div>
                    <small class="text-secondary">
                        ${rupiah(x.harga)} / item
                    </small>
                </div>

                <div class="col-md-4">
                    <div class="qty-box">
                        <button type="button" class="qty-btn"
                            onclick="kurangiProduk('${x.id}')">−</button>

                        <input class="qty-input" value="${x.jumlah}" readonly>

                        <button type="button" class="qty-btn"
                            onclick="tambahJumlah('${x.id}')">+</button>

                        <small class="text-secondary ms-2">
                            Stok ${x.stok}
                        </small>
                    </div>
                </div>

                <div class="col-md-2 text-md-end fw-semibold">
                    ${rupiah(x.harga*x.jumlah)}
                </div>

                <div class="col-md-1 text-end">
                    <button type="button"
                        class="btn btn-sm btn-outline-danger"
                        onclick="hapusProduk('${x.id}')">×</button>
                </div>
            </div>`;
        box.appendChild(row);
    });

    hitungTotal();
}

function tambahJumlah(id){
    if(!pesanan[id])return;

    if(pesanan[id].jumlah>=pesanan[id].stok){
        alert('Jumlah sudah mencapai stok yang tersedia.');
        return;
    }

    pesanan[id].jumlah++;
    tampilkanPesanan();
}

function kurangiProduk(id){
    if(!pesanan[id])return;

    pesanan[id].jumlah--;

    if(pesanan[id].jumlah<=0)
        delete pesanan[id];

    tampilkanPesanan();
}

function hapusProduk(id){
    delete pesanan[id];
    tampilkanPesanan();
}

function hitungTotal(){
    let total=0;

    Object.values(pesanan).forEach(x=>{
        total+=x.harga*x.jumlah;
    });

    document.getElementById('total-harga').innerText=rupiah(total);
}

document.querySelector('form[method="POST"]').addEventListener('submit',function(e){
    if(!Object.keys(pesanan).length){
        e.preventDefault();
        alert('Silakan pilih minimal satu produk!');
    }
});
</script>

</body>
</html>