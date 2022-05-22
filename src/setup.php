<?php

function createStructure($pdo)
{
    return $pdo->exec(
        '
            CREATE TABlE IF NOT EXISTS `USER_BASE` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `username` VARCHAR (20),
                `birthday` DATETIME
            )
        '
    );
}

$pdo = new \PDO($_ENV['DATABASE_URL'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
$dbName = $_ENV['DB_DATABASE'];

createStructure($pdo);
