<?php

function ins($pdo, $nickname) {
    $sql = "INSERT INTO players (nickname) VALUES (:nickname)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['nickname' => $nickname]);
}

function updatePlayer($pdo, $id, $newNickname) {
    $sql = "UPDATE players SET nickname = :nickname WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        'nickname' => $newNickname,
        'id' => $id
    ]);
}

function delPlayer($pdo, $id) {
    $sql = "DELETE FROM players WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['id' => $id]);
}

function getPlayer($pdo, $id) {
    $sql = "SELECT * FROM players WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
}

function getAllPlayers($pdo) {
    $sql = "SELECT * FROM players ORDER BY id DESC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}