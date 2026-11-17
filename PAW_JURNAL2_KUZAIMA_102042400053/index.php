<?php
require 'fungsi.php';
$menu  = 'beranda';
$judul = 'Beranda';

$jml_mk     = $conn->query('SELECT COUNT(*) FROM mata_kuliah')->fetch_row()[0];
$jml_materi = $conn->query('SELECT COUNT(*) FROM materi')->fetch_row()[0];
$jml_tugas  = $conn->query('SELECT COUNT(*) FROM tugas')->fetch_row()[0];
$daftar_mk  = $conn->query('SELECT kode, nama FROM mata_kuliah ORDER BY kode')
                   ->fetch_all(MYSQLI_ASSOC);

require 'header.php';
?>
<div class="kartu">
  <h2>Learning Management System</h2>
  <p>Pengelolaan mata kuliah, materi pembelajaran, dan tugas.</p>
  <div class="statistik">
    <a class="stat hijau" href="mata_kuliah.php"><span>Mata kuliah</span><strong><?= (int) $jml_mk ?></strong></a>
    <a class="stat" href="materi.php"><span>Materi</span><strong><?= (int) $jml_materi ?></strong></a>
    <a class="stat" href="tugas.php"><span>Tugas</span><strong><?= (int) $jml_tugas ?></strong></a>
  </div>
</div>

<div class="kartu hijau">
  <h3>Mata kuliah tersedia</h3>
  <ul>
    <?php foreach ($daftar_mk as $mk): ?>
      <li><?= e($mk['kode']) ?> - <?= e($mk['nama']) ?></li>
    <?php endforeach; ?>
  </ul>
</div>

<?php require 'footer.php'; ?>