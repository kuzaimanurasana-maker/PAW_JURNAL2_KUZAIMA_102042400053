<?php
require 'fungsi.php';
$menu  = 'materi';
$judul = 'Form materi';

$id = null;
$lama = null;
if (isset($_GET['id'])) {
    $id = id_valid($_GET['id']);
    if (!$id) {
        set_pesan('galat', 'ID tidak valid.');
        pindah('materi.php');
    }
    $lama = ambil_satu($conn, 'SELECT mata_kuliah_id, judul, isi, tautan FROM materi WHERE id = ?', $id);
    if (!$lama) {
        set_pesan('galat', 'Data tidak ditemukan.');
        pindah('materi.php');
    }
}

$data = $lama
    ? ['mata_kuliah_id' => $lama['mata_kuliah_id'], 'judul' => $lama['judul'],
       'isi' => $lama['isi'], 'tautan' => $lama['tautan'] ?? '']
    : ['mata_kuliah_id' => 0, 'judul' => '', 'isi' => '', 'tautan' => ''];
$galat = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!cek_csrf()) {
        $galat['umum'] = 'Token tidak cocok. Kirim ulang form.';
    } else {
        $data = [
            'mata_kuliah_id' => id_valid($_POST['mata_kuliah_id'] ?? '') ?: 0,
            'judul'          => trim($_POST['judul'] ?? ''),
            'isi'            => trim($_POST['isi'] ?? ''),
            'tautan'         => trim($_POST['tautan'] ?? ''),
        ];

        
        if (!$data['mata_kuliah_id']
            || !ambil_satu($conn, 'SELECT id FROM mata_kuliah WHERE id = ?', $data['mata_kuliah_id'])) {
            $galat['mata_kuliah_id'] = 'Pilih mata kuliah yang tersedia.';
        }
        if (!panjang_ok($data['judul'], 3, 150)) {
            $galat['judul'] = 'Judul harus 3-150 karakter.';
        }
        if (!panjang_ok($data['isi'], 3, 2000)) {
            $galat['isi'] = 'Isi harus 3-2000 karakter.';
        }
        if ($data['tautan'] !== '' && !tautan_valid($data['tautan'])) {
            $galat['tautan'] = 'Tautan maksimal 255 karakter dan harus diawali http:// atau https://.';
        }

        if (!$galat) {
            $tautan = $data['tautan'] === '' ? null : $data['tautan'];

            if ($lama
                && $data['mata_kuliah_id'] == $lama['mata_kuliah_id']
                && $data['judul'] === $lama['judul']
                && $data['isi'] === $lama['isi']
                && $tautan === $lama['tautan']) {
                $galat['umum'] = 'Tidak ada data yang berubah.';
            } else {
                try {
                    if ($id) {
                        $stmt = $conn->prepare('UPDATE materi SET mata_kuliah_id = ?, judul = ?, isi = ?, tautan = ? WHERE id = ?');
                        $stmt->bind_param('isssi', $data['mata_kuliah_id'], $data['judul'], $data['isi'], $tautan, $id);
                        set_pesan('sukses', 'Materi berhasil diubah.');
                    } else {
                        $stmt = $conn->prepare('INSERT INTO materi (mata_kuliah_id, judul, isi, tautan) VALUES (?, ?, ?, ?)');
                        $stmt->bind_param('isss', $data['mata_kuliah_id'], $data['judul'], $data['isi'], $tautan);
                        set_pesan('sukses', 'Materi berhasil ditambahkan.');
                    }
                    $stmt->execute();
                    pindah('materi.php');
                } catch (mysqli_sql_exception $ex) {
                    $galat['umum'] = 'Terjadi kesalahan database. Periksa koneksi dan data.';
                }
            }
        }
    }
}

$daftar_mk = daftar_mata_kuliah($conn);

require 'header.php';
?>
<div class="kartu">
  <h2><?= $id ? 'Edit materi' : 'Tambah materi' ?></h2>

  <?php if (isset($galat['umum'])): ?>
    <div class="pesan galat"><?= e($galat['umum']) ?></div>
  <?php endif; ?>

  <form method="post" action="materi_form.php<?= $id ? '?id=' . (int) $id : '' ?>">
    <input type="hidden" name="token" value="<?= e(csrf_token()) ?>">

    <div class="grid2">
      <div>
        <label for="mata_kuliah_id">Mata kuliah</label>
        <select id="mata_kuliah_id" name="mata_kuliah_id">
          <option value="0">Pilih Mata kuliah</option>
          <?php foreach ($daftar_mk as $mk): ?>
            <option value="<?= (int) $mk['id'] ?>" <?= $data['mata_kuliah_id'] == $mk['id'] ? 'selected' : '' ?>>
              <?= e($mk['kode'] . ' - ' . $mk['nama']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?= galat_teks($galat, 'mata_kuliah_id') ?>
      </div>
      <div>
        <label for="judul">Judul materi</label>
        <input type="text" id="judul" name="judul" value="<?= e($data['judul']) ?>">
        <?= galat_teks($galat, 'judul') ?>
      </div>
    </div>

    <label for="isi">Isi materi</label>
    <textarea id="isi" name="isi"><?= e($data['isi']) ?></textarea>
    <?= galat_teks($galat, 'isi') ?>

    <label for="tautan">Tautan referensi (opsional)</label>
    <input type="text" id="tautan" name="tautan" value="<?= e($data['tautan']) ?>" placeholder="https://...">
    <?= galat_teks($galat, 'tautan') ?>

    <div class="form-aksi">
      <button type="submit" class="btn btn-utama">Simpan data</button>
      <a class="btn" href="materi.php">Batal</a>
    </div>
  </form>
</div>

<?php require 'footer.php'; ?>