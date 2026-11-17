CREATE DATABASE IF NOT EXISTS lms_paw CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE lms_paw;

DROP TABLE IF EXISTS tugas;
DROP TABLE IF EXISTS materi;
DROP TABLE IF EXISTS mata_kuliah;

CREATE TABLE mata_kuliah (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(20) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  dosen VARCHAR(100) NOT NULL,
  semester TINYINT UNSIGNED NOT NULL
);

CREATE TABLE materi (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mata_kuliah_id INT UNSIGNED NOT NULL,
  judul VARCHAR(150) NOT NULL,
  isi TEXT NOT NULL,
  tautan VARCHAR(255) NULL,
  FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id) ON DELETE RESTRICT
);

CREATE TABLE tugas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mata_kuliah_id INT UNSIGNED NOT NULL,
  judul VARCHAR(150) NOT NULL,
  instruksi TEXT NOT NULL,
  tenggat DATE NOT NULL,
  status VARCHAR(20) NOT NULL,
  FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id) ON DELETE RESTRICT
);

INSERT INTO mata_kuliah (id, kode, nama, dosen, semester) VALUES
(1, 'DDE', 'Analisis dan Perancangan Sistem Informasi', 'Dosen DDE', 5),
(2, 'KLL', 'Pemodelan Proses Bisnis', 'Dosen KLL', 5),
(3, 'AER', 'Pengembangan Aplikasi Website', 'Dosen AER', 5);

INSERT INTO materi (mata_kuliah_id, judul, isi, tautan) VALUES
(3, 'Dasar PHP', 'Variabel, percabangan, perulangan, dan fungsi dasar PHP.', 'https://www.php.anakweb.id/brlajaar-dasar-php/'),
(2, 'Pengenalan BPMN', 'Notasi dasar pemodelan proses bisnis dengan BPMN.', NULL);

INSERT INTO tugas (mata_kuliah_id, judul, instruksi, tenggat, status) VALUES
(3, 'Latihan CRUD', 'Buat fitur tambah, lihat, ubah, dan hapus data mata kuliah.', '2026-12-05', 'Aktif'),
(2, 'Diagram BPMN', 'Buat diagram proses bisnis pengadaan dan pengiriman barang.', '2026-12-10', 'Draf');