<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';
require_once 'models/Player.php';

$pdo = addBD();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

switch ($action) {
    case 'list':
        $players = getAllPlayers($pdo);
        $view_content = 'views/players.php';
        break;

    case 'add_player':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nickname'])) {
            ins($pdo, $_POST['nickname']);
            header("Location: index.php?action=list");
            exit;
        }
        break;

    case 'edit_form':
        $player = getPlayer($pdo, $_GET['id']);
        $view_content = 'views/edit_player.php';
        break;

    case 'update_player':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            updatePlayer($pdo, $_POST['id'], $_POST['nickname']);
            header("Location: index.php?action=list");
            exit;
        }
        break;

    case 'delete_player':
        if (isset($_GET['id'])) {
            delPlayer($pdo, $_GET['id']);
            header("Location: index.php?action=list");
            exit;
        }
        break;

    case 'bracket':
        $view_content = 'views/bracket.php';
        break;

    default:
        $view_content = 'views/players.php';
        break;
}

require_once 'views/layout.php';