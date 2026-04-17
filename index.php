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
        $sql = "SELECT matches.*, players1.nickname AS p1_name, players2.nickname AS p2_name, rounds.round_name
            FROM matches
            JOIN rounds ON matches.round_id = rounds.id
            LEFT JOIN players AS players1 ON matches.player1_id = players1.id
            LEFT JOIN players AS players2 ON matches.player2_id = players2.id
            WHERE rounds.tournament_id = 1
            ORDER BY rounds.round_number ASC, matches.id ASC";

        $stmt = $pdo->query($sql);
        $all_matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $rounds = [];
        $final_winner = null;

        foreach ($all_matches as $match) {
            $rounds[$match['round_id']][] = $match;

            if ($match['round_name'] === 'Финал' && !empty($match['winner_id'])) {
                $final_winner = ($match['winner_id'] == $match['player1_id']) ? $match['p1_name'] : $match['p2_name'];
            }
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

    case 'set_winner':
        $match_id = (int)$_GET['match_id'];
        $player_id = (int)$_GET['player_id'];
        $round_id = (int)$_GET['round_id'];
        $tournament_id = 1;

        $stmt = $pdo->prepare("UPDATE matches SET winner_id = ? WHERE id = ?");
        $stmt->execute([$player_id, $match_id]);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE round_id = ? AND winner_id IS NULL");
        $stmt->execute([$round_id]);
        $unfinished = $stmt->fetchColumn();

        if ($unfinished == 0) {
            $stmt = $pdo->prepare("SELECT winner_id FROM matches WHERE round_id = ? ORDER BY id");
            $stmt->execute([$round_id]);
            $winners = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (count($winners) > 1) {
                $stmt = $pdo->prepare("SELECT round_number FROM rounds WHERE id = ?");
                $stmt->execute([$round_id]);
                $current_num = $stmt->fetchColumn();

                $next_num = $current_num + 1;
                $round_names = [2 => 'Полуфинал', 3 => 'Финал'];
                $name = isset($round_names[$next_num]) ? $round_names[$next_num] : "Раунд $next_num";

                $stmt = $pdo->prepare("INSERT INTO rounds (tournament_id, round_number, round_name) VALUES (?, ?, ?)");
                $stmt->execute([$tournament_id, $next_num, $name]);
                $next_round_id = $pdo->lastInsertId();

                $pairs = array_chunk($winners, 2);
                foreach ($pairs as $pair) {
                    $p1 = $pair[0];
                    $p2 = isset($pair[1]) ? $pair[1] : null;
                    $win = ($p2 === null) ? $p1 : null;

                    $stmt = $pdo->prepare("INSERT INTO matches (round_id, player1_id, player2_id, winner_id) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$next_round_id, $p1, $p2, $win]);
                }
            }
        }

        header("Location: index.php?action=bracket");
        exit;

    default:
        $view_content = 'views/players.php';
        break;
}

require_once 'views/layout.php';