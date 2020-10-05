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


use BronOS\PhpSqlDiff\SQLDatabaseDifferInterface;
use BronOS\PhpSqlDiscovery\SQLDatabaseScannerInterface;
use BronOS\PhpSqlMigrations\CodeGenerator\MigrationClassGeneratorInterface;
use BronOS\PhpSqlMigrations\Config\PsmConfig;
use BronOS\PhpSqlMigrations\FS\MigrationsDirInterface;
use BronOS\PhpSqlMigrations\MigrationBuilder;
use BronOS\PhpSqlMigrations\MigrationBuilderInterface;
use BronOS\PhpSqlMigrations\QueryBuilder\DatabaseDiffQueryBuilderInterface;
use PDO;

/**
 * Migration builder factory.
 * Responsible for instantiating of migration builder object.
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */
class DefaultMigrationBuilderFactory implements MigrationBuilderFactoryInterface
{
    /**
     * Instantiates migration builder object based on passed config.
     *
     * @param PsmConfig                         $config
     *
     * @param PDO                               $pdo
     * @param DatabaseDiffQueryBuilderInterface $queryBuilder
     * @param MigrationClassGeneratorInterface  $classGenerator
     * @param SQLDatabaseScannerInterface       $scanner
     * @param SQLDatabaseDifferInterface        $differ
     * @param MigrationsDirInterface            $migrationsDir
     *
     * @return MigrationBuilderInterface
     */
    public function make(
        PsmConfig $config,
        PDO $pdo,
        DatabaseDiffQueryBuilderInterface $queryBuilder,
        MigrationClassGeneratorInterface $classGenerator,
        SQLDatabaseScannerInterface $scanner,
        SQLDatabaseDifferInterface $differ,
        MigrationsDirInterface $migrationsDir
    ): MigrationBuilderInterface {
        return new MigrationBuilder(
            $pdo,
            $queryBuilder,
            $classGenerator,
            $scanner,
            $differ,
            $migrationsDir,
        );
    }
}