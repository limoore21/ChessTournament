<?php
if (!isset($players)) {
    $players = [];
}
?>
<h2>Участники турнира</h2>

<div style="margin-bottom: 30px; padding: 15px; border: 1px solid #ccc; background: #fafafa;">
    <form method="POST" action="index.php?action=add_player">
        <label for="nickname">Добавить нового участника:</label><br><br>
        <input type="text" id="nickname" name="nickname" placeholder="Введите никнейм" required
               style="padding: 8px; width: 200px;">
        <button type="submit" style="padding: 8px 15px; cursor: pointer;">Добавить</button>
    </form>
</div>

<table border="1" width="100%" cellpadding="8" style="border-collapse: collapse; text-align: left;">
    <thead>
    <tr style="background: #eee;">
        <th>ID</th>
        <th>Никнейм</th>
        <th>История (W/L)</th>
        <th style="width: 100px;">Действие</th>
    </tr>
    </thead>
    <tbody>
    <?php if (!empty($players)): ?>
        <?php foreach ($players as $player): ?>
            <tr>
                <td><?= $player['id'] ?></td>
                <td><?= htmlspecialchars($player['nickname']) ?></td>
                <td>
                    <span style="color: green; font-weight: bold;"><?= $player['wins'] ?></span> /
                    <span style="color: red; font-weight: bold;"><?= $player['losses'] ?></span>
                </td>
                <td>
                    <?php if ($isAdmin): ?>
                        <a href="?action=delete_player&id=<?= $player['id'] ?>"
                           onclick="return confirm('Вы уверены, что хотите удалить этого игрока?')"
                           style="color: #d9534f; text-decoration: none;">Удалить</a>
                    <?php else: ?>
                        <span style="color: #ccc;">Нет прав</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="4" style="text-align: center;">Список игроков пуст</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

<div style="margin-top: 30px;">
    <?php if (count($players) >= 6): ?>
        <div style="padding: 20px; background: #dff0d8; border: 1px solid #d6e9c6; color: #3c763d;">
            <p>Достаточно участников для начала турнира (всего: <?= count($players) ?>).</p>
            <a href="index.php?action=draw"
               style="display: inline-block; padding: 12px 25px; background: #5cb85c; color: #fff; text-decoration: none; border-radius: 3px; font-weight: bold;">
                ПРОВЕСТИ ЖЕРЕБЬЕВКУ
            </a>
        </div>
    <?php else: ?>
        <div style="padding: 20px; background: #fcf8e3; border: 1px solid #faebcc; color: #8a6d3b;">
            <p>Недостаточно участников для формирования сетки. Минимум: 6. Сейчас: <?= count($players) ?>.</p>
        </div>
    <?php endif; ?>
</div>