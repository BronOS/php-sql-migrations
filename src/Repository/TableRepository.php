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


use BronOS\PhpSqlDiscovery\Exception\PhpSqlDiscoveryException;
use BronOS\PhpSqlDiscovery\Factory\TableFactoryInterface;
use BronOS\PhpSqlDiscovery\Repository\TableRepository as ScannerTableRepository;
use PDO;

/**
 * Table repository.
 * Responsible for filtering "migrations" table.
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */
class TableRepository extends ScannerTableRepository
{
    private string $migrationsTableName;
    private array $ignoreTables;

    /**
     * ColumnsRepository constructor.
     *
     * @param PDO    $pdo
     * @param string $migrationsTableName
     * @param array  $ignoreTables
     */
    public function __construct(PDO $pdo, string $migrationsTableName, array $ignoreTables = [])
    {
        parent::__construct($pdo);
        $this->migrationsTableName = $migrationsTableName;
        $this->ignoreTables = $ignoreTables;
    }

    /**
     * Find all table's info/metadata and returns it as a raw array.
     *
     * @param array $ignoreTables
     * @return array
     *
     * @throws PhpSqlDiscoveryException
     */
    public function findInfoAll(array $ignoreTables = []): array
    {
        return parent::findInfoAll(
            array_unique(
                array_merge(
                    [$this->migrationsTableName],
                    $ignoreTables,
                    $this->ignoreTables
                )
            )
        );
    }
}