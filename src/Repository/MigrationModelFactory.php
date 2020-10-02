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

/**
 * Migration model factory.
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */
class MigrationModelFactory implements MigrationModelFactoryInterface
{
    /**
     * @param array $row
     *
     * @return MigrationModel
     *
     * @throws PhpSqlMigrationsException
     */
    public function fromRow(array $row): MigrationModel
    {
        try {
            return new MigrationModel(
                $row[self::COLUMN_NAME],
                new \DateTime($row[self::COLUMN_CREATED_AT]),
                is_null($row[self::COLUMN_UPDATED_AT]) ? null : new \DateTime($row[self::COLUMN_UPDATED_AT]),
                (string)$row[self::COLUMN_DOWN_QUERIES],
            );
        } catch (\Throwable $e) {
            throw new PhpSqlMigrationsException(
                "Cannot instantiate migration model from database row. Error: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}