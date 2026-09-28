<?php
$dsn = "mysql:host=127.0.0.1;port=3306;dbname=absensi_barcode";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
$pdo = new PDO($dsn, 'user_absensi', 'Absensi_2026_Sec', $options);
$stmt = $pdo->query("SELECT base_url, instance_name FROM whatsapp_settings LIMIT 1");
echo json_encode($stmt->fetch(), JSON_PRETTY_PRINT);
