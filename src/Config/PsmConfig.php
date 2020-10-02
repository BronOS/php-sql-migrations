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

namespace BronOS\PhpSqlMigrations\Config;


use BronOS\PhpSqlMigrations\Factory\ConfigDatabaseSchemaFactory;
use BronOS\PhpSqlMigrations\Factory\DefaultDatabaseDifferFactory;
use BronOS\PhpSqlMigrations\Factory\DefaultDatabaseDiffQueryBuilderFactory;
use BronOS\PhpSqlMigrations\Factory\DefaultDatabaseScannerFactory;
use BronOS\PhpSqlMigrations\Factory\DefaultMigrationBuilderFactory;
use BronOS\PhpSqlMigrations\Factory\DefaultMigrationInformerFactory;
use BronOS\PhpSqlMigrations\Factory\MigrationClassGeneratorFactory;
use BronOS\PhpSqlMigrations\Factory\MigrationsDirFactory;
use BronOS\PhpSqlMigrations\Factory\PDOFactory;

defined('__WORKDIR__') OR define('__WORKDIR__', getcwd());

/**
 * PHP SQL Migrations config structure.
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */
class PsmConfig
{
    public string $dbType = 'mysql';
    public string $dbHost = '127.0.0.1';
    public string $dbPort = '3306';
    public string $dbUser = 'root';
    public string $dbPass = '';

    public string $migrationsPath = __WORKDIR__ . '/migrations';
    public string $migrationsTable = 'migrations';
    public string $templateFilePath = __DIR__ . '/../../templates/migration.template';
    public ?string $schemaClass = null;

    public string $pdoFactoryClass = PDOFactory::class;
    public string $databaseSchemaFactoryClass = ConfigDatabaseSchemaFactory::class;
    public string $migrationsDirFactoryClass = MigrationsDirFactory::class;
    public string $migrationsClassGeneratorFactoryClass = MigrationClassGeneratorFactory::class;
    public string $databaseDifferFactoryClass = DefaultDatabaseDifferFactory::class;
    public string $databaseDiffQueryBuilderFactoryClass = DefaultDatabaseDiffQueryBuilderFactory::class;
    public string $databaseScannerFactoryClass = DefaultDatabaseScannerFactory::class;
    public string $migrationBuilderFactoryClass = DefaultMigrationBuilderFactory::class;
    public string $migrationInformerFactoryClass = DefaultMigrationInformerFactory::class;
}