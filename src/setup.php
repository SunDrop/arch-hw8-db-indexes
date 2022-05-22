<?php

function createStructure(\PDO $pdo): bool|int
{
    return $pdo->exec(
        '
            CREATE TABLE IF NOT EXISTS user_base
            (
                id int auto_increment primary key,
                username varchar(20) null,
                birthday datetime null
            );
        '
    );
}

function randomString(): string
{
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $str = '';
    for ($i = 0; $i < random_int(10, 20); $i++) {
        $str .= $characters[random_int(0, strlen($characters) - 1)];
    }

    return ucfirst($str);
}

function randomDate(\DateTime $start, \DateTime $end): DateTime
{
    $randomTimestamp = random_int($start->getTimestamp(), $end->getTimestamp());
    $randomDate = new \DateTime();
    $randomDate->setTimestamp($randomTimestamp);
    return $randomDate;
}

function fillData(\PDO $pdo): void
{
    $count = $pdo->query('SELECT count(*) FROM user_base')->fetchColumn();
    $limit = 40_000_000;
    $statement = $pdo->prepare('INSERT INTO user_base (username, birthday) VALUES (?, ?)');
    for ($i = $limit; $i >= $count; --$i) {
        $statement->execute([
            randomString(),
            randomDate(new \DateTime('1920-01-01'), new \DateTime('2022-12-31'))->format('Y-m-d H:i:s')
        ]);
    }
}

$pdo = new \PDO($_ENV['DATABASE_URL'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
createStructure($pdo);
fillData($pdo);
