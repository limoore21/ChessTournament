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
        nav { margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="container">
    <nav>
        <a href="index.php?action=list">Игроки</a> |
        <a href="index.php?action=bracket">Сетка</a> |
        <a href="index.php?action=draw">Провести жеребьевку</a>
    </nav>

    <?php include($view_content); ?>

</div>
</body>
</html>