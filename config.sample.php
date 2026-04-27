<?php
// config.sample.php - пример конфигурации
// Скопируйте этот файл в config.php и заполните свои данные

$host = 'localhost';
$db   = 'chess_tournament';
$user = 'root';
$pass = 'ваш_пароль';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Подключение успешно!";
} catch (\PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}
?>