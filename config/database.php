<?php
mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_connect('localhost', 'root', '', 'travelmate_db');

if (!$conn) {
    die('Database connection failed.');
}

mysqli_set_charset($conn, 'utf8mb4');
