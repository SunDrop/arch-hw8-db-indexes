<?php
include_once 'functions.php';

$pdo = new \PDO($_ENV['DATABASE_URL'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
createStructure($pdo);
fillData($pdo);
