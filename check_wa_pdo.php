<?php
$dsn = "mysql:host=127.0.0.1;port=3306;dbname=absensi_barcode";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, 'user_absensi', 'Absensi_2026_Sec', $options);
    $stmt = $pdo->query("SELECT id, module, recipient_type, status, response_payload, created_at FROM whatsapp_notification_logs ORDER BY id DESC LIMIT 5");
    $rows = $stmt->fetchAll();
    echo json_encode($rows, JSON_PRETTY_PRINT);
} catch (\PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
