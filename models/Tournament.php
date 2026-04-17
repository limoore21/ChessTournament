<?php

function drawTournament($pdo, $tournament_id) {
    // 1. Получаем всех участников турнира
    $sql = "SELECT player_id FROM tournament_participants WHERE tournament_id = :t_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['t_id' => $tournament_id]);
    $participants = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($participants) < 2) return false;

    // 2. Рандом, Перемешиваем массив
    shuffle($participants);

    // 3. Создаем первый раунд (Раунд №1)
    $sql_round = "INSERT INTO rounds (tournament_id, round_number, round_name) VALUES (:t_id, 1, '1/4 Финала')";
    $pdo->prepare($sql_round)->execute(['t_id' => $tournament_id]);
    $round_id = $pdo->lastInsertId();

    // 4. Разбиваем игроков на пары и записываем в матчи
    $pairs = array_chunk($participants, 2);

    foreach ($pairs as $pair) {
        $p1 = $pair[0];
        $p2 = isset($pair[1]) ? $pair[1] : null; // Если игрока без пары, p2 = null
        $winner = ($p2 === null) ? $p1 : null;   // Если пары нет, p1 сразу победитель

        $sql_match = "INSERT INTO matches (round_id, player1_id, player2_id, winner_id) 
                      VALUES (:r_id, :p1, :p2, :winner)";
        $pdo->prepare($sql_match)->execute([
            'r_id' => $round_id,
            'p1' => $p1,
            'p2' => $p2,
            'winner' => $winner
        ]);
    }
    return true;
}