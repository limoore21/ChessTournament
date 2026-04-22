<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

define('ROOT_PATH', __DIR__ . '/');

require_once ROOT_PATH . 'config.php';

$action = $_GET['action'] ?? 'players';

if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_pass'])) {
    if ($_POST['admin_pass'] === '') {
        $_SESSION['admin'] = true;
        header("Location: index.php?action=bracket");
        exit;
    } else {
        header("Location: index.php?action=players");
        exit;
    }
}

$isAdmin = isset($_SESSION['admin']) && $_SESSION['admin'] === true;

if ($action === 'set_winner_ajax' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (!$isAdmin) {
        echo json_encode(['success' => false, 'error' => 'Доступ запрещен']);
        exit;
    }

    $matchId = (int)($_POST['match_id'] ?? 0);
    $winnerId = (int)($_POST['winner_id'] ?? 0);

    if (!$matchId || !$winnerId) {
        echo json_encode(['success' => false, 'error' => 'Неверные параметры']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT player1_id, player2_id, round_id, player1_score, player2_score, winner_id, is_completed FROM matches WHERE id = ?");
        $stmt->execute([$matchId]);
        $match = $stmt->fetch();

        if (!$match) {
            echo json_encode(['success' => false, 'error' => 'Матч не найден']);
            exit;
        }

        if ($match['winner_id'] !== null || $match['is_completed'] == 1) {
            echo json_encode(['success' => false, 'error' => 'Матч уже завершен']);
            exit;
        }

        $score1 = (int)($match['player1_score'] ?? 0);
        $score2 = (int)($match['player2_score'] ?? 0);

        if ($winnerId == $match['player1_id']) {
            $score1++;
        } else {
            $score2++;
        }

        $winCondition = 2;
        $isMatchCompleted = ($score1 >= $winCondition || $score2 >= $winCondition);

        if ($isMatchCompleted) {
            $pdo->prepare("UPDATE matches SET player1_score = ?, player2_score = ?, winner_id = ?, is_completed = 1 WHERE id = ?")
                ->execute([$score1, $score2, $winnerId, $matchId]);

            $loserId = ($winnerId == $match['player1_id']) ? $match['player2_id'] : $match['player1_id'];
            $pdo->prepare("UPDATE players SET wins = wins + 1 WHERE id = ?")->execute([$winnerId]);
            if ($loserId) {
                $pdo->prepare("UPDATE players SET losses = losses + 1 WHERE id = ?")->execute([$loserId]);
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE round_id = ? AND winner_id IS NULL AND is_completed = 0");
            $stmt->execute([$match['round_id']]);
            $remaining = $stmt->fetchColumn();

            $response = [
                'success' => true,
                'match_completed' => true,
                'new_scores' => ['player1' => $score1, 'player2' => $score2]
            ];

            if ($remaining == 0) {
                require_once 'models/Tournament.php';
                $newRoundId = createNextRoundSafe($pdo, 1, $match['round_id']);
                if ($newRoundId) {
                    $response['new_round'] = getRoundData($pdo, $newRoundId);
                }
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM rounds r JOIN matches m ON m.round_id = r.id WHERE r.tournament_id = 1 AND m.winner_id IS NULL");
            $stmt->execute();
            if ($stmt->fetchColumn() == 0) {
                $stmt = $pdo->prepare("SELECT p.nickname FROM matches m JOIN players p ON m.winner_id = p.id ORDER BY m.id DESC LIMIT 1");
                $stmt->execute();
                $response['champion'] = $stmt->fetchColumn();
            }

        } else {
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
        $sql = "SELECT p.*, 
            (SELECT COUNT(*) FROM matches WHERE winner_id = p.id) as wins,
            (SELECT COUNT(*) FROM matches WHERE (player1_id = p.id OR player2_id = p.id) 
             AND winner_id IS NOT NULL AND winner_id != p.id) as losses
            FROM players p";
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
        $stmt = $pdo->prepare("SELECT m.*, p1.nickname as p1_name, p2.nickname as p2_name, r.round_name 
                            FROM matches m
                            JOIN rounds r ON m.round_id = r.id
                            LEFT JOIN players p1 ON m.player1_id = p1.id
                            LEFT JOIN players p2 ON m.player2_id = p2.id
                            WHERE r.tournament_id = 1 ORDER BY r.round_number ASC");
        $stmt->execute();
        $allMatches = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $rounds = [];
        foreach ($allMatches as $m) {
            $rounds[$m['round_id']][] = $m;
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
        $_SESSION = array();


        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();

        header("Location: index.php?action=players");
        exit;
        break;

    default:
        header("Location: index.php?action=players");
        exit;
}

require_once 'views/layout.php';