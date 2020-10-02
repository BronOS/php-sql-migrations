<?php

/**
 * Php Sql Migrations
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace BronOS\PhpSqlMigrations\Repository;


use BronOS\PhpSqlMigrations\Exception\PhpSqlMigrationsException;
use PDOException;
use PDOStatement;

/**
 * Migration repository.
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */
class MigrationsRepository implements MigrationsRepositoryInterface
{
    private const PDO_ERROR_CODE_SUCCESS = '00000';

    private \PDO $pdo;
    private MigrationModelFactoryInterface $factory;
    private string $tableName;

    /**
     * MigrationsRepository constructor.
     *
     * @param \PDO                           $pdo
     * @param MigrationModelFactoryInterface $factory
     * @param string                         $tableName
     */
    public function __construct(\PDO $pdo, MigrationModelFactoryInterface $factory, string $tableName)
    {
        $this->pdo = $pdo;
        $this->factory = $factory;
        $this->tableName = $tableName;
    }

    /**
     * Executes passed SQL query and returns PDOStatement object.
     *
     * @param string $query
     * @param array  $binds
     *
     * @return PDOStatement
     *
     * @throws PhpSqlMigrationsException
     */
    public function execute(string $query, array $binds = []): PDOStatement
    {
        try {
            $sth = $this->pdo->prepare($query);
            if ($sth === false) {
                throw new PDOException("Cannot prepare sql statement");
            }

            $sth->execute($binds);
        } catch (PDOException $e) {
            throw new PhpSqlMigrationsException($e->getMessage(), (int)$e->getCode(), $e);
        }

        return $sth;
    }

    /**
     * @return bool
     *
     * @throws PhpSqlMigrationsException
     */
    public function isTableExists(): bool
    {
        return count($this->execute("SHOW TABLES LIKE '{$this->tableName}'")->fetchAll()) > 0;
    }

    /**
     * @return bool
     *
     * @throws PhpSqlMigrationsException
     */
    public function createTable(): bool
    {
        return $this->execute(
                "CREATE TABLE `{$this->tableName}` (
                  `name` varchar(255) NOT NULL DEFAULT '',
                  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
                  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
                  `down_queries` text DEFAULT NULL,
              PRIMARY KEY (`name`))"
            )->errorCode() === self::PDO_ERROR_CODE_SUCCESS;
    }

    /**
     * @param string $name
     * @param string $downQueries
     *
     * @return bool
     *
     * @throws PhpSqlMigrationsException
     */
    public function insertOrUpdate(string $name, string $downQueries): bool
    {
        return $this->execute(
                "INSERT INTO {$this->tableName} (`name`, `down_queries`) VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE `updated_at` = NOW(), `down_queries` = ?", [
                    $name,
                    $downQueries,
                    $downQueries,
                ]
            )->errorCode() === self::PDO_ERROR_CODE_SUCCESS;
    }

    /**
     * @param string $name
     *
     * @return bool
     *
     * @throws PhpSqlMigrationsException
     */
    public function delete(string $name): bool
    {
        return $this->execute(
                "DELETE FROM {$this->tableName} WHERE `name` = ?", [
                    $name,
                ]
            )->errorCode() === self::PDO_ERROR_CODE_SUCCESS;
    }

    /**
     * @return MigrationModel[]
     *
     * @throws PhpSqlMigrationsException
     */
    public function findAll(): array
    {
        $list = [];
        foreach ($this->execute("SELECT * FROM {$this->tableName}")->fetchAll() as $row) {
            $list[] = $this->factory->fromRow($row);
        }
        return $list;
    }
}