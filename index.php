<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

define('ROOT_PATH', __DIR__ . '/');

require_once ROOT_PATH . 'config.php';
require_once ROOT_PATH . 'models/Elo.php';

$action = $_GET['action'] ?? 'players';

// если пользователь отправил форму входа и нажал кнопку админа
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_pass'])) {
    if ($_POST['admin_pass'] === '1234') {
        // сохраняем в сессии что админ залогинен
        $_SESSION['admin'] = true;
        // перекидываем на турнирную сетку
        header("Location: index.php?action=bracket");
        exit;
    } else {
        // если пароль неверный остаемся на списке игроков
        header("Location: index.php?action=players");
        exit;
    }
}

// проверяем есть ли в сессии метка что админ залогинен
$isAdmin = isset($_SESSION['admin']) && $_SESSION['admin'] === true;

// если пришел аякс запрос на установку победителя
if ($action === 'set_winner_ajax' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // говорим браузеру что ответ будет в формате json
    header('Content-Type: application/json');

    // если пользователь не админ то запрещаем
    if (!$isAdmin) {
        echo json_encode(['success' => false, 'error' => 'Доступ запрещен']);
        exit;
    }

    // достаем из запроса id матча и id победителя
    $matchId = (int)($_POST['match_id'] ?? 0);
    $winnerId = (int)($_POST['winner_id'] ?? 0);

    // если id нет то выдаем ошибку
    if (!$matchId || !$winnerId) {
        echo json_encode(['success' => false, 'error' => 'Неверные параметры']);
        exit;
    }

    try {
        // ищем матч в базе данных по id
        $stmt = $pdo->prepare("SELECT player1_id, player2_id, round_id, player1_score, player2_score, winner_id, is_completed FROM matches WHERE id = ?");
        $stmt->execute([$matchId]);
        $match = $stmt->fetch();

        // если матча нет в базе
        if (!$match) {
            echo json_encode(['success' => false, 'error' => 'Матч не найден']);
            exit;
        }

        // если у матча уже есть победитель или он завершен
        if ($match['winner_id'] !== null || $match['is_completed'] == 1) {
            echo json_encode(['success' => false, 'error' => 'Матч уже завершен']);
            exit;
        }

        // берем текущий счет или ставим 0 если его нет
        $score1 = (int)($match['player1_score'] ?? 0);
        $score2 = (int)($match['player2_score'] ?? 0);

        // прибавляем очко тому кто победил
        if ($winnerId == $match['player1_id']) {
            $score1++;
        } else {
            $score2++;
        }

        // для победы в матче нужно набрать 2 очка
        $winCondition = 2;
        // проверяем набрал ли кто то 2 очка
        $isMatchCompleted = ($score1 >= $winCondition || $score2 >= $winCondition);

        // если матч завершен
        if ($isMatchCompleted) {
            // обновляем матч сохраняем счет победителя и ставим флаг завершения
            $pdo->prepare("UPDATE matches SET player1_score = ?, player2_score = ?, winner_id = ?, is_completed = 1 WHERE id = ?")
                ->execute([$score1, $score2, $winnerId, $matchId]);

            // определяем кто проиграл
            $loserId = ($winnerId == $match['player1_id']) ? $match['player2_id'] : $match['player1_id'];
            // прибавляем победителю одну победу
            $pdo->prepare("UPDATE players SET wins = wins + 1 WHERE id = ?")->execute([$winnerId]);
            // если проигравший есть прибавляем ему одно поражение
            if ($loserId) {
                $pdo->prepare("UPDATE players SET losses = losses + 1 WHERE id = ?")->execute([$loserId]);
            }

            // обновляем рейтинг эло
            updateEloRatings($pdo, $winnerId, $loserId);

            // считаем сколько матчей в этом раунде еще не завершено
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE round_id = ? AND winner_id IS NULL AND is_completed = 0");
            $stmt->execute([$match['round_id']]);
            $remaining = $stmt->fetchColumn();

            $response = [
                'success' => true,
                'match_completed' => true,
                'new_scores' => ['player1' => $score1, 'player2' => $score2]
            ];

            // если все матчи раунда завершены
            if ($remaining == 0) {
                require_once 'models/Tournament.php';
                $newRoundId = createNextRoundSafe($pdo, 1, $match['round_id']);
                if ($newRoundId) {
                    $response['new_round'] = getRoundData($pdo, $newRoundId);
                }
            }

            // проверяем остались ли вообще незавершенные матчи во всем турнире
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM rounds JOIN matches ON matches.round_id = rounds.id WHERE rounds.tournament_id = 1 AND matches.winner_id IS NULL");
            $stmt->execute();
            if ($stmt->fetchColumn() == 0) {
                $stmt = $pdo->prepare("SELECT players.nickname FROM matches JOIN players ON matches.winner_id = players.id ORDER BY matches.id DESC LIMIT 1");
                $stmt->execute();
                $response['champion'] = $stmt->fetchColumn();
            }

        } else {
            // если матч не завершен просто обновляем счет
            $pdo->prepare("UPDATE matches SET player1_score = ?, player2_score = ? WHERE id = ?")
                ->execute([$score1, $score2, $matchId]);

            $response = [
                'success' => true,
                'match_completed' => false,
                'new_scores' => ['player1' => $score1, 'player2' => $score2]
            ];
        }

        echo json_encode($response);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

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

    case 'set_winner':
        if (!$isAdmin) die("Доступ запрещен!");
        $matchId = $_GET['id'] ?? null;
        $winnerId = $_GET['winner_id'] ?? null;
        if ($matchId && $winnerId) {
            $stmt = $pdo->prepare("SELECT player1_id, player2_id FROM matches WHERE id = ?");
            $stmt->execute([$matchId]);
            $match = $stmt->fetch();
            if ($match) {
                $loserId = ($winnerId == $match['player1_id']) ? $match['player2_id'] : $match['player1_id'];
                $pdo->prepare("UPDATE matches SET winner_id = ? WHERE id = ?")->execute([$winnerId, $matchId]);
                $pdo->prepare("UPDATE players SET wins = wins + 1 WHERE id = ?")->execute([$winnerId]);
                if ($loserId) {
                    $pdo->prepare("UPDATE players SET losses = losses + 1 WHERE id = ?")->execute([$loserId]);
                }
            }
            $stmt = $pdo->prepare("SELECT round_id FROM matches WHERE id = ?");
            $stmt->execute([$matchId]);
            $currentRoundId = $stmt->fetchColumn();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE round_id = ? AND winner_id IS NULL");
            $stmt->execute([$currentRoundId]);
            if ($stmt->fetchColumn() == 0) {
                require_once 'models/Tournament.php';
                createNextRound($pdo, 1, $currentRoundId);
            }
        }
        header("Location: index.php?action=bracket");
        exit;

    case 'players':
        $sql = "SELECT players.*, 
            (SELECT COUNT(*) FROM matches WHERE matches.winner_id = players.id) as wins,
            (SELECT COUNT(*) FROM matches WHERE (matches.player1_id = players.id OR matches.player2_id = players.id) 
             AND matches.winner_id IS NOT NULL AND matches.winner_id != players.id) as losses
            FROM players";
        $players = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $players = $players ?: [];
        $view_content = 'views/players.php';
        break;

    case 'draw':
        if (!$isAdmin) die("Доступ запрещен!");
        require_once 'models/Tournament.php';
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE matches; TRUNCATE rounds; SET FOREIGN_KEY_CHECKS = 1;");
        if (drawTournament($pdo, 1)) {
            header("Location: index.php?action=bracket");
            exit;
        } else {
            die("Ошибка жеребьевки!");
        }
        break;

    case 'bracket':
        $view_content = 'views/bracket.php';
        $stmt = $pdo->prepare("SELECT matches.*, players1.nickname as p1_name, players2.nickname as p2_name, rounds.round_name 
                            FROM matches
                            JOIN rounds ON matches.round_id = rounds.id
                            LEFT JOIN players as players1 ON matches.player1_id = players1.id
                            LEFT JOIN players as players2 ON matches.player2_id = players2.id
                            WHERE rounds.tournament_id = 1 ORDER BY rounds.round_number ASC");
        $stmt->execute();
        $allMatches = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $rounds = [];
        foreach ($allMatches as $matchRow) {
            $rounds[$matchRow['round_id']][] = $matchRow;
        }
        $final_winner = null;
        if (!empty($rounds)) {
            $lastRound = end($rounds);
            if (count($lastRound) === 1 && !empty($lastRound[0]['winner_id'])) {
                $stmt = $pdo->prepare("SELECT nickname FROM players WHERE id = ?");
                $stmt->execute([$lastRound[0]['winner_id']]);
                $final_winner = $stmt->fetchColumn();
            }
        }
        break;

    case 'logout':
        session_destroy();
        header("Location: index.php?action=players");
        exit;

    case 'reset_players':
        if (!$isAdmin) die("Доступ запрещен!");

        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $pdo->exec("TRUNCATE players");
        $pdo->exec("TRUNCATE matches");
        $pdo->exec("TRUNCATE rounds");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        $pdo->exec("INSERT IGNORE INTO tournaments (id, name) VALUES (1, 'Главный турнир')");

        header("Location: index.php?action=players");
        exit;

    default:
        header("Location: index.php?action=players");
        exit;
}

require_once 'views/layout.php';