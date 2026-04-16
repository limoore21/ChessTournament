<?php

function addBD() {
    // включаем режим ошибок PDO, чтобы видеть, если SQL сломается
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];
    return new PDO("mysql:host=127.0.0.1;dbname=chess_db;charset=utf8", "root", "1234", $options);
}