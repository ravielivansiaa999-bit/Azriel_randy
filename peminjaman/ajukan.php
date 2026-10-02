<?php
require '../config/database.php';
require '../includes/auth.php';
wajibRole(['siswa']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header("Location: ../buku/index.php");
exit;
}
$anggota = (int)$_SESSION['user']['anggota_id'];
$buku = (int)($_POST['buku_id'] ?? 0);
try {
// 1. Cek buku dan stok
$stmt = $conn->prepare("SELECT stok FROM buku WHERE id = ? AND deleted_at IS NULL");
$stmt->bind_param("i", $buku);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row || $row['stok'] < 1) throw new Exception("Buku tidak tersedia.");
// 2. Maksimal 3 aktif
$stmt = $conn->prepare("SELECT COUNT(*) FROM peminjaman
WHERE anggota_id = ? AND status IN ('diajukan','dipinjam')");
$stmt->bind_param("i", $anggota);
$stmt->execute();
if ($stmt->get_result()->fetch_row()[0] >= 3)
throw new Exception("Batas maksimal 3 buku aktif sudah tercapai.");
// 3. Tidak boleh dobel
$stmt = $conn->prepare("SELECT COUNT(*) FROM peminjaman
WHERE anggota_id = ? AND buku_id = ? AND status IN ('diajukan','dipinjam')");
$stmt->bind_param("ii", $anggota, $buku);
$stmt->execute();
if ($stmt->get_result()->fetch_row()[0] > 0)
throw new Exception("Anda sudah mengajukan/meminjam buku ini.");
// 4. Simpan pengajuan
$stmt = $conn->prepare("INSERT INTO peminjaman (anggota_id, buku_id) VALUES (?, ?)");
$stmt->bind_param("ii", $anggota, $buku);
$stmt->execute();
$_SESSION['pesan'] = "Pengajuan terkirim. Tunggu persetujuan petugas.";
} catch (mysqli_sql_exception $ex) {
$_SESSION['error'] = "Terjadi kesalahan database.";
} catch (Exception $ex) {
$_SESSION['error'] = $ex->getMessage();
}
header("Location: riwayat.php");