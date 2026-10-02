
<?php
require '../config/database.php';
require '../includes/auth.php';

wajibLogin();

$cari = trim($_GET['cari'] ?? '');
$halaman = max(1, (int)($_GET['hal'] ?? 1));
$perHal = 5;
$offset = ($halaman - 1) * $perHal;
$like = "%$cari%";

// Hitung total buku
$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM buku
    WHERE deleted_at IS NULL
    AND (judul LIKE ? OR kode LIKE ?)
");

$stmt->bind_param("ss", $like, $like);
$stmt->execute();

$totalData = (int)$stmt->get_result()->fetch_row()[0];
$totalHal = max(1, (int)ceil($totalData / $perHal));

// Ambil data buku
$stmt = $conn->prepare("
    SELECT b.*, k.nama AS kategori
    FROM buku b
    JOIN kategori k ON k.id = b.kategori_id
    WHERE b.deleted_at IS NULL
    AND (b.judul LIKE ? OR b.kode LIKE ?)
    ORDER BY b.judul
    LIMIT ? OFFSET ?
");

$stmt->bind_param("ssii", $like, $like, $perHal, $offset);
$stmt->execute();

$dataBuku = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

require '../includes/header.php';
?>

<style>
/* KATALOG BUKU */
.katalog {
    font-family: 'Segoe UI', Arial, sans-serif;
    color: #1e293b;
    padding: 10px 0 30px;
}

.katalog * {
    box-sizing: border-box;
}

/* HEADER */
.katalog-header {
    background: linear-gradient(120deg, #4338ca, #6366f1, #818cf8);
    color: white;
    padding: 30px;
    border-radius: 20px;
    margin-bottom: 25px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(79, 70, 229, .15);
}

.katalog-header::after {
    content: "📚";
    position: absolute;
    right: 35px;
    top: 10px;
    font-size: 95px;
    opacity: .15;
    transform: rotate(-15deg);
}

.katalog-header h2 {
    color: white;
    font-size: 27px;
    margin: 0 0 10px;
}

.katalog-header p {
    color: #e0e7ff;
    margin: 0;
    font-size: 14px;
}

/* TOOLBAR */
.katalog-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
    background: white;
    padding: 20px;
    border-radius: 16px;
    border: 1px solid #edf0f7;
    box-shadow: 0 4px 15px rgba(15, 23, 42, .04);
    margin-bottom: 20px;
}

.katalog-search {
    display: flex;
    gap: 10px;
    flex: 1;
    min-width: 250px;
}

.katalog-search input {
    flex: 1;
    min-width: 0;
    padding: 12px 15px;
    border: 1px solid #dbe2ef;
    border-radius: 10px;
    font-size: 14px;
    outline: none;
    transition: .2s;
    background: #f8fafc;
}

.katalog-search input:focus {
    border-color: #6366f1;
    background: white;
    box-shadow: 0 0 0 3px rgba(99,102,241,.12);
}

/* BUTTON */
.btn-katalog {
    border: none;
    padding: 11px 17px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    transition: .2s;
    white-space: nowrap;
}

.btn-katalog:hover {
    transform: translateY(-2px);
    filter: brightness(.96);
}

.btn-cari {
    background: #4f46e5;
    color: white;
}

.btn-tambah {
    background: #16a34a;
    color: white;
}

.btn-pinjam {
    background: #2563eb;
    color: white;
}

.btn-hapus {
    background: #fee2e2;
    color: #dc2626;
}

.btn-pinjam:disabled {
    background: #cbd5e1;
    color: #64748b;
    cursor: not-allowed;
    transform: none;
}

/* TABLE CARD */
.katalog-table-card {
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
    gap: 10px;
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

.katalog table {
    width: 100%;
    border-collapse: collapse;
    min-width: 720px;
}

.katalog thead {
    background: #f8fafc;
}

.katalog th {
    padding: 15px 17px;
    text-align: left;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #64748b;
    font-weight: 700;
    border-bottom: 1px solid #e2e8f0;
}

.katalog td {
    padding: 15px 17px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
    color: #475569;
    vertical-align: middle;
}

.katalog tbody tr {
    transition: background .2s;
}

.katalog tbody tr:nth-child(even) {
    background: #fafbff;
}

.katalog tbody tr:hover {
    background: #f1f5ff;
}

.katalog tbody tr:last-child td {
    border-bottom: none;
}

.kode-buku {
    display: inline-block;
    background: #eef2ff;
    color: #4f46e5;
    padding: 6px 9px;
    border-radius: 7px;
    font-size: 12px;
    font-weight: 700;
}

.judul-buku {
    font-weight: 650;
    color: #1e293b;
}

.kategori-badge {
    display: inline-block;
    background: #f1f5f9;
    color: #475569;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

/* STOK */
.stok-badge {
    display: inline-block;
    min-width: 38px;
    text-align: center;
    padding: 6px 10px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 12px;
}

.stok-tersedia {
    background: #dcfce7;
    color: #15803d;
}

.stok-menipis {
    background: #fef3c7;
    color: #b45309;
}

.stok-habis {
    background: #fee2e2;
    color: #dc2626;
}

/* AKSI */
.aksi-buku {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-wrap: wrap;
}

.aksi-buku form {
    margin: 0;
}

.aksi-buku .btn-katalog {
    padding: 8px 11px;
    font-size: 12px;
}

/* EMPTY STATE */
.empty-state {
    text-align: center;
    padding: 55px 20px;
    color: #64748b;
}

.empty-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.empty-state h3 {
    color: #334155;
    margin: 0 0 8px;
}

.empty-state p {
    font-size: 13px;
    margin: 0;
}

/* PAGINATION */
.katalog-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    flex-wrap: wrap;
    padding: 20px 22px;
    border-top: 1px solid #edf0f7;
}

.pagination-info {
    font-size: 12px;
    color: #64748b;
}

.pagination-links {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.page-link {
    min-width: 35px;
    height: 35px;
    padding: 0 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    text-decoration: none;
    color: #475569;
    font-size: 13px;
    font-weight: 600;
    background: white;
    transition: .2s;
}

.page-link:hover {
    background: #eef2ff;
    border-color: #a5b4fc;
    color: #4f46e5;
}

.page-link.active {
    background: #4f46e5;
    color: white;
    border-color: #4f46e5;
}

/* RESPONSIVE */
@media (max-width: 650px) {
    .katalog-header {
        padding: 23px;
        border-radius: 15px;
    }

    .katalog-header h2 {
        font-size: 22px;
    }

    .katalog-header::after {
        font-size: 60px;
        right: 15px;
        top: 20px;
    }

    .katalog-toolbar {
        padding: 15px;
        align-items: stretch;
    }

    .katalog-search {
        min-width: 100%;
    }

    .btn-tambah {
        width: 100%;
    }

    .table-heading {
        padding: 16px;
    }

    .katalog-pagination {
        padding: 16px;
        justify-content: center;
    }

    .pagination-info {
        width: 100%;
        text-align: center;
    }
}
</style>

<div class="katalog">

    <!-- HEADER -->
    <div class="katalog-header">
        <h2>📚 Katalog Buku</h2>
        <p>
            Temukan koleksi buku favoritmu dan jelajahi
            berbagai kategori bacaan di perpustakaan.
        </p>
    </div>

    <!-- PENCARIAN DAN TOMBOL TAMBAH -->
    <div class="katalog-toolbar">

        <form method="get" class="katalog-search">
            <input
                type="text"
                name="cari"
                value="<?= e($cari) ?>"
                placeholder="🔍 Cari judul atau kode buku..."
            >

            <button type="submit" class="btn-katalog btn-cari">
                Cari Buku
            </button>
        </form>

        <?php if (in_array($role, ['admin', 'petugas'])): ?>
            <a href="tambah.php" class="btn-katalog btn-tambah">
                ＋ Tambah Buku
            </a>
        <?php endif; ?>

    </div>

    <!-- TABEL BUKU -->
    <div class="katalog-table-card">

        <div class="table-heading">
            <h3>Daftar Koleksi Buku</h3>

            <span class="total-badge">
                <?= $totalData ?> Buku
            </span>
        </div>

        <?php if (count($dataBuku) > 0): ?>

            <div class="table-responsive">

                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Kode Buku</th>
                            <th>Judul Buku</th>
                            <th>Pengarang</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($dataBuku as $index => $b): ?>

                            <?php
                            $stok = (int)$b['stok'];

                            if ($stok < 1) {
                                $kelasStok = 'stok-habis';
                                $labelStok = 'Habis';
                            } elseif ($stok <= 3) {
                                $kelasStok = 'stok-menipis';
                                $labelStok = $stok;
                            } else {
                                $kelasStok = 'stok-tersedia';
                                $labelStok = $stok;
                            }
                            ?>

                            <tr>

                                <td>
                                    <?= $offset + $index + 1 ?>
                                </td>

                                <td>
                                    <span class="kode-buku">
                                        <?= e($b['kode']) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="judul-buku">
                                        <?= e($b['judul']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= e($b['pengarang']) ?>
                                </td>

                                <td>
                                    <span class="kategori-badge">
                                        <?= e($b['kategori']) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="stok-badge <?= $kelasStok ?>">
                                        <?= $labelStok ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="aksi-buku">

                                        <?php if ($role === 'siswa'): ?>

                                            <form
                                                method="post"
                                                action="../peminjaman/ajukan.php"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="buku_id"
                                                    value="<?= (int)$b['id'] ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn-katalog btn-pinjam"
                                                    <?= $stok < 1 ? 'disabled' : '' ?>
                                                >
                                                    📖 Ajukan
                                                </button>
                                            </form>

                                        <?php endif; ?>

                                        <?php if ($role === 'admin'): ?>

                                            <form
                                                method="post"
                                                action="hapus.php"
                                                onsubmit="return confirm('Yakin ingin menghapus buku ini?')"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= (int)$b['id'] ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn-katalog btn-hapus"
                                                >
                                                    🗑 Hapus
                                                </button>
                                            </form>

                                        <?php endif; ?>

                                    </div>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>
                </table>

            </div>

        <?php else: ?>

            <div class="empty-state">
                <div class="empty-icon">📭</div>

                <h3>Buku Tidak Ditemukan</h3>

                <p>
                    <?php if ($cari !== ''): ?>
                        Tidak ada buku yang cocok dengan pencarian
                        "<?= e($cari) ?>".
                    <?php else: ?>
                        Belum ada koleksi buku yang tersedia.
                    <?php endif; ?>
                </p>
            </div>

        <?php endif; ?>

        <!-- PAGINATION -->
        <?php if ($totalData > 0): ?>

            <div class="katalog-pagination">

                <div class="pagination-info">
                    Menampilkan
                    <?= $offset + 1 ?>
                    -
                    <?= min($offset + $perHal, $totalData) ?>
                    dari <?= $totalData ?> buku
                </div>

                <div class="pagination-links">

                    <?php if ($halaman > 1): ?>
                        <a
                            class="page-link"
                            href="?hal=<?= $halaman - 1 ?>&cari=<?= urlencode($cari) ?>"
                        >
                            ‹
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalHal; $i++): ?>

                        <a
                            class="page-link <?= $i == $halaman ? 'active' : '' ?>"
                            href="?hal=<?= $i ?>&cari=<?= urlencode($cari) ?>"
                        >
                            <?= $i ?>
                        </a>

                    <?php endfor; ?>

                    <?php if ($halaman < $totalHal): ?>
                        <a
                            class="page-link"
                            href="?hal=<?= $halaman + 1 ?>&cari=<?= urlencode($cari) ?>"
                        >
                            ›
                        </a>
                    <?php endif; ?>

                </div>

            </div>

        <?php endif; ?>

    </div>

</div>

<?php require '../includes/footer.php'; ?>