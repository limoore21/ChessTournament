<?php

// получить рейтинг игрока
function getEloRating($pdo, $playerId)
{
    $stmt = $pdo->prepare("SELECT elo_rating FROM players WHERE id = ?");
    $stmt->execute([$playerId]);
    $rating = $stmt->fetchColumn();
    if ($rating === false) {
        return 1200;
    }
    return (int)$rating;
}

// обновить рейтинг игрока
function setEloRating($pdo, $playerId, $rating)
{
    $stmt = $pdo->prepare("UPDATE players SET elo_rating = ? WHERE id = ?");
    $stmt->execute([$rating, $playerId]);
}

// посчитать ожидаемый результат (вероятность победы)
function expectedScore($ratingA, $ratingB)
{
    return 1 / (1 + pow(10, ($ratingB - $ratingA) / 400));
}

// обновить рейтинги после матча
function updateEloRatings($pdo, $winnerId, $loserId, $kFactor = 32)
{
    // текущие рейтинги
    $winnerRating = getEloRating($pdo, $winnerId);
    $loserRating = getEloRating($pdo, $loserId);

    // ожидаемый результат
    $expectedWinner = expectedScore($winnerRating, $loserRating);
    $expectedLoser = expectedScore($loserRating, $winnerRating);

    // новые рейтинги
    $newWinnerRating = round($winnerRating + $kFactor * (1 - $expectedWinner));
    $newLoserRating = round($loserRating + $kFactor * (0 - $expectedLoser));

    // сохраняем
    setEloRating($pdo, $winnerId, $newWinnerRating);
    setEloRating($pdo, $loserId, $newLoserRating);

//возвращаем ответ для браузера в виде двухмерного ассоциативного массива
    return [
        'winner' => [
            'old' => $winnerRating,
            'new' => $newWinnerRating
        ],
        'loser' => [
            'old' => $loserRating,
            'new' => $newLoserRating
        ]
    ];
}