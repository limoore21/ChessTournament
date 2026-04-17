
<h2>Турнирная сетка</h2>

<?php if (isset($final_winner) && $final_winner): ?>
    <div style="
                color: #000;
                padding: 30px;
                text-align: center;
                border-radius: 15px;
                margin-bottom: 30px;
                box-shadow: 0 10px 20px rgba(0,0,0,0.2);
                border: 4px solid #fff;">
        <h1 style="margin: 0; font-size: 40px;">🏆 ЧЕМПИОН НАЙДЕН! 🏆</h1>
        <p style="font-size: 24px; margin: 10px 0;">Поздравляем игрока
            <strong style="font-size: 32px; text-transform: uppercase;">
                <?= htmlspecialchars($final_winner) ?>
            </strong>
            теперь он официально лучший в этом турнире!</p>
        <div style="font-size: 50px;">🎉🎊🔥</div>
    </div>
<?php endif; ?>

<?php if (empty($rounds)): ?>
    <div style="padding: 20px; border: 1px dashed #ccc; background: #f9f9f9; text-align: center;">
        <p>Данные о матчах отсутствуют. Жеребьевка еще не проводилась.</p>
        <a href="index.php?action=draw" style="color: #2196f3; font-weight: bold; text-decoration: none;">Создать сетку сейчас</a>
    </div>
<?php else: ?>

    <div style="display: flex; gap: 40px; align-items: flex-start; overflow-x: auto; padding: 20px 0;">

        <?php foreach ($rounds as $round_id => $matches): ?>
            <div class="round-column" style="min-width: 220px;">
                <h3 style="text-align: center; background: #333; color: #fff; padding: 10px; border-radius: 4px; margin-top: 0; font-size: 16px;">
                    <?= htmlspecialchars($matches[0]['round_name'] ?? "Раунд $round_id") ?>
                </h3>

                <?php foreach ($matches as $match): ?>
                    <div style="border: 2px solid #333; margin-bottom: 20px; background: #fff; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); position: relative;">
                        <div style="background: #333; color: #fff; padding: 4px; font-size: 10px; text-align: center;">
                            ID Матча: <?= $match['id'] ?>
                        </div>

                        <div style="padding: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <span style="<?= (isset($match['winner_id']) && $match['winner_id'] == $match['player1_id']) ? 'font-weight: bold; color: #1b5e20;' : '' ?>">
                                    <?= htmlspecialchars($match['p1_name'] ?? 'Ожидание...') ?>
                                </span>
                                <?php if (empty($match['winner_id']) && !empty($match['player1_id']) && !empty($match['player2_id'])): ?>
                                    <a href="?action=set_winner&match_id=<?= $match['id'] ?>&player_id=<?= $match['player1_id'] ?>&round_id=<?= $match['round_id'] ?>"
                                       style="background: #4caf50; color: white; padding: 4px 8px; text-decoration: none; border-radius: 3px; font-size: 10px; font-weight: bold;">WIN</a>
                                <?php endif; ?>
                            </div>

                            <div style="text-align: center; border-top: 1px solid #eee; height: 1px; margin: 10px 0;"></div>

                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="<?= (isset($match['winner_id']) && $match['winner_id'] == $match['player2_id']) ? 'font-weight: bold; color: #1b5e20;' : '' ?>">
                                    <?= htmlspecialchars($match['p2_name'] ?? 'Ожидание...') ?>
                                </span>
                                <?php if (empty($match['winner_id']) && !empty($match['player1_id']) && !empty($match['player2_id'])): ?>
                                    <a href="?action=set_winner&match_id=<?= $match['id'] ?>&player_id=<?= $match['player2_id'] ?>&round_id=<?= $match['round_id'] ?>"
                                       style="background: #4caf50; color: white; padding: 4px 8px; text-decoration: none; border-radius: 3px; font-size: 10px; font-weight: bold;">WIN</a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($match['winner_id'])): ?>
                            <div style="background: #f1f8e9; color: #1b5e20; padding: 5px; text-align: center; font-size: 11px; font-weight: bold; border-top: 1px solid #c8e6c9;">
                                Матч завершен
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

    </div>

    <div style="margin-top: 40px; border-top: 2px solid #eee; padding-top: 20px;">
        <a href="index.php?action=draw"
           onclick="return confirm('Внимание! Все текущие результаты будут удалены. Вы уверены?')"
           style="display: inline-block; color: #d32f2f; text-decoration: none; font-size: 14px; border: 1px solid #d32f2f; padding: 8px 16px; border-radius: 4px;">
            Сбросить и начать заново
        </a>
    </div>
<?php endif; ?>