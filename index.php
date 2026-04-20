<?php
ini_set( 'display_errors', 1 );
ini_set( 'display_startup_errors', 1 );
error_reporting(E_ALL);
session_start();
require_once 'config.php';

// Авторизация
if (isset($_GET['login']) && $_GET['login'] === 'admin') {
    $_SESSION['user_role'] = 'admin';
}
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

$isAdmin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
$action = $_GET['action'] ?? 'players';

switch ($action) {
    case 'add_player':
        if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nickname'])) {
            $stmt = $pdo->prepare("INSERT INTO players (nickname) VALUES (?)");
            $stmt->execute([$_POST['nickname']]);
        }
        header("Location: index.php?action=players");
        exit;

    case 'delete_player':
        if ($isAdmin && isset($_GET['id'])) {
            $stmt = $pdo->prepare("DELETE FROM players WHERE id = ?");
            $stmt->execute([$_GET['id']]);
        }
        header("Location: index.php?action=players");
        exit;

    case 'players':
        $sql = "SELECT p.*, 
            (SELECT COUNT(*) FROM matches WHERE winner_id = p.id) as wins,
            (SELECT COUNT(*) FROM matches WHERE (player1_id = p.id OR player2_id = p.id) 
             AND winner_id IS NOT NULL AND winner_id != p.id) as losses
            FROM players p";

        $players = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $players = $players ?: []; // Если пусто, то пустой массив

        $view_content = 'views/players.php';
        break;

    case 'draw':
        if (!$isAdmin) die("Доступ запрещен!");
        require_once 'models/Tournament.php';
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE matches; TRUNCATE rounds; SET FOREIGN_KEY_CHECKS = 1;");
        if (drawTournament($pdo, 1)) {
            header("Location: index.php?action=bracket");
        } else {
            die("Ошибка жеребьевки!");
        }
        break;

    case 'bracket':
        $sql = "SELECT m.round_id, m.*, p1.nickname as p1_name, p2.nickname as p2_name, r.round_name 
                FROM matches m
                JOIN rounds r ON m.round_id = r.id
                LEFT JOIN players p1 ON m.player1_id = p1.id
                LEFT JOIN players p2 ON m.player2_id = p2.id
                WHERE r.tournament_id = 1
                ORDER BY r.round_number, m.id";
        $rounds = $pdo->query($sql)->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
        $view_content = 'views/bracket.php';
        break;

    default:
        header("Location: index.php?action=players");
        exit;
}

require_once 'views/layout.php';