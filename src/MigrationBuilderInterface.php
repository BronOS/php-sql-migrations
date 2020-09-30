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


use BronOS\PhpSqlDiscovery\Exception\PhpSqlDiscoveryException;
use BronOS\PhpSqlMigrations\Exception\PhpSqlMigrationsException;
use BronOS\PhpSqlMigrations\QueryBuilder\MigrationQuery;
use BronOS\PhpSqlSchema\Exception\DuplicateColumnException;
use BronOS\PhpSqlSchema\Exception\DuplicateIndexException;
use BronOS\PhpSqlSchema\Exception\DuplicateRelationException;
use BronOS\PhpSqlSchema\Exception\DuplicateTableException;
use BronOS\PhpSqlSchema\Exception\SQLTableSchemaDeclarationException;
use BronOS\PhpSqlSchema\SQLDatabaseSchemaInterface;

/**
 * SQL migration builder.
 * Responsible for finding differences between defined schema and database state,
 * and building migration file based on it.
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */
interface MigrationBuilderInterface
{
    /**
     * Finds diff between passed schema and DB state (with PDO).
     * Builds SQL queries if diff has been found.
     *
     * @param SQLDatabaseSchemaInterface $schema
     *
     * @return MigrationQuery|null Source database schema
     *
     * @throws PhpSqlDiscoveryException
     * @throws DuplicateColumnException
     * @throws DuplicateIndexException
     * @throws DuplicateRelationException
     * @throws DuplicateTableException
     * @throws SQLTableSchemaDeclarationException
     * @throws PhpSqlMigrationsException
     */
    public function buildQueries(SQLDatabaseSchemaInterface $schema): ?MigrationQuery;

    /**
     * Finds diff between passed schema and DB state (with PDO).
     * Generates new migration file if diff has been found
     * and returns file path of it or null otherwise.
     *
     * @param string|null                $name   Migration name
     * @param SQLDatabaseSchemaInterface $schema Source database schema
     *
     * @return string|null
     *
     * @throws DuplicateColumnException
     * @throws DuplicateIndexException
     * @throws DuplicateRelationException
     * @throws DuplicateTableException
     * @throws PhpSqlDiscoveryException
     * @throws SQLTableSchemaDeclarationException
     */
    public function generate(?string $name, SQLDatabaseSchemaInterface $schema): ?string;

    /**
     * Generates new empty migration file and returns file path of it.
     *
     * @param string|null $name Migration name
     *
     * @return string
     *
     * @throws PhpSqlMigrationsException
     */
    public function generateEmpty(?string $name = null): string;
}