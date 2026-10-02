<?php
require '../config/database.php';
require '../includes/auth.php';
wajibRole(['admin', 'petugas']);
$kategori = $conn->query("SELECT * FROM kategori ORDER BY nama")->fetch_all(MYSQLI_ASSOC);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$kode = trim($_POST['kode'] ?? '');
$judul = trim($_POST['judul'] ?? '');
$pengarang = trim($_POST['pengarang'] ?? '');
$kat = (int)($_POST['kategori_id'] ?? 0);
$stok = (int)($_POST['stok'] ?? 0);
if ($kode === '' || $judul === '' || $pengarang === '' || $kat < 1 || $stok < 0) {
$error = "Semua field wajib diisi dengan benar.";
} else {
try {
$stmt = $conn->prepare("INSERT INTO buku (kode, judul, pengarang, kategori_id, stok)
VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssii", $kode, $judul, $pengarang, $kat, $stok);
$stmt->execute();
$_SESSION['pesan'] = "Buku berhasil ditambahkan.";
header("Location: index.php");
exit;
} catch (mysqli_sql_exception $ex) {
$error = ($ex->getCode() == 1062) ? "Kode buku sudah dipakai." : "Gagal menyimpan data.";
}
}
}
require '../includes/header.php';
?>
<h2>Tambah Buku</h2>
<?php if ($error): ?><p class="err"><?= e($error) ?></p><?php endif; ?>
<form method="post">
<p>Kode <input name="kode" value="<?= e($_POST['kode'] ?? '') ?>"></p>
<p>Judul <input name="judul" value="<?= e($_POST['judul'] ?? '') ?>"></p>
<p>Pengarang <input name="pengarang" value="<?= e($_POST['pengarang'] ?? '') ?>"></p>
<p>Kategori
<select name="kategori_id">
<?php foreach ($kategori as $k): ?>
<option value="<?= $k['id'] ?>"><?= e($k['nama']) ?></option>
<?php endforeach; ?>
</select></p>
<p>Stok <input type="number" name="stok" min="0" value="1"></p>
<button>Simpan</button> <a href="index.php">Batal</a>
</form>
<?php require '../includes/footer.php';