
<?php
require '../config/database.php';
require '../includes/auth.php';

wajibRole(['admin', 'petugas']);

$status = $_GET['status'] ?? 'diajukan';

if (!in_array($status, ['diajukan', 'dipinjam', 'dikembalikan', 'ditolak'], true)) {
    $status = 'diajukan';
}

// Ambil data peminjaman
$stmt = $conn->prepare(
    "SELECT p.*, a.nama, a.kelas, b.judul,
    (p.status = 'dipinjam' AND p.tgl_jatuh_tempo < CURDATE()) AS terlambat
    FROM peminjaman p
    JOIN anggota a ON a.id = p.anggota_id
    JOIN buku b ON b.id = p.buku_id
    WHERE p.status = ?
    ORDER BY p.id DESC"
);

$stmt->bind_param("s", $status);
$stmt->execute();

$data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

require '../includes/header.php';
?>

<style>
/* HALAMAN PEMINJAMAN */
.peminjaman {
    font-family: 'Segoe UI', Arial, sans-serif;
    color: #1e293b;
    padding: 10px 0 30px;
}

.peminjaman * {
    box-sizing: border-box;
}

/* HEADER */
.peminjaman-header {
    background: linear-gradient(120deg, #4338ca, #6366f1, #818cf8);
    color: white;
    padding: 30px;
    border-radius: 20px;
    margin-bottom: 25px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(79, 70, 229, .15);
}

.peminjaman-header::after {
    content: "📋";
    position: absolute;
    right: 35px;
    top: 10px;
    font-size: 95px;
    opacity: .15;
    transform: rotate(-12deg);
}

.peminjaman-header h2 {
    color: white;
    font-size: 27px;
    margin: 0 0 10px;
}

.peminjaman-header p {
    color: #e0e7ff;
    margin: 0;
    font-size: 14px;
}

/* STATUS TABS */
.status-card {
    background: white;
    padding: 20px;
    border-radius: 16px;
    border: 1px solid #edf0f7;
    box-shadow: 0 4px 15px rgba(15, 23, 42, .04);
    margin-bottom: 20px;
}

.status-title {
    font-size: 14px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 15px;
}

.status-tabs {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.status-tab {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 16px;
    border-radius: 10px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    transition: .2s;
}

.status-tab:hover {
    background: #eef2ff;
    color: #4f46e5;
    border-color: #c7d2fe;
}

.status-tab.active {
    background: #4f46e5;
    color: white;
    border-color: #4f46e5;
    box-shadow: 0 4px 12px rgba(79, 70, 229, .2);
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
}

/* TABLE CARD */
.peminjaman-table-card {
    background: white;
    border: 1px solid #edf0f7;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(15, 23, 42, .045);
}

.table-heading {
    padding: 20px 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid #edf0f7;
}

.table-heading h3 {
    margin: 0;
    font-size: 17px;
    color: #1e293b;
}

.total-badge {
    background: #eef2ff;
    color: #4f46e5;
    padding: 7px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
}

/* TABLE */
.table-responsive {
    width: 100%;
    overflow-x: auto;
}

.peminjaman table {
    width: 100%;
    min-width: 750px;
    border-collapse: collapse;
}

.peminjaman thead {
    background: #f8fafc;
}

.peminjaman th {
    padding: 15px 17px;
    text-align: left;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .4px;
    color: #64748b;
    font-weight: 700;
    border-bottom: 1px solid #e2e8f0;
}

.peminjaman td {
    padding: 16px 17px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
    color: #475569;
    vertical-align: middle;
}

.peminjaman tbody tr:nth-child(even) {
    background: #fafbff;
}

.peminjaman tbody tr:hover {
    background: #f1f5ff;
}

.peminjaman tbody tr:last-child td {
    border-bottom: none;
}

/* ANGGOTA */
.anggota-info {
    display: flex;
    align-items: center;
    gap: 11px;
}

.anggota-avatar {
    width: 38px;
    height: 38px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 11px;
    background: #e0e7ff;
    color: #4f46e5;
    font-size: 16px;
    font-weight: 700;
}

.anggota-nama {
    color: #1e293b;
    font-weight: 650;
    margin-bottom: 4px;
}

.anggota-kelas {
    color: #94a3b8;
    font-size: 11px;
}

/* BUKU */
.buku-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.buku-icon {
    width: 35px;
    height: 40px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #eef2ff;
    border-radius: 6px 10px 10px 6px;
    color: #6366f1;
    font-size: 17px;
}

.buku-judul {
    color: #334155;
    font-weight: 600;
    line-height: 1.5;
}

/* TANGGAL */
.tanggal {
    color: #475569;
    font-weight: 600;
    white-space: nowrap;
}

.tanggal-terlambat {
    color: #dc2626;
    font-weight: 700;
}

.terlambat-label {
    display: inline-block;
    background: #fee2e2;
    color: #dc2626;
    padding: 5px 8px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 800;
    margin-top: 7px;
}

/* DENDA */
.denda {
    color: #334155;
    font-weight: 700;
    white-space: nowrap;
}

.denda-positif {
    color: #dc2626;
}

/* BUTTON */
.aksi-peminjaman {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-wrap: wrap;
}

.aksi-peminjaman form {
    margin: 0;
}

.btn-peminjaman {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    border: none;
    padding: 9px 12px;
    border-radius: 9px;
    font-size: 12px;
    font-weight: 650;
    cursor: pointer;
    transition: .2s;
    white-space: nowrap;
}

.btn-peminjaman:hover {
    transform: translateY(-2px);
    filter: brightness(.96);
}

.btn-setujui {
    background: #dcfce7;
    color: #15803d;
}

.btn-tolak {
    background: #fee2e2;
    color: #dc2626;
}

.btn-kembali {
    background: #dbeafe;
    color: #1d4ed8;
}

/* EMPTY STATE */
.empty-state {
    padding: 60px 20px;
    text-align: center;
}

.empty-icon {
    font-size: 50px;
    margin-bottom: 15px;
}

.empty-state h3 {
    color: #334155;
    margin: 0 0 8px;
    font-size: 18px;
}

.empty-state p {
    color: #94a3b8;
    font-size: 13px;
    margin: 0;
}

/* RESPONSIVE */
@media (max-width: 650px) {
    .peminjaman-header {
        padding: 23px;
        border-radius: 15px;
    }

    .peminjaman-header h2 {
        font-size: 22px;
    }

    .peminjaman-header::after {
        font-size: 60px;
        right: 15px;
        top: 20px;
    }

    .status-card {
        padding: 15px;
    }

    .status-tabs {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .status-tab {
        justify-content: center;
        padding: 11px 8px;
        font-size: 12px;
    }

    .table-heading {
        padding: 16px;
    }
}
</style>

<div class="peminjaman">

    <!-- HEADER -->
    <div class="peminjaman-header">
        <h2>📋 Data Peminjaman</h2>
        <p>
            Kelola pengajuan, peminjaman, pengembalian,
            dan status transaksi buku perpustakaan.
        </p>
    </div>

    <!-- FILTER STATUS -->
    <div class="status-card">

        <div class="status-title">
            Filter Status Peminjaman
        </div>

        <div class="status-tabs">

            <?php
            $daftarStatus = [
                'diajukan' => 'Menunggu',
                'dipinjam' => 'Dipinjam',
                'dikembalikan' => 'Dikembalikan',
                'ditolak' => 'Ditolak'
            ];
            ?>

            <?php foreach ($daftarStatus as $s => $label): ?>

                <a
                    href="?status=<?= urlencode($s) ?>"
                    class="status-tab <?= $s === $status ? 'active' : '' ?>"
                >
                    <span class="status-dot"></span>
                    <?= $label ?>
                </a>

            <?php endforeach; ?>

        </div>

    </div>

    <!-- TABEL PEMINJAMAN -->
    <div class="peminjaman-table-card">

        <div class="table-heading">

            <h3>
                <?php
                $judulStatus = [
                    'diajukan' => 'Pengajuan Menunggu Persetujuan',
                    'dipinjam' => 'Buku Sedang Dipinjam',
                    'dikembalikan' => 'Riwayat Pengembalian',
                    'ditolak' => 'Pengajuan Ditolak'
                ];
                echo $judulStatus[$status];
                ?>
            </h3>

            <span class="total-badge">
                <?= count($data) ?> Transaksi
            </span>

        </div>

        <?php if (count($data) > 0): ?>

            <div class="table-responsive">

                <table>

                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Anggota</th>
                            <th>Buku</th>
                            <th>Jatuh Tempo</th>
                            <th>Denda</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($data as $index => $d): ?>

                            <?php
                            $terlambat = (bool)$d['terlambat'];
                            $denda = (float)($d['denda'] ?? 0);
                            ?>

                            <tr>

                                <td>
                                    <?= $index + 1 ?>
                                </td>

                                <!-- ANGGOTA -->
                                <td>
                                    <div class="anggota-info">

                                        <div class="anggota-avatar">
                                            <?= e(strtoupper(substr($d['nama'], 0, 1))) ?>
                                        </div>

                                        <div>
                                            <div class="anggota-nama">
                                                <?= e($d['nama']) ?>
                                            </div>

                                            <div class="anggota-kelas">
                                                Kelas <?= e($d['kelas']) ?>
                                            </div>
                                        </div>

                                    </div>
                                </td>

                                <!-- BUKU -->
                                <td>
                                    <div class="buku-info">

                                        <div class="buku-icon">
                                            📖
                                        </div>

                                        <div class="buku-judul">
                                            <?= e($d['judul']) ?>
                                        </div>

                                    </div>
                                </td>

                                <!-- JATUH TEMPO -->
                                <td>

                                    <div class="<?= $terlambat ? 'tanggal-terlambat' : 'tanggal' ?>">
                                        <?= e($d['tgl_jatuh_tempo'] ?? '-') ?>
                                    </div>

                                    <?php if ($terlambat): ?>
                                        <span class="terlambat-label">
                                            ⚠ TERLAMBAT
                                        </span>
                                    <?php endif; ?>

                                </td>

                                <!-- DENDA -->
                                <td>
                                    <span class="denda <?= $denda > 0 ? 'denda-positif' : '' ?>">
                                        Rp<?= number_format($denda, 0, ',', '.') ?>
                                    </span>
                                </td>

                                <!-- AKSI -->
                                <td>

                                    <div class="aksi-peminjaman">

                                        <form method="post" action="proses.php">

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int)$d['id'] ?>"
                                            >

                                            <?php if ($d['status'] === 'diajukan'): ?>

                                                <button
                                                    type="submit"
                                                    name="aksi"
                                                    value="setujui"
                                                    class="btn-peminjaman btn-setujui"
                                                >
                                                    ✓ Setujui
                                                </button>

                                                <button
                                                    type="submit"
                                                    name="aksi"
                                                    value="tolak"
                                                    class="btn-peminjaman btn-tolak"
                                                    onclick="return confirm('Yakin ingin menolak pengajuan ini?')"
                                                >
                                                    ✕ Tolak
                                                </button>

                                            <?php elseif ($d['status'] === 'dipinjam'): ?>

                                                <button
                                                    type="submit"
                                                    name="aksi"
                                                    value="kembali"
                                                    class="btn-peminjaman btn-kembali"
                                                    onclick="return confirm('Proses pengembalian buku ini?')"
                                                >
                                                    ↩ Kembalikan
                                                </button>

                                            <?php else: ?>

                                                <span style="color:#94a3b8;font-size:12px;">
                                                    Tidak ada aksi
                                                </span>

                                            <?php endif; ?>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <div class="empty-state">

                <div class="empty-icon">
                    <?php
                    echo match ($status) {
                        'diajukan' => '📭',
                        'dipinjam' => '📚',
                        'dikembalikan' => '✅',
                        'ditolak' => '📋',
                        default => '📭'
                    };
                    ?>
                </div>

                <h3>
                    Tidak Ada Data Peminjaman
                </h3>

                <p>
                    Belum ada transaksi dengan status
                    <?= htmlspecialchars($judulStatus[$status], ENT_QUOTES, 'UTF-8') ?>.
                </p>

            </div>

        <?php endif; ?>

    </div>

</div>

<?php require '../includes/footer.php'; ?>