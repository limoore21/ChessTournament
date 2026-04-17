<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';
require_once 'models/Player.php';

$pdo = addBD();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

switch ($action) {
    case 'list':
        $players = getAllPlayers($pdo);
        $view_content = 'views/players.php';
        break;

    case 'add_player':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nickname'])) {
            ins($pdo, $_POST['nickname']);
            header("Location: index.php?action=list");
            exit;
        }
        break;

    case 'edit_form':
        $player = getPlayer($pdo, $_GET['id']);
        $view_content = 'views/edit_player.php';
        break;

    case 'update_player':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            updatePlayer($pdo, $_POST['id'], $_POST['nickname']);
            header("Location: index.php?action=list");
            exit;
        }
        break;

    case 'delete_player':
        if (isset($_GET['id'])) {
            delPlayer($pdo, $_GET['id']);
            header("Location: index.php?action=list");
            exit;
        }
        break;

    case 'bracket':
        $sql = "SELECT m.*, 
                   p1.nickname AS p1_name, 
                   p2.nickname AS p2_name,
                   r.round_name
            FROM matches m
            JOIN rounds r ON m.round_id = r.id
            LEFT JOIN players p1 ON m.player1_id = p1.id
            LEFT JOIN players p2 ON m.player2_id = p2.id
            WHERE r.tournament_id = 1";

        $stmt = $pdo->query($sql);
        $all_matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $pdo->exec("TRUNCATE TABLE matches;");
        $pdo->exec("TRUNCATE TABLE rounds;");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        $rounds = [];
        foreach ($all_matches as $match) {
            $rounds[$match['round_id']][] = $match;
        }

        $view_content = 'views/bracket.php';
        break;

    case 'draw':
        require_once 'models/Tournament.php';
        $tournament_id = 1;

        try {
            $pdo->prepare("INSERT IGNORE INTO tournaments (id, title) VALUES (1, 'Первый чемпионат')")->execute();

            $pdo->prepare("INSERT IGNORE INTO tournament_participants (tournament_id, player_id) 
                       SELECT 1, id FROM players")->execute();

            $pdo->prepare("DELETE FROM rounds WHERE tournament_id = ?")->execute([$tournament_id]);

            if (drawTournament($pdo, $tournament_id)) {
                header("Location: index.php?action=bracket");
                exit;
            } else {
                die("Ошибка: Мало игроков для жеребьевки.");
            }
        } catch (PDOException $e) {
            die("Ошибка базы данных: " . $e->getMessage());
        }
        break;

    default:
        $view_content = 'views/players.php';
        break;
}

require_once 'views/layout.php';