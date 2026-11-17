<?php
require 'fungsi.php';
$menu  = 'tugas';
$judul = 'Form tugas';
$daftar_status = ['Draf', 'Aktif', 'Selesai'];
$id = null;
$lama = null;
if (isset($_GET['id'])) {
    $id = id_valid($_GET['id']);
    if (!$id) {
        set_pesan('galat', 'ID tidak valid.');
        pindah('tugas.php');
    }
    $lama = ambil_satu($conn,
        'SELECT mata_kuliah_id, judul, instruksi, tenggat, status FROM tugas WHERE id = ?', $id);
    if (!$lama) {
        set_pesan('galat', 'Data tidak ditemukan.');
        pindah('tugas.php');
    }
}

$data = $lama ?: [
    'mata_kuliah_id' => 0, 'judul' => '', 'instruksi' => '',
    'tenggat' => '', 'status' => 'Draf',
];
$galat = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!cek_csrf()) {
        $galat['umum'] = 'Token tidak cocok. Kirim ulang form.';
    } else {
        $data = [
            'mata_kuliah_id' => id_valid($_POST['mata_kuliah_id'] ?? '') ?: 0,
            'judul'          => trim($_POST['judul'] ?? ''),
            'instruksi'      => trim($_POST['instruksi'] ?? ''),
            'tenggat'        => trim($_POST['tenggat'] ?? ''),
            'status'         => trim($_POST['status'] ?? ''),
        ];

        if (!$data['mata_kuliah_id']
            || !ambil_satu($conn, 'SELECT id FROM mata_kuliah WHERE id = ?', $data['mata_kuliah_id'])) {
            $galat['mata_kuliah_id'] = 'Pilih mata kuliah yang tersedia.';
        }
        if (!panjang_ok($data['judul'], 3, 150)) {
            $galat['judul'] = 'Judul harus 3-150 karakter.';
        }
        if (!panjang_ok($data['instruksi'], 3, 2000)) {
            $galat['instruksi'] = 'Instruksi harus 3-2000 karakter.';
        }
        if (!tanggal_valid($data['tenggat'])) {
            $galat['tenggat'] = 'Tanggal tenggat tidak valid.';
        }
        if (!in_array($data['status'], $daftar_status, true)) {
            $galat['status'] = 'Status hanya Draf, Aktif, atau Selesai.';
        }

        if (!$galat) {
            if ($lama
                && $data['mata_kuliah_id'] == $lama['mata_kuliah_id']
                && $data['judul'] === $lama['judul']
                && $data['instruksi'] === $lama['instruksi']
                && $data['tenggat'] === $lama['tenggat']
                && $data['status'] === $lama['status']) {
                $galat['umum'] = 'Tidak ada data yang berubah.';
            } else {
                try {
                    if ($id) {
                        $stmt = $conn->prepare('UPDATE tugas SET mata_kuliah_id = ?, judul = ?, instruksi = ?, tenggat = ?, status = ? WHERE id = ?');
                        $stmt->bind_param('issssi', $data['mata_kuliah_id'], $data['judul'], $data['instruksi'], $data['tenggat'], $data['status'], $id);
                        set_pesan('sukses', 'Tugas berhasil diubah.');
                    } else {
                        $stmt = $conn->prepare('INSERT INTO tugas (mata_kuliah_id, judul, instruksi, tenggat, status) VALUES (?, ?, ?, ?, ?)');
                        $stmt->bind_param('issss', $data['mata_kuliah_id'], $data['judul'], $data['instruksi'], $data['tenggat'], $data['status']);
                        set_pesan('sukses', 'Tugas berhasil ditambahkan.');
                    }
                    $stmt->execute();
                    pindah('tugas.php');
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
  <h2><?= $id ? 'Edit tugas' : 'Tambah tugas' ?></h2>

  <?php if (isset($galat['umum'])): ?>
    <div class="pesan galat"><?= e($galat['umum']) ?></div>
  <?php endif; ?>

  <form method="post" action="tugas_form.php<?= $id ? '?id=' . (int) $id : '' ?>">
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
        <label for="judul">Judul tugas</label>
        <input type="text" id="judul" name="judul" value="<?= e($data['judul']) ?>">
        <?= galat_teks($galat, 'judul') ?>
      </div>
    </div>

    <label for="instruksi">Instruksi tugas</label>
    <textarea id="instruksi" name="instruksi"><?= e($data['instruksi']) ?></textarea>
    <?= galat_teks($galat, 'instruksi') ?>

    <div class="grid2">
      <div>
        <label for="tenggat">Tenggat</label>
        <input type="date" id="tenggat" name="tenggat" value="<?= e($data['tenggat']) ?>">
        <?= galat_teks($galat, 'tenggat') ?>
      </div>
      <div>
        <label for="status">Status</label>
        <select id="status" name="status">
          <?php foreach ($daftar_status as $s): ?>
            <option value="<?= e($s) ?>" <?= $data['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
          <?php endforeach; ?>
        </select>
        <?= galat_teks($galat, 'status') ?>
      </div>
    </div>

    <div class="form-aksi">
      <button type="submit" class="btn btn-utama">Simpan data</button>
      <a class="btn" href="tugas.php">Batal</a>
    </div>
  </form>
</div>

<?php require 'footer.php'; ?>