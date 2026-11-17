<?php
require 'fungsi.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {
    if (!cek_csrf()) {
        set_pesan('galat', 'Token tidak cocok. Data tidak dihapus.');
        pindah('tugas.php');
    }
    $id = id_valid($_POST['id'] ?? '');
    if (!$id) {
        set_pesan('galat', 'ID tidak valid. Data tidak dihapus.');
        pindah('tugas.php');
    }
    $stmt = $conn->prepare('DELETE FROM tugas WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        set_pesan('sukses', 'Tugas berhasil dihapus.');
    } else {
        set_pesan('galat', 'Data tidak ditemukan.');
    }
    pindah('tugas.php');
}

$menu  = 'tugas';
$judul = 'Tugas';

$q      = trim($_GET['q'] ?? '');
$like   = '%' . $q . '%';
$filter = id_valid($_GET['mk'] ?? '') ?: 0;

$detail = null;
if ($id = id_valid($_GET['detail'] ?? '')) {
    $detail = ambil_satu($conn,
        'SELECT t.id, t.judul, t.instruksi, t.tenggat, t.status, mk.kode, mk.nama AS nama_mk
         FROM tugas t JOIN mata_kuliah mk ON mk.id = t.mata_kuliah_id
         WHERE t.id = ?', $id);
}
$konfirm = null;
if ($id = id_valid($_GET['hapus'] ?? '')) {
    $konfirm = ambil_satu($conn, 'SELECT id, judul FROM tugas WHERE id = ?', $id);
}

$stmt = $conn->prepare(
    'SELECT t.id, t.judul, t.tenggat, t.status, mk.kode, mk.nama AS nama_mk
     FROM tugas t JOIN mata_kuliah mk ON mk.id = t.mata_kuliah_id
     WHERE t.judul LIKE ? AND (? = 0 OR t.mata_kuliah_id = ?)
     ORDER BY t.tenggat'
);
$stmt->bind_param('sii', $like, $filter, $filter);
$stmt->execute();
$daftar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$daftar_mk = daftar_mata_kuliah($conn);

require 'header.php';
?>
<?php if ($detail): ?>
<div class="kartu">
  <h2>Detail tugas</h2>
  <dl class="detail">
    <dt>Mata kuliah</dt><dd><?= e($detail['kode'] . ' - ' . $detail['nama_mk']) ?></dd>
    <dt>Judul</dt>      <dd><?= e($detail['judul']) ?></dd>
    <dt>Instruksi</dt>  <dd><?= nl2br(e($detail['instruksi'])) ?></dd>
    <dt>Tenggat</dt>    <dd><?= e($detail['tenggat']) ?></dd>
    <dt>Status</dt>     <dd><?= e($detail['status']) ?></dd>
  </dl>
  <div class="form-aksi">
    <a class="btn btn-utama" href="tugas_form.php?id=<?= (int) $detail['id'] ?>">Edit data</a>
    <a class="btn" href="tugas.php">Kembali</a>
  </div>
</div>
<?php endif; ?>

<?php if ($konfirm): ?>
<div class="kartu">
  <h2>Konfirmasi hapus</h2>
  <p>Hapus tugas <strong><?= e($konfirm['judul']) ?></strong>? Data baru dihapus setelah tombol konfirmasi dikirim.</p>
  <form method="post" action="tugas.php" class="form-aksi">
    <input type="hidden" name="aksi" value="hapus">
    <input type="hidden" name="id" value="<?= (int) $konfirm['id'] ?>">
    <input type="hidden" name="token" value="<?= e(csrf_token()) ?>">
    <button type="submit" class="btn btn-hapus">Ya, hapus data</button>
    <a class="btn" href="tugas.php">Batal</a>
  </form>
</div>
<?php endif; ?>

<div class="kartu">
  <div class="header-kartu">
    <h2>Daftar tugas</h2>
    <a class="btn btn-utama" href="tugas_form.php">+ Tambah tugas</a>
  </div>

  <form method="get" action="tugas.php" class="baris-cari">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari judul tugas">
    <select name="mk">
      <option value="0">Semua mata kuliah</option>
      <?php foreach ($daftar_mk as $mk): ?>
        <option value="<?= (int) $mk['id'] ?>" <?= $filter == $mk['id'] ? 'selected' : '' ?>>
          <?= e($mk['kode'] . ' - ' . $mk['nama']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-utama">Cari</button>
    <a class="btn" href="tugas.php">Reset</a>
  </form>

  <p>Menampilkan <?= count($daftar) ?> data.</p>

  <?php if (!$daftar): ?>
    <div class="kosong">Data tidak ditemukan.</div>
  <?php else: ?>
  <table>
    <tr><th>Judul</th><th>Mata kuliah</th><th>Tenggat</th><th>Status</th><th>Aksi</th></tr>
    <?php foreach ($daftar as $t): ?>
    <tr>
      <td><?= e($t['judul']) ?></td>
      <td><?= e($t['kode'] . ' - ' . $t['nama_mk']) ?></td>
      <td><?= e($t['tenggat']) ?></td>
      <td><?= e($t['status']) ?></td>
      <td class="aksi">
        <a class="btn btn-kecil" href="tugas.php?detail=<?= (int) $t['id'] ?>">Detail</a>
        <a class="btn btn-kecil" href="tugas_form.php?id=<?= (int) $t['id'] ?>">Edit</a>
        <a class="btn btn-kecil btn-hapus" href="tugas.php?hapus=<?= (int) $t['id'] ?>">Hapus</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>

<?php require 'footer.php'; ?>