<?php

namespace TcgMarket\Handler;

use Aura\Sql\ExtendedPdo;
use RuntimeException;
use PDO;
use PDOStatement;
use TcgMarket\Core\DatabaseInterface;

/**
 * Handler responsible for managing database operations
 */
class DatabaseHandler implements DatabaseInterface
{
    public function __construct(private readonly ExtendedPdo $pdo)
    {
    }

    public function getPdo(): ExtendedPdo
    {
        return $this->pdo;
    }

    public function ping(): void
    {
        $this->execute($this->prepare('SELECT 1'));
    }

    public function transaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }

    public function quote($value, int $type = PDO::PARAM_STR): string
    {
        return $this->pdo->quote($value, $type);
    }

    public function prepare(string $query, array $values = []): PDOStatement
    {
        return $this->pdo->prepareWithValues($query, $values);
    }

    public function execute(PDOStatement $statement): int
    {
        if (false === $statement->execute()) {
            $error = $statement->errorInfo();
            // TODO: throw custom exception
            throw new RuntimeException($error[2], $statement->errorCode());
        }

        $affected = $statement->rowCount();
        $statement->closeCursor();
        $statement = null;

        return $affected;
    }

    public function run(string $query, array $values = []): int
    {
        $statement = $this->prepare($query, $values);

        return $this->execute($statement);
    }

    public function queryOne(PDOStatement $statement): ?array
    {
        if (false === $statement->execute()) {
            $error = $statement->errorInfo();
            // TODO: throw custom exception
            throw new RuntimeException($error[2], $statement->errorCode());
        }

        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        $statement = null;

        return false === $row ? null : $row;
    }

    public function queryAssoc(PDOStatement $statement): array
    {
        if (false === $statement->execute()) {
            $error = $statement->errorInfo();
            // TODO: throw custom exception
            throw new RuntimeException($error[2], $statement->errorCode());
        }

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        $statement = null;

        return $rows;
    }

    public function queryObject(PDOStatement $statement, $fqcn = 'stdClass'): array
    {
        if (false === $statement->execute()) {
            $error = $statement->errorInfo();
            // TODO: throw custom exception
            throw new RuntimeException($error[2], $statement->errorCode());
        }

        $objects = [];

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $objects[] = new $fqcn($row);
        }

        $statement->closeCursor();
        $statement = null;

        return $objects;
    }
}
