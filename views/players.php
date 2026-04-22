<h2 class="section-title">УЧАСТНИКИ ТУРНИРА</h2>

<div class="add-card">
    <form method="POST" action="index.php?action=add_player" accept-charset="UTF-8">
        <label>ДОБАВИТЬ УЧАСТНИКА</label>
        <input type="text" name="nickname" placeholder="никнейм" required>
        <button type="submit">ДОБАВИТЬ</button>
    </form>
</div>

<?php if (!empty($players)): ?>
    <table class="players-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>НИКНЕЙМ</th>
            <th>Победы / Поражения</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($players as $player): ?>
            <tr>
                <td><?= $player['id'] ?></td>
                <td><?= htmlspecialchars($player['nickname']) ?></td>
                <td><?= $player['wins'] ?> / <?= $player['losses'] ?></td>
                <td>
                    <?php if ($isAdmin): ?>
                        <a href="?action=delete_player&id=<?= $player['id'] ?>"
                           onclick="return confirm('Удалить?')"
                           class="delete-link">[удалить]</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="info-box">Список участников пуст. Добавьте игроков для начала турнира.</div>
<?php endif; ?>

<?php if (count($players) >= 2): ?>
    <div class="ready-banner">
        <p>⚡ Участников: <?= count($players) ?>. Турнир готов к проведению.</p>
        <?php if ($isAdmin): ?>
            <a href="index.php?action=draw" class="btn-start" onclick="return confirm('Начать жеребьевку?')">НАЧАТЬ ТУРНИР</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="warning-box">
        ⚠️ Необходимо минимум 2 участника. Сейчас: <?= count($players) ?>.
    </div>
<?php endif; ?>