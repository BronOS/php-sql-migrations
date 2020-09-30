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

namespace BronOS\PhpSqlMigrations\QueryBuilder;


use BronOS\PhpSqlDiff\Diff\ColumnDiff;
use BronOS\PhpSqlSchema\Column\ColumnInterface;

/**
 * SQL diff query builder.
 * Responsible for building SQL query based on column diff object.
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */
interface ColumnDiffQueryBuilderInterface
{
    /**
     * Represents SQL column object as a SQL signature, such as:
     *      BIGINT(20) NOT NULL DEFAULT 1000000 COMMENT 'my big int'
     *
     * @param ColumnInterface $column
     * @param string          $defaultCharset
     *
     * @return string
     */
    public function buildSignature(ColumnInterface $column, string $defaultCharset): string;

    /**
     * Builds a SQL "UP" and "DOWN" queries based on column diff object. Example:
     *      UP:   ALTER TABLE `blog` ADD COLUMN `my_int` BIGINT(20) NOT NULL DEFAULT 100 COMMENT 'type bigint'
     *      DOWN: ALTER TABLE `blog` DROP COLUMN `my_int`
     *
     * @param ColumnDiff $diff
     * @param string     $tableName
     * @param string     $defaultCharset
     *
     * @return MigrationQuery
     */
    public function buildQuery(ColumnDiff $diff, string $tableName, string $defaultCharset): MigrationQuery;
}