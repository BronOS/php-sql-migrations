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

namespace BronOS\PhpSqlMigrations;


use BronOS\PhpSqlMigrations\Exception\PhpSqlMigrationsException;
use PDO;
use PDOException;
use PDOStatement;

/**
 * Provides base functionality for migration.
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */
abstract class AbstractMigration
{
    private const PDO_ERROR_CODE_SUCCESS = '00000';

    private PDO $pdo;

    /**
     * AbstractMigration constructor.
     *
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Upgrades database state.
     */
    abstract public function up(): void;

    /**
     * Downgrades database state.
     */
    abstract public function down(): void;

    /**
     * @param string $query
     * @param array  $binds
     *
     * @return array
     *
     * @throws PhpSqlMigrationsException
     */
    protected function fetchAll(string $query, array $binds = []): array
    {
        return $this->execute($query, $binds)->fetchAll();
    }

    /**
     * Runs/executes passed SQL query and returns TRUE on success or FALSE on fail.
     *
     * @param string $query
     *
     * @return bool
     *
     * @throws PhpSqlMigrationsException
     */
    protected function run(string $query): bool
    {
        $sth = $this->execute($query);

        if ($sth->errorCode() === self::PDO_ERROR_CODE_SUCCESS) {
            return true;
        }

        return false;
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
    protected function execute(string $query, array $binds = []): PDOStatement
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
}