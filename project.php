<?php
// Sesuaikan dengan server MySQL lokal (XAMPP/Laragon)
const DB_HOST = 'localhost';
const DB_NAME = 'cacanuruldwijulia';
const DB_USER = 'root';
const DB_PASS = '';

session_start();

$pdo = new PDO(
    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
    DB_USER,
    DB_PASS,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);

function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_ok(?string $t): bool {
    return !empty($_SESSION['csrf']) && is_string($t) && hash_equals($_SESSION['csrf'], $t);
}
function flash(string $msg): void { $_SESSION['flash'] = $msg; }
function rupiah($n): string { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
