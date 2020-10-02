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

namespace BronOS\PhpSqlMigrations\Factory;


use BronOS\PhpSqlDiscovery\DefaultSQLColumnScanner;
use BronOS\PhpSqlDiscovery\DefaultSQLDatabaseScanner;
use BronOS\PhpSqlDiscovery\DefaultSQLIndexScanner;
use BronOS\PhpSqlDiscovery\DefaultSQLRelationScanner;
use BronOS\PhpSqlDiscovery\Factory\DatabaseFactory;
use BronOS\PhpSqlDiscovery\Factory\TableFactory;
use BronOS\PhpSqlDiscovery\Repository\DefaultsRepository;
use BronOS\PhpSqlDiscovery\SQLDatabaseScanner;
use BronOS\PhpSqlDiscovery\SQLDatabaseScannerInterface;
use BronOS\PhpSqlDiscovery\SQLIndexScanner;
use BronOS\PhpSqlDiscovery\SQLTableScanner;
use BronOS\PhpSqlMigrations\Config\PsmConfig;
use BronOS\PhpSqlMigrations\Repository\TableRepository;
use PDO;

/**
 * SQL database scanner factory.
 * Responsible for instantiating of SQL database scanner object.
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */
class DefaultDatabaseScannerFactory implements DatabaseScannerFactoryInterface
{
    /**
     * Instantiates SQL database scanner object based on passed config.
     *
     * @param PsmConfig $config
     * @param PDO       $pdo
     *
     * @return SQLDatabaseScannerInterface
     */
    public function make(PsmConfig $config, PDO $pdo): SQLDatabaseScannerInterface
    {
        return new SQLDatabaseScanner(
            new SQLTableScanner(
                new TableRepository(
                    $pdo,
                    $config->migrationsTable
                ),
                new TableFactory(),
                new DefaultSQLIndexScanner($pdo),
                new DefaultSQLRelationScanner($pdo),
                new DefaultSQLColumnScanner($pdo)
            ),
            new DefaultsRepository($pdo),
            new DatabaseFactory()
        );
    }
}