<?php
require 'fungsi.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {
    if (!cek_csrf()) {
        set_pesan('galat', 'Token tidak cocok. Data tidak dihapus.');
        pindah('materi.php');
    }
    $id = id_valid($_POST['id'] ?? '');
    if (!$id) {
        set_pesan('galat', 'ID tidak valid. Data tidak dihapus.');
        pindah('materi.php');
    }
    $stmt = $conn->prepare('DELETE FROM materi WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        set_pesan('sukses', 'Materi berhasil dihapus.');
    } else {
        set_pesan('galat', 'Data tidak ditemukan.');
    }
    pindah('materi.php');
}

$menu  = 'materi';
$judul = 'Materi';

$q      = trim($_GET['q'] ?? '');
$like   = '%' . $q . '%';
$filter = id_valid($_GET['mk'] ?? '') ?: 0;
$detail = null;
if ($id = id_valid($_GET['detail'] ?? '')) {
    $detail = ambil_satu($conn,
        'SELECT m.id, m.judul, m.isi, m.tautan, mk.kode, mk.nama AS nama_mk
         FROM materi m JOIN mata_kuliah mk ON mk.id = m.mata_kuliah_id
         WHERE m.id = ?', $id);
}
$konfirm = null;
if ($id = id_valid($_GET['hapus'] ?? '')) {
    $konfirm = ambil_satu($conn, 'SELECT id, judul FROM materi WHERE id = ?', $id);
}
$stmt = $conn->prepare(
    'SELECT m.id, m.judul, mk.kode, mk.nama AS nama_mk
     FROM materi m JOIN mata_kuliah mk ON mk.id = m.mata_kuliah_id
     WHERE m.judul LIKE ? AND (? = 0 OR m.mata_kuliah_id = ?)
     ORDER BY m.judul'
);
$stmt->bind_param('sii', $like, $filter, $filter);
$stmt->execute();
$daftar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$daftar_mk = daftar_mata_kuliah($conn);

require 'header.php';
?>
<?php if ($detail): ?>
<div class="kartu">
  <h2>Detail materi</h2>
  <dl class="detail">
    <dt>Mata kuliah</dt>
    <dd><?= e($detail['kode'] . ' - ' . $detail['nama_mk']) ?></dd>
    <dt>Judul</dt>
    <dd><?= e($detail['judul']) ?></dd>
    <dt>Isi</dt>
    <dd><?= nl2br(e($detail['isi'])) ?>
      <?php if ($detail['tautan']): ?>
        <a href="<?= e($detail['tautan']) ?>" target="_blank" rel="noopener"><?= e($detail['tautan']) ?></a>
      <?php else: ?>
        -
      <?php endif; ?>
    </dd>
  </dl>
  <div class="form-aksi">
    <a class="btn btn-utama" href="materi_form.php?id=<?= (int) $detail['id'] ?>">Edit data</a>
    <a class="btn" href="materi.php">Kembali</a>
  </div>
</div>
<?php endif; ?>

<?php if ($konfirm): ?>
<div class="kartu">
  <h2>Konfirmasi hapus</h2>
  <p>Hapus materi <strong><?= e($konfirm['judul']) ?></strong>? Data baru dihapus setelah tombol konfirmasi dikirim.</p>
  <form method="post" action="materi.php" class="form-aksi">
    <input type="hidden" name="aksi" value="hapus">
    <input type="hidden" name="id" value="<?= (int) $konfirm['id'] ?>">
    <input type="hidden" name="token" value="<?= e(csrf_token()) ?>">
    <button type="submit" class="btn btn-hapus">Ya, hapus data</button>
    <a class="btn" href="materi.php">Batal</a>
  </form>
</div>
<?php endif; ?>

<div class="kartu">
  <div class="header-kartu">
    <h2>Daftar materi</h2>
    <a class="btn btn-utama" href="materi_form.php">+ Tambah materi</a>
  </div>

  <form method="get" action="materi.php" class="baris-cari">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari judul materi">
    <select name="mk">
      <option value="0">Semua mata kuliah</option>
      <?php foreach ($daftar_mk as $mk): ?>
        <option value="<?= (int) $mk['id'] ?>" <?= $filter == $mk['id'] ? 'selected' : '' ?>>
          <?= e($mk['kode'] . ' - ' . $mk['nama']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-utama">Cari</button>
    <a class="btn" href="materi.php">Reset</a>
  </form>

  <p>Menampilkan <?= count($daftar) ?> data.</p>

  <?php if (!$daftar): ?>
    <div class="kosong">Data tidak ditemukan.</div>
  <?php else: ?>
  <table>
    <tr><th>Judul</th><th>Mata kuliah</th><th>Aksi</th></tr>
    <?php foreach ($daftar as $m): ?>
    <tr>
      <td><?= e($m['judul']) ?></td>
      <td><?= e($m['kode'] . ' - ' . $m['nama_mk']) ?></td>
      <td class="aksi">
        <a class="btn btn-kecil" href="materi.php?detail=<?= (int) $m['id'] ?>">Detail</a>
        <a class="btn btn-kecil" href="materi_form.php?id=<?= (int) $m['id'] ?>">Edit</a>
        <a class="btn btn-kecil btn-hapus" href="materi.php?hapus=<?= (int) $m['id'] ?>">Hapus</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>

<?php require 'footer.php'; ?>