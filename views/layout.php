<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Chess Tournament</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: #fff; padding: 20px; }
        .bracket { display: flex; justify-content: space-between; }
        .round { display: flex; flex-direction: column; justify-content: space-around; }
        .match { border: 1px solid #ccc; padding: 10px; margin: 10px 0; background: #eee; width: 150px; }
        .player { padding: 5px; border-bottom: 1px solid #ddd; }
        .winner { background: #d4edda; font-weight: bold; }
        nav { margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
    </style>
</head>
<body>
<div class="container">
    <nav>
        <a href="index.php?action=players">Игроки</a> |
        <a href="index.php?action=bracket">Сетка</a> |
        <?php if ($isAdmin): ?>
            <a href="index.php?action=draw">Провести жеребьевку</a> |
            <a href="index.php?logout=1" style="color: gray;">Выйти</a>
        <?php else: ?>
            <a href="index.php?login=admin" style="color: green;">Админ</a>
        <?php endif; ?>
    </nav>

    <?php include($view_content); ?>

</div>
</body>
</html>