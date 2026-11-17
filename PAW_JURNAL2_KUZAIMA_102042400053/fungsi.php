<?php
session_start();
require_once 'koneksi.php';

function e($teks) {
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

function pindah($lokasi) {
    header('Location: ' . $lokasi);
    exit;
}

function set_pesan($jenis, $teks) {
    $_SESSION['pesan'] = ['jenis' => $jenis, 'teks' => $teks];
}
function tampil_pesan() {
    if (empty($_SESSION['pesan'])) {
        return '';
    }
    $p = $_SESSION['pesan'];
    unset($_SESSION['pesan']);
    return '<div class="pesan ' . e($p['jenis']) . '">' . e($p['teks']) . '</div>';
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function cek_csrf() {
    return isset($_SESSION['csrf'], $_POST['token'])
        && hash_equals($_SESSION['csrf'], $_POST['token']);
}

function id_valid($nilai) {
    return filter_var($nilai, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
}

function panjang_ok($teks, $min, $max) {
    $n = mb_strlen(trim($teks));
    return $n >= $min && $n <= $max;
}

function tanggal_valid($teks) {
    $d = DateTime::createFromFormat('Y-m-d', $teks);
    return $d && $d->format('Y-m-d') === $teks;
}

function tautan_valid($teks) {
    return mb_strlen($teks) <= 255 && preg_match('#^https?://[^\s]+$#i', $teks);
}

function galat_teks($galat, $kunci) {
    return isset($galat[$kunci])
        ? '<div class="galat-teks">' . e($galat[$kunci]) . '</div>'
        : '';
}
function ambil_satu($conn, $sql, $id) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function daftar_mata_kuliah($conn) {
    return $conn->query('SELECT id, kode, nama FROM mata_kuliah ORDER BY nama')
                ->fetch_all(MYSQLI_ASSOC);
}