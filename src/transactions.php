<?php

include_once 'functions.php';

$pdo = new \PDO($_ENV['DATABASE_URL'], 'root', 'root');

$start = microtime(true);
$pdo->beginTransaction();
for ($i = 0; $i < 25000; $i++) {
    insert($pdo);
}
$pdo->commit();
$time_elapsed_secs = microtime(true) - $start;
var_dump($time_elapsed_secs);

file_put_contents('out.txt', $time_elapsed_secs . PHP_EOL, FILE_APPEND);
