<?php

namespace App\Service;

use PDO;

class DatabaseService
{
    private PDO $connection;

    public function __construct(string $databaseUrl)
    {
        $dsn = sprintf(
            'pgsql:host=%s;dbname=%s;port=%s',
            parse_url($databaseUrl, PHP_URL_HOST),
            ltrim(parse_url($databaseUrl, PHP_URL_PATH), '/'),
            parse_url($databaseUrl, PHP_URL_PORT)
        );
        $username = parse_url($databaseUrl, PHP_URL_USER);
        $password = parse_url($databaseUrl, PHP_URL_PASS);

        $this->connection = new PDO($dsn, $username, $password);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function query(string $sql, array $parameters = [])
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function execute(string $sql, array $parameters = [])
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute($parameters);

        return $statement->rowCount();
    }

    public function fetchAll($sql, $parameters)
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($parameters);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}