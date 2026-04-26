#Chess Tournament Manager

> Веб-приложение для проведения шахматных турниров по олимпийской системе

**Демо:** [tournamentmaxtm.42web.io](http://tournamentmaxtm.42web.io/index.php?action=players)

---

## Блок-схемы (ТЗ)

![Блок-схема работы приложения](./images/БЛОК-СХЕМА%20РАБОТЫ%20ПРИЛОЖЕНИЯ.png)

![Блок-схема жеребьевки](./images/БЛОК-СХЕМА%20ЖЕРЕБЬЕВКИ.png)

![Блок-схема матча (счет 1:1 и 2:0)](./images/БЛОК-СХЕМА%20МАТЧА%20(СЧЕТ%201%3A1%20и%202%3A0).png)

---

## Что делает проект

Автоматизирует проведение шахматных турниров «на вылет»:

- Добавление и удаление игроков
- Случайная жеребьевка
- Турнирная сетка в реальном времени
- Подсчет побед/поражений
- Определение чемпиона

---

## Технологии

- PHP 8.3
- MariaDB/Mysql (PDO)
- HTML/CSS
- JavaScript (AJAX)

---

## Структура проекта
Chess Tournament Manager (Single Elimination)

    Система для автоматизации проведения шахматных турниров по системе «на вылет».
    Приложение позволяет управлять списком участников, 
    генерировать турнирную сетку и в реальном времени вести игроков от первого раунда до финала.

Текущий статус проекта

    Система полностью функциональна. 
    Реализован полный цикл турнира: 
    От регистрации участников до объявления чемпиона.

Что реализовано:

Архитектура MVC

    Управление игроками (CRUD): Добавление участников и их удаление
    Умная жеребьевка: Автоматическое распределение игроков по парам. Система корректно обрабатывает нечетное количество участников (техническая победа).
    Интерактивная сетка: Визуальное отображение раундов в виде колонок с карточками матчей.
    Логика продвижения: При выборе победителя в матче система проверяет завершенность раунда и автоматически формирует пары для следующего этапа.
    Финал и Награждение: Автоматическое определение чемпиона турнира с выводом торжественного поздравления.

Структура проекта

    index.php — Главный контроллер, обрабатывает все действия.
    models/Tournament.php — Основная логика жеребьевки и продвижения.
    views/players.php — Страница управления участниками.
    views/bracket.php — Интерактивная турнирная сетка.
    layout.php — Главный шаблон оформления.
    config.php — Параметры подключения к базе данных.

Установка и запуск

    База данных: Импортируйте базу данных из корня проекта, если дамп не работает снизу проложил SQL код(players, tournaments, rounds, matches).
    Настройка: Укажите свои данные (host, db_name, user, password) в файле config.php.
    Запустите локальный сервер OpenServer/Apache/Nginx

Как пользоваться

    Перейдите в раздел Игроки и добавьте минимум 6 участников.
    Нажмите кнопку Провести жеребьевку. Система создаст первый раунд.
    В разделе Сетка отмечайте победителей в каждом матче кнопкой WIN.
    Как только все матчи раунда будут завершены, система автоматически создаст следующий этап (Полуфинал/Финал).
    После победы в последнем матче вы увидите поздравление для чемпиона турнира.

В планах на будущее

    [1] Добавление истории завершенных турниров.
    [2] Возможность редактирования никнеймов игроков.
    [3] Генерация PDF-отчета с результатами турнира.
    [4] Счетчик эло.
    [5] Оптимизировать сетку под другие виды спорта

Разработчик: Максимко

Обещанный SQL-запрос:

CREATE DATABASE IF NOT EXISTS chess_tournament;
USE chess_tournament;


SET FOREIGN_KEY_CHECKS = 0;


-- Таблица игроков
DROP TABLE IF EXISTS players;
CREATE TABLE players (
id INT(11) NOT NULL AUTO_INCREMENT,
nickname VARCHAR(100) NOT NULL,
PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Таблица турниров
DROP TABLE IF EXISTS tournaments;
CREATE TABLE tournaments (
id INT(11) NOT NULL AUTO_INCREMENT,
title VARCHAR(255) NOT NULL,
PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Таблица раундов
DROP TABLE IF EXISTS rounds;
CREATE TABLE rounds (
id INT(11) NOT NULL AUTO_INCREMENT,
tournament_id INT(11) NOT NULL,
round_number INT(11) NOT NULL,
round_name VARCHAR(100) DEFAULT NULL,
PRIMARY KEY (id),
CONSTRAINT fk_round_tournament FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Таблица матчей
DROP TABLE IF EXISTS matches;
CREATE TABLE matches (
id INT(11) NOT NULL AUTO_INCREMENT,
round_id INT(11) NOT NULL,
player1_id INT(11) DEFAULT NULL,
player2_id INT(11) DEFAULT NULL,
winner_id INT(11) DEFAULT NULL,
PRIMARY KEY (id),
CONSTRAINT fk_match_round FOREIGN KEY (round_id) REFERENCES rounds(id) ON DELETE CASCADE,
CONSTRAINT fk_player1 FOREIGN KEY (player1_id) REFERENCES players(id) ON DELETE SET NULL,
CONSTRAINT fk_player2 FOREIGN KEY (player2_id) REFERENCES players(id) ON DELETE SET NULL,
CONSTRAINT fk_winner FOREIGN KEY (winner_id) REFERENCES players(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Таблица участников турнира (связующая)
DROP TABLE IF EXISTS tournament_participants;
CREATE TABLE tournament_participants (
tournament_id INT(11) NOT NULL,
player_id INT(11) NOT NULL,
PRIMARY KEY (tournament_id, player_id),
CONSTRAINT fk_tp_tournament FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
CONSTRAINT fk_tp_player FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Включаем проверки обратно
SET FOREIGN_KEY_CHECKS = 1;


-- один стартовый турнир, чтобы всё сразу работало
INSERT INTO tournaments (id, title) VALUES (1, 'Турнир номер один');


