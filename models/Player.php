<?php

// 1. ДОБАВЛЕНИЕ
function ins($pdo, $nickname) {
    $sql = "INSERT INTO players (nickname) VALUES (:nickname)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['nickname' => $nickname]);
}

// 2. РЕДАКТИРОВАНИЕ
function updatePlayer($pdo, $id, $newNickname) {
    $sql = "UPDATE players SET nickname = :nickname WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        'nickname' => $newNickname,
        'id' => $id
    ]);
}

// 3. УДАЛЕНИЕ
function delPlayer($pdo, $id) {
    // благодаря CONSTRAINT ... ON DELETE CASCADE в SQL,
    // при удалении игрока из таблицы players, он сам удалится
    // из турнирных таблиц
    $sql = "DELETE FROM players WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['id' => $id]);
}

// 4. ПОЛУЧЕНИЕ ОДНОГО
function getPlayer($pdo, $id) {
    $sql = "SELECT * FROM players WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
}

//Получение всех плееров
function getAllPlayers($pdo) {
    $sql = "SELECT * FROM players ORDER BY id DESC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}