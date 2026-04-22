<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Шатхматный турнирный менеджер</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            background: #2c2c2c;
            background-image: radial-gradient(#3a3a3a 1px, transparent 1px);
            background-size: 30px 30px;
            min-height: 100vh;
            padding: 30px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: #f5f0e8;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            overflow: hidden;
            border: 1px solid #c4b59a;
        }

        /* Шапка */
        .header {
            background: linear-gradient(to bottom, #1a1a2e, #16213e);
            padding: 20px 30px;
            border-bottom: 3px solid #c9a84c;
        }

        .header h1 {
            color: #e8d5a8;
            font-size: 28px;
            letter-spacing: 2px;
            font-weight: normal;
        }

        .header h1 small {
            font-size: 14px;
            color: #8b7a5c;
            letter-spacing: 1px;
        }

        /* Навигация */
        nav {
            background: #2c241a;
            padding: 0 30px;
            display: flex;
            gap: 5px;
            border-bottom: 1px solid #4a3f2c;
        }

        nav a {
            color: #d4c5a9;
            text-decoration: none;
            padding: 12px 24px;
            transition: all 0.2s ease;
            font-size: 14px;
            letter-spacing: 1px;
            border-bottom: 2px solid transparent;
        }

        nav a:hover {
            color: #e8d5a8;
            background: #3a3024;
            border-bottom-color: #c9a84c;
        }

        /* Контент */
        .content {
            padding: 30px;
        }

        /* Заголовки секций */
        .section-title {
            font-size: 24px;
            color: #2c241a;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #c9a84c;
            display: inline-block;
            font-weight: normal;
            letter-spacing: 1px;
        }

        .add-card {
            background: #fff8f0;
            border: 1px solid #e0d5c0;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 4px;
        }

        .add-card label {
            display: block;
            color: #4a3f2c;
            margin-bottom: 10px;
            font-size: 14px;
            letter-spacing: 1px;
        }

        .add-card input {
            padding: 10px 15px;
            width: 260px;
            border: 1px solid #d4c5a9;
            background: #fff;
            font-family: 'Georgia', serif;
            font-size: 14px;
            border-radius: 4px;
        }

        .add-card input:focus {
            outline: none;
            border-color: #c9a84c;
            box-shadow: 0 0 5px rgba(201,168,76,0.3);
        }

        .add-card button {
            padding: 10px 24px;
            background: #4a3f2c;
            color: #e8d5a8;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Georgia', serif;
            font-size: 14px;
            letter-spacing: 1px;
            transition: background 0.2s;
        }

        .add-card button:hover {
            background: #5c4f38;
        }

        .players-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff8f0;
            border: 1px solid #e0d5c0;
        }

        .players-table thead {
            background: #e8ddd0;
        }

        .players-table th {
            padding: 12px;
            text-align: left;
            color: #2c241a;
            font-weight: normal;
            letter-spacing: 1px;
            font-size: 14px;
            border-bottom: 2px solid #c9a84c;
        }

        .players-table td {
            padding: 12px;
            border-bottom: 1px solid #ede5d8;
            color: #3a3024;
        }

        .players-table tbody tr:hover {
            background: #f5efe5;
        }

        .delete-link {
            color: #8b5a4c;
            text-decoration: none;
            font-size: 13px;
        }

        .delete-link:hover {
            color: #a03a2a;
        }

        .ready-banner {
            background: #e8ddd0;
            border-left: 4px solid #c9a84c;
            padding: 20px;
            margin-top: 30px;
        }

        .ready-banner p {
            margin-bottom: 15px;
            color: #2c241a;
        }

        .btn-start {
            display: inline-block;
            padding: 10px 30px;
            background: #4a3f2c;
            color: #e8d5a8;
            text-decoration: none;
            border-radius: 4px;
            letter-spacing: 1px;
            font-size: 14px;
            transition: background 0.2s;
        }

        .btn-start:hover {
            background: #5c4f38;
        }

        .btn-reset {
            display: inline-block;
            padding: 8px 20px;
            background: #8b5a4c;
            color: #f5f0e8;
            text-decoration: none;
            border-radius: 4px;
            font-size: 13px;
        }

        .btn-reset:hover {
            background: #6b4234;
        }

        .bracket-wrapper {
            overflow-x: auto;
        }

        .rounds-grid {
            display: flex;
            gap: 40px;
            justify-content: center;
            min-width: min-content;
            padding: 10px;
        }

        .round {
            min-width: 260px;
            background: #fff8f0;
            border: 1px solid #e0d5c0;
            border-radius: 4px;
            overflow: hidden;
        }

        .round-title {
            background: #2c241a;
            color: #e8d5a8;
            padding: 10px;
            text-align: center;
            font-size: 14px;
            letter-spacing: 1px;
            border-bottom: 2px solid #c9a84c;
        }

        .match {
            padding: 12px;
            border-bottom: 1px solid #ede5d8;
        }

        .match:last-child {
            border-bottom: none;
        }

        .match-id {
            font-size: 10px;
            color: #8b7a5c;
            text-align: center;
            margin-bottom: 8px;
        }

        .player-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 5px;
            margin: 4px 0;
            border-radius: 3px;
        }

        .player-name {
            font-size: 14px;
        }

        .player-name.waiting {
            color: #b0a590;
            font-style: italic;
        }

        .player-name.winner {
            color: #2c5f2d;
            font-weight: bold;
        }

        .win-link {
            font-size: 11px;
            color: #c9a84c;
            text-decoration: none;
            padding: 2px 8px;
            border: 1px solid #c9a84c;
            border-radius: 3px;
        }

        .win-link:hover {
            background: #c9a84c;
            color: #2c241a;
        }

        .vs {
            text-align: center;
            font-size: 10px;
            color: #b0a590;
            margin: 5px 0;
        }

        .match-status {
            font-size: 10px;
            text-align: center;
            margin-top: 8px;
            color: #6b8c42;
        }

        .champion-card {
            background: linear-gradient(135deg, #2c241a, #3a3024);
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
            border: 1px solid #c9a84c;
        }

        .champion-card h2 {
            color: #e8d5a8;
            font-size: 18px;
            font-weight: normal;
            letter-spacing: 2px;
        }

        .champion-name {
            font-size: 32px;
            color: #c9a84c;
            margin: 15px 0;
            font-weight: bold;
        }

        .info-box {
            background: #e8ddd0;
            padding: 15px;
            border-left: 4px solid #c9a84c;
            margin-bottom: 20px;
        }

        .warning-box {
            background: #f5e5d5;
            padding: 15px;
            border-left: 4px solid #8b5a4c;
            margin-bottom: 20px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff8f0;
            padding: 30px;
            width: 320px;
            border: 2px solid #c9a84c;
            z-index: 1000;
        }

        .modal.active {
            display: block;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.6);
            z-index: 999;
        }

        .modal-overlay.active {
            display: block;
        }

        .modal h4 {
            color: #2c241a;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: normal;
        }

        .modal input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #d4c5a9;
            background: #fff;
            font-family: 'Georgia', serif;
        }

        .modal button {
            width: 100%;
            padding: 10px;
            margin-bottom: 8px;
            border: none;
            cursor: pointer;
            font-family: 'Georgia', serif;
        }

        .btn-submit {
            background: #4a3f2c;
            color: #e8d5a8;
        }

        .btn-cancel {
            background: #b0a590;
            color: #fff;
        }

        .footer {
            background: #2c241a;
            padding: 15px;
            text-align: center;
            color: #8b7a5c;
            font-size: 11px;
            border-top: 1px solid #4a3f2c;
        }

        @media (max-width: 800px) {
            body { padding: 15px; }
            .rounds-grid { flex-direction: column; align-items: center; }
            .add-card input { width: 100%; margin-bottom: 10px; }
        }
        .bracket-wrapper {
            overflow-x: auto;
            padding: 10px 0;
        }

        .rounds-grid {
            display: flex;
            gap: 30px;
            justify-content: center;
            min-width: min-content;
        }

        .round {
            background: #fff8f0;
            border: 2px solid #c9a84c;
            border-radius: 8px;
            min-width: 320px;
            max-width: 350px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .round-title {
            background: #2c241a;
            color: #e8d5a8;
            padding: 14px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 2px;
            border-bottom: 2px solid #c9a84c;
        }

        .round-number {
            display: inline-block;
            background: #c9a84c;
            color: #2c241a;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 20px;
            margin-left: 10px;
        }

        .matches-container {
            padding: 15px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .match-card {
            background: #ffffff;
            border: 1px solid #e0d5c0;
            border-radius: 8px;
            padding: 12px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .match-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            border-color: #c9a84c;
        }

        .match-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 8px;
            margin-bottom: 10px;
            border-bottom: 1px dashed #ede5d8;
            font-size: 11px;
        }

        .match-number {
            color: #8b7a5c;
            font-weight: bold;
        }

        .match-completed {
            color: #2c5f2d;
            background: #e8f0e5;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
        }

        .match-players {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .match-player {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px;
            background: #faf7f2;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .match-player.winner {
            background: #e8f0e5;
            border-left: 4px solid #2c5f2d;
        }

        .player-avatar {
            font-size: 24px;
            width: 40px;
            text-align: center;
        }

        .player-info {
            flex: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .player-name {
            font-size: 15px;
            font-weight: 500;
            color: #2c241a;
        }

        .match-player.winner .player-name {
            color: #2c5f2d;
            font-weight: bold;
        }

        .player-name.waiting {
            color: #b0a590;
            font-style: italic;
        }

        .match-vs {
            text-align: center;
            font-size: 14px;
            color: #c9a84c;
            margin: -5px 0;
        }

        .win-button {
            background: #c9a84c;
            color: #2c241a;
            text-decoration: none;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            transition: all 0.2s ease;
        }

        .win-button:hover {
            background: #2c5f2d;
            color: #e8d5a8;
            transform: scale(1.05);
        }

        .champion-card {
            background: linear-gradient(135deg, #2c241a, #3a3024);
            padding: 35px;
            text-align: center;
            margin-bottom: 30px;
            border: 2px solid #c9a84c;
            border-radius: 12px;
            animation: glow 2s ease-in-out infinite;
        }

        @keyframes glow {
            0%, 100% { box-shadow: 0 0 10px rgba(201,168,76,0.3); }
            50% { box-shadow: 0 0 25px rgba(201,168,76,0.6); }
        }

        .champion-card h2 {
            color: #e8d5a8;
            font-size: 20px;
            font-weight: normal;
            letter-spacing: 3px;
        }

        .champion-name {
            font-size: 42px;
            color: #c9a84c;
            margin: 20px 0;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .btn-start {
            display: inline-block;
            padding: 12px 35px;
            background: #4a3f2c;
            color: #e8d5a8;
            text-decoration: none;
            border-radius: 6px;
            letter-spacing: 2px;
            font-size: 14px;
            font-weight: bold;
            transition: all 0.2s;
        }

        .btn-start:hover {
            background: #5c4f38;
            transform: scale(1.02);
        }

        .btn-reset {
            display: inline-block;
            padding: 10px 25px;
            background: #8b5a4c;
            color: #f5f0e8;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
            transition: all 0.2s;
        }

        .btn-reset:hover {
            background: #6b4234;
        }
    </style>
    <script>
        function showModal() {
            document.getElementById('modal').classList.add('active');
            document.getElementById('modal-overlay').classList.add('active');
        }
        function closeModal() {
            document.getElementById('modal').classList.remove('active');
            document.getElementById('modal-overlay').classList.remove('active');
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });
    </script>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>♜ Шахматный Турнир <small>турнирный менеджер</small></h1>
    </div>

    <nav>
        <a href="index.php?action=players">▸ УЧАСТНИКИ</a>
        <a href="index.php?action=bracket">▸ ТУРНИРНАЯ СЕТКА</a>
        <?php if ($isAdmin): ?>
            <a href="index.php?action=reset_players" onclick="return confirm('ВНИМАНИЕ! Все игроки будут удалены! ID начнется с 1. Продолжить?')" style="color: #c9a84c;">▸ СБРОСИТЬ ИГРОКОВ</a>
            <a href="index.php?action=draw" onclick="return confirm('Сбросить все результаты и начать заново?')">▸ ЖЕРЕБЬЕВКА</a>
            <a href="index.php?action=logout">▸ ВЫХОД</a>
        <?php else: ?>
            <a href="javascript:void(0)" onclick="showModal()">▸ АДМИН</a>
        <?php endif; ?>
    </nav>

    <div class="content">
        <?php include($view_content); ?>
    </div>

    <div class="footer">
        ♔ CHESS TOURNAMENT MANAGER ♔
    </div>
</div>

<div id="modal-overlay" class="modal-overlay" onclick="closeModal()"></div>
<div id="modal" class="modal">
    <form method="POST" action="index.php?action=login">
        <h4>ВХОД В АДМИН-ПАНЕЛЬ</h4>
        <input type="password" name="admin_pass" placeholder="пароль" required>
        <button type="submit" class="btn-submit">ВОЙТИ</button>
        <button type="button" class="btn-cancel" onclick="closeModal()">ОТМЕНА</button>
    </form>
</div>
</body>
</html>