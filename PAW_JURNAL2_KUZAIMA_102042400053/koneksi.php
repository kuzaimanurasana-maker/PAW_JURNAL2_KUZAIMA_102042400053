<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = new mysqli('127.0.0.1', 'root', '', 'lms_paw');
$conn->set_charset('utf8mb4');
?>