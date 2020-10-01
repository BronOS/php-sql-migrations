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

namespace BronOS\PhpSqlMigrations\ServiceLocator;


use BronOS\PhpSqlDiff\SQLDatabaseDifferInterface;
use BronOS\PhpSqlDiscovery\SQLDatabaseScannerInterface;
use BronOS\PhpSqlMigrations\CodeGenerator\MigrationClassGeneratorInterface;
use BronOS\PhpSqlMigrations\Factory\DatabaseDifferFactoryInterface;
use BronOS\PhpSqlMigrations\Factory\DatabaseDiffQueryBuilderFactoryInterface;
use BronOS\PhpSqlMigrations\Factory\DatabaseScannerFactoryInterface;
use BronOS\PhpSqlMigrations\Factory\DatabaseSchemaFactoryInterface;
use BronOS\PhpSqlMigrations\Factory\MigrationBuilderFactoryInterface;
use BronOS\PhpSqlMigrations\Factory\MigrationClassGeneratorFactoryInterface;
use BronOS\PhpSqlMigrations\Factory\MigrationsDirFactoryInterface;
use BronOS\PhpSqlMigrations\Factory\PDOFactoryInterface;
use BronOS\PhpSqlMigrations\FS\MigrationsDirInterface;
use BronOS\PhpSqlMigrations\MigrationBuilderInterface;
use BronOS\PhpSqlMigrations\QueryBuilder\DatabaseDiffQueryBuilderInterface;
use BronOS\PhpSqlSchema\SQLDatabaseSchema;
use PDO;

/**
 * Service locator.
 * Responsible for instantiating and handling of objects/dependencies.
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */
interface ServiceLocatorInterface
{
    /**
     * @return PDOFactoryInterface
     */
    public function getPdoFactory(): PDOFactoryInterface;

    /**
     * @return MigrationsDirFactoryInterface
     */
    public function getMigrationsDirFactory(): MigrationsDirFactoryInterface;

    /**
     * @return MigrationClassGeneratorFactoryInterface
     */
    public function getMigrationClassGeneratorFactory(): MigrationClassGeneratorFactoryInterface;

    /**
     * @return MigrationBuilderFactoryInterface
     */
    public function getMigrationBuilderFactory(): MigrationBuilderFactoryInterface;

    /**
     * @return DatabaseSchemaFactoryInterface
     */
    public function getDatabaseSchemaFactory(): DatabaseSchemaFactoryInterface;

    /**
     * @return DatabaseScannerFactoryInterface
     */
    public function getDatabaseScannerFactory(): DatabaseScannerFactoryInterface;

    /**
     * @return DatabaseDiffQueryBuilderFactoryInterface
     */
    public function getDatabaseDiffQueryBuilderFactory(): DatabaseDiffQueryBuilderFactoryInterface;

    /**
     * @return DatabaseDifferFactoryInterface
     */
    public function getDatabaseDifferFactory(): DatabaseDifferFactoryInterface;

    /**
     * @return PDO
     */
    public function getPdo(): PDO;

    /**
     * @return MigrationsDirInterface
     */
    public function getMigrationsDir(): MigrationsDirInterface;

    /**
     * @return MigrationClassGeneratorInterface
     */
    public function getMigrationClassGenerator(): MigrationClassGeneratorInterface;

    /**
     * @return MigrationBuilderInterface
     */
    public function getMigrationBuilder(): MigrationBuilderInterface;

    /**
     * @return SQLDatabaseSchema
     */
    public function getDatabaseSchema(): SQLDatabaseSchema;

    /**
     * @return SQLDatabaseScannerInterface
     */
    public function getDatabaseScanner(): SQLDatabaseScannerInterface;

    /**
     * @return DatabaseDiffQueryBuilderInterface
     */
    public function getDatabaseDiffQueryBuilder(): DatabaseDiffQueryBuilderInterface;

    /**
     * @return SQLDatabaseDifferInterface
     */
    public function getDatabaseDiffer(): SQLDatabaseDifferInterface;
}