<?php
require 'fungsi.php';
$menu  = 'mk';
$tema  = 'hijau';
$judul = 'Form mata kuliah';
$id = null;
$lama = null;
if (isset($_GET['id'])) {
    $id = id_valid($_GET['id']);
    if (!$id) {
        set_pesan('galat', 'ID tidak valid.');
        pindah('mata_kuliah.php');
    }
    $lama = ambil_satu($conn, 'SELECT kode, nama, dosen, semester FROM mata_kuliah WHERE id = ?', $id);
    if (!$lama) {
        set_pesan('galat', 'Data tidak ditemukan.');
        pindah('mata_kuliah.php');
    }
}

$data  = $lama ?: ['kode' => '', 'nama' => '', 'dosen' => '', 'semester' => ''];
$galat = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!cek_csrf()) {
        $galat['umum'] = 'Token tidak cocok. Kirim ulang form.';
    } else {
        $data = [
            'kode'     => strtoupper(trim($_POST['kode'] ?? '')),
            'nama'     => trim($_POST['nama'] ?? ''),
            'dosen'    => trim($_POST['dosen'] ?? ''),
            'semester' => trim($_POST['semester'] ?? ''),
        ];

        if (!preg_match('/^[A-Z0-9-]{3,20}$/', $data['kode'])) {
            $galat['kode'] = 'Kode 3-20 karakter: huruf kapital, angka, atau tanda -.';
        }
        if (!panjang_ok($data['nama'], 3, 100)) {
            $galat['nama'] = 'Nama harus 3-100 karakter.';
        }
        if (!panjang_ok($data['dosen'], 3, 100)) {
            $galat['dosen'] = 'Dosen harus 3-100 karakter.';
        }
        $semester = filter_var($data['semester'], FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 14]]);
        if ($semester === false) {
            $galat['semester'] = 'Semester harus bilangan bulat 1-14.';
        }

        if (!$galat) {
            $tidakBerubah = $lama
                && $data['kode'] === $lama['kode']
                && $data['nama'] === $lama['nama']
                && $data['dosen'] === $lama['dosen']
                && $semester == $lama['semester'];

            if ($tidakBerubah) {
                $galat['umum'] = 'Tidak ada data yang berubah.';
            } else {
                try {
                    if ($id) {
                        $stmt = $conn->prepare('UPDATE mata_kuliah SET kode = ?, nama = ?, dosen = ?, semester = ? WHERE id = ?');
                        $stmt->bind_param('sssii', $data['kode'], $data['nama'], $data['dosen'], $semester, $id);
                        $stmt->execute();
                        set_pesan('sukses', 'Mata kuliah berhasil diubah.');
                    } else {
                        $stmt = $conn->prepare('INSERT INTO mata_kuliah (kode, nama, dosen, semester) VALUES (?, ?, ?, ?)');
                        $stmt->bind_param('sssi', $data['kode'], $data['nama'], $data['dosen'], $semester);
                        $stmt->execute();
                        set_pesan('sukses', 'Mata kuliah berhasil ditambahkan.');
                    }
                    pindah('mata_kuliah.php');
                } catch (mysqli_sql_exception $ex) {
                    if ($ex->getCode() === 1062) {
                        $galat['kode'] = 'Kode mata kuliah sudah digunakan.';
                    } else {
                        $galat['umum'] = 'Terjadi kesalahan database.';
                    }
                }
            }
        }
    }
}

require 'header.php';
?>
<div class="kartu">
  <h2><?= $id ? 'Edit mata kuliah' : 'Tambah mata kuliah' ?></h2>

  <?php if (isset($galat['umum'])): ?>
    <div class="pesan galat"><?= e($galat['umum']) ?></div>
  <?php endif; ?>

  <form method="post" action="mata_kuliah_form.php<?= $id ? '?id=' . (int) $id : '' ?>">
    <input type="hidden" name="token" value="<?= e(csrf_token()) ?>">

    <div class="grid2">
      <div>
        <label for="kode">Kode</label>
        <input type="text" id="kode" name="kode" value="<?= e($data['kode']) ?>">
        <?= galat_teks($galat, 'kode') ?>
      </div>
      <div>
        <label for="semester">Semester (1-14)</label>
        <input type="text" id="semester" name="semester" value="<?= e($data['semester']) ?>">
        <?= galat_teks($galat, 'semester') ?>
      </div>
    </div>

    <label for="nama">Nama mata kuliah</label>
    <input type="text" id="nama" name="nama" value="<?= e($data['nama']) ?>">
    <?= galat_teks($galat, 'nama') ?>

    <label for="dosen">Dosen pengampu</label>
    <input type="text" id="dosen" name="dosen" value="<?= e($data['dosen']) ?>">
    <?= galat_teks($galat, 'dosen') ?>

    <div class="form-aksi">
      <button type="submit" class="btn btn-utama">Simpan data</button>
      <a class="btn" href="mata_kuliah.php">Batal</a>
    </div>
  </form>
</div>

<?php require 'footer.php'; ?>