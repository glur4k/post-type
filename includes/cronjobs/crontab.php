<?php
header('Content-Type: text/html; charset=utf-8');

require_once('/../vendor/simple_html_dom.php');
require_once('/../helpers/utils.php');
require_once('cron-mannschaften.php');
require_once('cron-spieler.php');

$spielerParser = new CronSpieler(); // Ruft Cron-Mannschaften auf
?>
