<?php

// рисуем турнирную сетку
function drawTournament($pdo, $tournamentId) {
    $players = $pdo->query("SELECT id FROM players")->fetchAll(PDO::FETCH_COLUMN);

    if (count($players) < 2) {
        return false;
    }

    shuffle($players);

    try {
        $pdo->beginTransaction();

        // создаём первый раунд
        $pdo->prepare("INSERT INTO rounds (tournament_id, round_number, round_name) VALUES (?, 1, 'Раунд 1')")->execute([$tournamentId]);
        $roundId = $pdo->lastInsertId();

        // создаём матчи
        $pairs = array_chunk($players, 2);
        foreach ($pairs as $pair) {
            $p1 = $pair[0];
            $p2 = $pair[1] ?? null;

            $pdo->prepare("INSERT INTO matches (round_id, player1_id, player2_id, player1_score, player2_score) VALUES (?, ?, ?, 0, 0)")->execute([$roundId, $p1, $p2]);

            // если игрок без пары - автоматическая победа
            if (!$p2) {
                $matchId = $pdo->lastInsertId();
                $pdo->prepare("UPDATE matches SET winner_id = ?, is_completed = 1 WHERE id = ?")->execute([$p1, $matchId]);
            }
        }

        $pdo->commit();
        return true;

    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

// создаём следующий раунд
function createNextRound($pdo, $tournamentId, $currentRoundId, $ajaxMode = false) {
    // собираем победителей текущего раунда
    $stmt = $pdo->prepare("SELECT winner_id FROM matches WHERE round_id = ? AND winner_id IS NOT NULL");
    $stmt->execute([$currentRoundId]);
    $winners = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($winners) < 2) {
        return $ajaxMode ? null : null;
    }

    // номер следующего раунда
    $stmt = $pdo->prepare("SELECT round_number FROM rounds WHERE id = ?");
    $stmt->execute([$currentRoundId]);
    $nextNum = $stmt->fetchColumn() + 1;

    // создаём новый раунд
    $pdo->prepare("INSERT INTO rounds (tournament_id, round_number, round_name) VALUES (?, ?, ?)")->execute([$tournamentId, $nextNum, "Раунд $nextNum"]);
    $nextRoundId = $pdo->lastInsertId();

    // создаём матчи для нового раунда
    $pairs = array_chunk($winners, 2);
    foreach ($pairs as $pair) {
        $p1 = $pair[0];
        $p2 = $pair[1] ?? null;

        $pdo->prepare("INSERT INTO matches (round_id, player1_id, player2_id, player1_score, player2_score) VALUES (?, ?, ?, 0, 0)")->execute([$nextRoundId, $p1, $p2]);

        // автопобеда если без пары
        if (!$p2) {
            $matchId = $pdo->lastInsertId();
            $pdo->prepare("UPDATE matches SET winner_id = ?, is_completed = 1 WHERE id = ?")->execute([$p1, $matchId]);
        }
    }

    return $ajaxMode ? $nextRoundId : $nextRoundId;
}

function createNextRoundSafe($pdo, $tournamentId, $currentRoundId) {
    return createNextRound($pdo, $tournamentId, $currentRoundId, true);
}

// получаем данные раунда для аякс ответа
function getRoundData($pdo, $roundId) {
    $stmt = $pdo->prepare("
        SELECT 
            matches.id, 
            matches.player1_id, 
            matches.player2_id, 
            matches.player1_score, 
            matches.player2_score, 
            players1.nickname as p1_name, 
            players2.nickname as p2_name, 
            rounds.round_name, 
            rounds.round_number
        FROM matches
        JOIN rounds ON matches.round_id = rounds.id
        LEFT JOIN players as players1 ON matches.player1_id = players1.id
        LEFT JOIN players as players2 ON matches.player2_id = players2.id
        WHERE rounds.id = ?
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
                'player1_score' => (int)$m['player1_score'],
                'player2_score' => (int)$m['player2_score'],
                'can_edit' => true
            ];
        }, $matches)
    ];
}