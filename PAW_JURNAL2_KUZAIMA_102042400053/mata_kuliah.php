<?php
require 'fungsi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {
    if (!cek_csrf()) {
        set_pesan('galat', 'Token tidak cocok. Data tidak dihapus.');
        pindah('mata_kuliah.php');
    }
    $id = id_valid($_POST['id'] ?? '');
    if (!$id) {
        set_pesan('galat', 'ID tidak valid. Data tidak dihapus.');
        pindah('mata_kuliah.php');
    }

    $stmt = $conn->prepare(
        'SELECT (SELECT COUNT(*) FROM materi WHERE mata_kuliah_id = ?)
              + (SELECT COUNT(*) FROM tugas  WHERE mata_kuliah_id = ?) AS jml'
    );
    $stmt->bind_param('ii', $id, $id);
    $stmt->execute();
    $jml = $stmt->get_result()->fetch_assoc()['jml'];

    if ($jml > 0) {
        set_pesan('galat', 'Data belum tersimpan. Mata kuliah masih memiliki materi atau tugas. Hapus data terkait terlebih dahulu.');
        pindah('mata_kuliah.php');
    }

    try {
        $stmt = $conn->prepare('DELETE FROM mata_kuliah WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            set_pesan('sukses', 'Mata kuliah berhasil dihapus.');
        } else {
            set_pesan('galat', 'Data tidak ditemukan.');
        }
    } catch (mysqli_sql_exception $ex) {
        set_pesan('galat', 'Penghapusan ditolak database karena data masih dipakai.');
    }
    pindah('mata_kuliah.php');
}

$menu  = 'mk';
$tema  = 'hijau';
$judul = 'Mata Kuliah';

$q    = trim($_GET['q'] ?? '');
$like = '%' . $q . '%';

$detail = null;
if ($id = id_valid($_GET['detail'] ?? '')) {
    $detail = ambil_satu($conn, 'SELECT * FROM mata_kuliah WHERE id = ?', $id);
}
$konfirm = null;
if ($id = id_valid($_GET['hapus'] ?? '')) {
    $konfirm = ambil_satu($conn, 'SELECT id, nama FROM mata_kuliah WHERE id = ?', $id);
}

$stmt = $conn->prepare(
    'SELECT id, kode, nama, dosen, semester FROM mata_kuliah
     WHERE kode LIKE ? OR nama LIKE ?
     ORDER BY nama'
);
$stmt->bind_param('ss', $like, $like);
$stmt->execute();
$daftar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

require 'header.php';
?>
<?php if ($detail): ?>
<div class="kartu">
  <h2>Detail mata kuliah</h2>
  <dl class="detail">
    <dt>Kode</dt>     <dd><?= e($detail['kode']) ?></dd>
    <dt>Nama</dt>     <dd><?= e($detail['nama']) ?></dd>
    <dt>Dosen</dt>    <dd><?= e($detail['dosen']) ?></dd>
    <dt>Semester</dt> <dd><?= (int) $detail['semester'] ?></dd>
  </dl>
  <div class="form-aksi">
    <a class="btn btn-utama" href="mata_kuliah_form.php?id=<?= (int) $detail['id'] ?>">Edit data</a>
    <a class="btn" href="mata_kuliah.php">Kembali</a>
  </div>
</div>
<?php endif; ?>

<?php if ($konfirm): ?>
<div class="kartu">
  <h2>Konfirmasi hapus</h2>
  <p>Hapus <strong><?= e($konfirm['nama']) ?></strong>? Data baru dihapus setelah tombol konfirmasi dikirim.</p>
  <form method="post" action="mata_kuliah.php" class="form-aksi">
    <input type="hidden" name="aksi" value="hapus">
    <input type="hidden" name="id" value="<?= (int) $konfirm['id'] ?>">
    <input type="hidden" name="token" value="<?= e(csrf_token()) ?>">
    <button type="submit" class="btn btn-hapus">Ya, hapus data</button>
    <a class="btn" href="mata_kuliah.php">Batal</a>
  </form>
</div>
<?php endif; ?>

<div class="kartu">
  <div class="header-kartu">
    <h2>Daftar mata kuliah</h2>
    <a class="btn btn-utama" href="mata_kuliah_form.php">+ Tambah mata kuliah</a>
  </div>

  <form method="get" action="mata_kuliah.php" class="baris-cari">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari kode atau nama">
    <button type="submit" class="btn btn-utama">Cari</button>
    <a class="btn" href="mata_kuliah.php">Reset</a>
  </form>

  <p>Menampilkan <?= count($daftar) ?> data.</p>

  <?php if (!$daftar): ?>
    <div class="kosong">Data tidak ditemukan.</div>
  <?php else: ?>
  <table>
    <tr><th>Kode</th><th>Nama</th><th>Dosen / semester</th><th>Aksi</th></tr>
    <?php foreach ($daftar as $mk): ?>
    <tr>
      <td><?= e($mk['kode']) ?></td>
      <td><?= e($mk['nama']) ?></td>
      <td><?= e($mk['dosen']) ?> / Semester <?= (int) $mk['semester'] ?></td>
      <td class="aksi">
        <a class="btn btn-kecil" href="mata_kuliah.php?detail=<?= (int) $mk['id'] ?>">Detail</a>
        <a class="btn btn-kecil" href="mata_kuliah_form.php?id=<?= (int) $mk['id'] ?>">Edit</a>
        <a class="btn btn-kecil btn-hapus" href="mata_kuliah.php?hapus=<?= (int) $mk['id'] ?>">Hapus</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>

<?php require 'footer.php'; ?>