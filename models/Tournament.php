<?php
function drawTournament($pdo, $tournamentId) {
    $stmt = $pdo->query("SELECT id FROM players");
    $players = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($players) < 2) {
        error_log("drawTournament: Need at least 2 players, have " . count($players));
        return false;
    }

    shuffle($players);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT id FROM tournaments WHERE id = ?");
        $stmt->execute([$tournamentId]);
        if (!$stmt->fetch()) {
            $pdo->prepare("INSERT INTO tournaments (id, name) VALUES (?, 'Main Tournament') ON DUPLICATE KEY UPDATE name = name")
                ->execute([$tournamentId]);
        }

        $stmt = $pdo->prepare("INSERT INTO rounds (tournament_id, round_number, round_name) VALUES (?, 1, 'Round 1')");
        $stmt->execute([$tournamentId]);
        $roundId = $pdo->lastInsertId();

        $pairs = array_chunk($players, 2);
        foreach ($pairs as $pair) {
            $p1 = $pair[0];
            $p2 = isset($pair[1]) ? $pair[1] : null;

            $stmt = $pdo->prepare("INSERT INTO matches (round_id, player1_id, player2_id) VALUES (?, ?, ?)");
            $stmt->execute([$roundId, $p1, $p2]);

            if ($p2 === null) {
                $matchId = $pdo->lastInsertId();
                $pdo->prepare("UPDATE matches SET winner_id = ? WHERE id = ?")->execute([$p1, $matchId]);
                error_log("Auto-win for player $p1 (bye)");
            }
        }

        $pdo->commit();
        error_log("drawTournament: Successfully created " . count($pairs) . " matches");
        return true;

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("drawTournament error: " . $e->getMessage());
        die("SQL Error: " . $e->getMessage());
    }
}

function createNextRound($pdo, $tournamentId, $currentRoundId) {
    $stmt = $pdo->prepare("SELECT winner_id FROM matches WHERE round_id = ? AND winner_id IS NOT NULL");
    $stmt->execute([$currentRoundId]);
    $winners = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($winners) < 2) return;

    $stmt = $pdo->prepare("SELECT round_number FROM rounds WHERE id = ?");
    $stmt->execute([$currentRoundId]);
    $currentNum = $stmt->fetchColumn();
    $nextNum = $currentNum + 1;

    $stmt = $pdo->prepare("INSERT INTO rounds (tournament_id, round_number, round_name) VALUES (?, ?, ?)");
    $stmt->execute([$tournamentId, $nextNum, "Round $nextNum"]);
    $nextRoundId = $pdo->lastInsertId();

    $pairs = array_chunk($winners, 2);
    foreach ($pairs as $pair) {
        $p1 = $pair[0];
        $p2 = $pair[1] ?? null;

        $stmt = $pdo->prepare("INSERT INTO matches (round_id, player1_id, player2_id) VALUES (?, ?, ?)");
        $stmt->execute([$nextRoundId, $p1, $p2]);

        if (!$p2) {
            $matchId = $pdo->lastInsertId();
            $pdo->prepare("UPDATE matches SET winner_id = ? WHERE id = ?")->execute([$p1, $matchId]);
        }
    }
}

function createNextRoundAjax($pdo, $tournamentId, $currentRoundId) {
    $stmt = $pdo->prepare("SELECT winner_id FROM matches WHERE round_id = ? AND winner_id IS NOT NULL");
    $stmt->execute([$currentRoundId]);
    $winners = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($winners) < 2) return null;

    $stmt = $pdo->prepare("SELECT round_number FROM rounds WHERE id = ?");
    $stmt->execute([$currentRoundId]);
    $currentNum = $stmt->fetchColumn();
    $nextNum = $currentNum + 1;

    $stmt = $pdo->prepare("INSERT INTO rounds (tournament_id, round_number, round_name) VALUES (?, ?, ?)");
    $stmt->execute([$tournamentId, $nextNum, "Round $nextNum"]);
    $nextRoundId = $pdo->lastInsertId();

    $pairs = array_chunk($winners, 2);
    foreach ($pairs as $pair) {
        $p1 = $pair[0];
        $p2 = $pair[1] ?? null;

        $stmt = $pdo->prepare("INSERT INTO matches (round_id, player1_id, player2_id) VALUES (?, ?, ?)");
        $stmt->execute([$nextRoundId, $p1, $p2]);

        if (!$p2) {
            $matchId = $pdo->lastInsertId();
            $pdo->prepare("UPDATE matches SET winner_id = ? WHERE id = ?")->execute([$p1, $matchId]);
        }
    }

    return $nextRoundId;
}
function createNextRoundSafe($pdo, $tournamentId, $currentRoundId) {
    $stmt = $pdo->prepare("SELECT winner_id FROM matches WHERE round_id = ? AND winner_id IS NOT NULL");
    $stmt->execute([$currentRoundId]);
    $winners = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($winners) < 2) return null;

    $stmt = $pdo->prepare("SELECT round_number FROM rounds WHERE id = ?");
    $stmt->execute([$currentRoundId]);
    $currentNum = $stmt->fetchColumn();
    $nextNum = $currentNum + 1;

    $stmt = $pdo->prepare("INSERT INTO rounds (tournament_id, round_number, round_name) VALUES (?, ?, ?)");
    $stmt->execute([$tournamentId, $nextNum, "Раунд $nextNum"]);
    $nextRoundId = $pdo->lastInsertId();

    $pairs = array_chunk($winners, 2);
    foreach ($pairs as $pair) {
        $p1 = $pair[0];
        $p2 = $pair[1] ?? null;

        $stmt = $pdo->prepare("INSERT INTO matches (round_id, player1_id, player2_id, player1_score, player2_score) VALUES (?, ?, ?, 0, 0)");
        $stmt->execute([$nextRoundId, $p1, $p2]);

        if (!$p2) {
            $matchId = $pdo->lastInsertId();
            $pdo->prepare("UPDATE matches SET winner_id = ?, is_completed = 1 WHERE id = ?")->execute([$p1, $matchId]);
        }
    }

    return $nextRoundId;
}

function getRoundData($pdo, $roundId) {
    $stmt = $pdo->prepare("
        SELECT m.*, p1.nickname as p1_name, p2.nickname as p2_name, r.round_name, r.round_number
        FROM matches m
        JOIN rounds r ON m.round_id = r.id
        LEFT JOIN players p1 ON m.player1_id = p1.id
        LEFT JOIN players p2 ON m.player2_id = p2.id
        WHERE r.id = ?
    ");
    $stmt->execute([$roundId]);
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($matches)) return null;

    return [
        'round_id' => $roundId,
        'round_number' => $matches[0]['round_number'],
        'round_name' => $matches[0]['round_name'],
        'matches' => array_map(function($m) {
            return [
                'id' => $m['id'],
                'player1_id' => $m['player1_id'],
                'player2_id' => $m['player2_id'],
                'p1_name' => $m['p1_name'],
                'p2_name' => $m['p2_name'],
                'player1_score' => $m['player1_score'] ?? 0,
                'player2_score' => $m['player2_score'] ?? 0,
                'can_edit' => true
            ];
        }, $matches)
    ];
}