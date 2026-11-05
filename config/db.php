<?php

$host = '127.0.0.1';
$db   = 'catalog_studio';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=3307;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    exit('No fue posible conectar con MySQL. Verifica que Laragon esté iniciado y que hayas importado database.sql. Detalle: ' . htmlspecialchars($e->getMessage()));
}
if (session_status() === PHP_SESSION_NONE) session_start();
