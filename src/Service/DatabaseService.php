<?php

namespace App\Service;

use PDO;

class DatabaseService
{
    private PDO $masterConnection;
    private PDO $slave1Connection;
    private PDO $slave2Connection;

    public function __construct(string $masterUrl, string $slave1Url, string $slave2Url)
    {
        $this->masterConnection = $this->createConnection($masterUrl);
        $this->slave1Connection = $this->createConnection($slave1Url);
        $this->slave2Connection = $this->createConnection($slave2Url);
    }

    private function createConnection(string $databaseUrl): PDO
    {
        $dsn = sprintf(
            'pgsql:host=%s;dbname=%s;port=%s',
            parse_url($databaseUrl, PHP_URL_HOST),
            ltrim(parse_url($databaseUrl, PHP_URL_PATH), '/'),
            parse_url($databaseUrl, PHP_URL_PORT)
        );
        $username = parse_url($databaseUrl, PHP_URL_USER);
        $password = parse_url($databaseUrl, PHP_URL_PASS);

        $connection = new PDO($dsn, $username, $password);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $connection;
    }

    public function queryFromSlave1(string $sql, array $parameters = [])
    {
        return $this->executeQuery($this->slave1Connection, $sql, $parameters);
    }

    public function queryFromSlave2(string $sql, array $parameters = [])
    {
        return $this->executeQuery($this->slave2Connection, $sql, $parameters);
    }

    private function executeQuery(PDO $connection, string $sql, array $parameters = [])
    {
        $statement = $connection->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function executeOnMaster(string $sql, array $parameters = [])
    {
        $statement = $this->masterConnection->prepare($sql);
        $statement->execute($parameters);

        return $statement->rowCount();
    }
}