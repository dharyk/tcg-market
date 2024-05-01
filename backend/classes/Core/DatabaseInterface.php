<?php

namespace TcgMarket\Core;

use Aura\Sql\ExtendedPdo;
use PDO;
use PDOStatement;

/**
 * Database interface
 */
interface DatabaseInterface
{

    /**
     * Return PDO handle
     *
     * @return ExtendedPdo
     */
    public function getPdo(): ExtendedPdo;

    /**
     * Begin a transaction
     */
    public function transaction(): bool;

    /**
     * Commit a transaction
     */
    public function commit(): bool;

    /**
     * Rollback a transaction
     */
    public function rollback(): bool;

    /**
     * Quote a value for use in SQL
     *
     * @param  mixed $value
     * @param  int   $type
     * @return string|false
     */
    public function quote($value, int $type = PDO::PARAM_STR): string|false;

    /**
     * Prepare a SQL statement
     *
     * @param  string               $query
     * @param  array<string, mixed> $values
     * @return PDOStatement
     */
    public function prepare(string $query, array $values = []): PDOStatement;

    /**
     * Execute a prepared SQL statement
     *
     * @param  PDOStatement $statement
     * @return int
     */
    public function execute(PDOStatement $statement): int;

    /**
     * Prepare and execute a SQL query
     *
     * @param  string               $query
     * @param  array<string, mixed> $values
     * @return PDOStatement
     */
    public function run(string $query, array $values = []): PDOStatement;

    /**
     * Run a SQL query and return first result as an associative array
     *
     * @param  PDOStatement $statement
     * @return array<string,mixed>|null
     */
    public function queryOne(PDOStatement $statement): ?array;

    /**
     * Run a SQL query and return result as an associative array
     *
     * @param  PDOStatement $statement
     * @return array<array<string,mixed>>
     */
    public function queryAssoc(PDOStatement $statement): array;

    /**
     * Run a SQL query and return result as an array of objects
     *
     * @param  PDOStatement $statement
     * @param  string       $fqcn
     * @return array<mixed>
     */
    public function queryObject(PDOStatement $statement, $fqcn = 'stdClass'): array;
}
