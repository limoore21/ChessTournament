<h2>Участники турнира</h2>

<form method="POST" action="?action=add_player" style="margin-bottom: 20px;">
    <input type="text" name="nickname" placeholder="Никнейм игрока" required>
    <button type="submit">Добавить</button>
</form>

<table border="1" width="100%" cellpadding="5" style="border-collapse: collapse;">
    <thead>
    <tr>
        <th>ID</th>
        <th>Никнейм</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($players as $player): ?>
        <tr>
            <td><?= $player['id'] ?></td>
            <td><?= htmlspecialchars($player['nickname']) ?></td>
            <td>
                <a href="?action=delete_player&id=<?= $player['id'] ?>"
                   onclick="return confirm('Удалить игрока?')">Удалить</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php if (count($players) < 6): ?>
    <p style="color: orange;">Нужно минимум 6 игроков для начала.</p>
<?php endif; ?>