<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8');

$host = 'sql100.infinityfree.com';
$db   = 'if0_41719034_chess_tournament';
$user = '#';
$pass = '#';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $pdo->exec("SET NAMES utf8mb4");
    $pdo->exec("SET CHARACTER SET utf8mb4");

} catch (\PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}
?>