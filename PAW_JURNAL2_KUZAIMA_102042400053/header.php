<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($judul ?? 'Beranda') ?> | LMS EAD</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
  <div class="merek">Learning Management System</div>
  <nav>
    <a href="index.php" class="<?= ($menu ?? '') === 'beranda' ? 'aktif' : '' ?>">Beranda</a>
    <a href="mata_kuliah.php" class="menu-mk <?= ($menu ?? '') === 'mk' ? 'aktif' : '' ?>">Mata Kuliah</a>
    <a href="materi.php" class="<?= ($menu ?? '') === 'materi' ? 'aktif' : '' ?>">Materi</a>
    <a href="tugas.php" class="<?= ($menu ?? '') === 'tugas' ? 'aktif' : '' ?>">Tugas</a>
  </nav>
</header>
<main class="container <?= e($tema ?? '') ?>">
<?= tampil_pesan() ?>