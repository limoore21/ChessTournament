<!doctype html>
<html lang="en">
<head>
    <title>Edit player</title>
</head>
<body>
<h2>Редактировать игрока</h2>
<form method="POST" action="?action=update_player">
    <input type="hidden" name="id" value="<?= $player['id'] ?>">
    <input type="text" name="nickname" value="<?= htmlspecialchars($player['nickname']) ?>" required>
    <button type="submit">Сохранить</button>
    <a href="?action=list">Отмена</a>
</form>

</body>
</html>