<?php
function drawTournament($pdo, $tournamentId) {
    $stmt = $pdo->query("SELECT id FROM players");
    $players = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($players) < 2) {
        return false;
    }

    shuffle($players);

    try {
        $pdo->beginTransaction();


        $stmt = $pdo->prepare("INSERT INTO rounds (tournament_id, round_number, round_name) VALUES (?, 1, 'Round 1')");        $stmt->execute([$tournamentId]);
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
            }
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Критическая ошибка SQL: " . $e->getMessage());
    }
}