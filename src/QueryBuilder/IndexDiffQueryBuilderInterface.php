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


use BronOS\PhpSqlDiff\Diff\IndexDiff;
use BronOS\PhpSqlSchema\Index\IndexInterface;

/**
 * SQL diff query builder.
 * Responsible for building SQL query based on index diff object.
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */
interface IndexDiffQueryBuilderInterface
{
    /**
     * Represents SQL index object as a SQL signature, such as:
     *      UNIQUE KEY `unq_idx_1` (`unq_field_1`,`unq_field_2`)
     *
     * @param IndexInterface $index
     *
     * @return string
     */
    public function buildSignature(IndexInterface $index): string;

    /**
     * Builds a SQL "UP" and "DOWN" queries based on index diff object. Example:
     *      UP:   ALTER TABLE `blog` ADD UNIQUE KEY `unq_idx_1` (`unq_field_1`,`unq_field_2`)
     *      DOWN: ALTER TABLE `blog` DROP UNIQUE KEY `unq_idx_1`
     *
     * @param IndexDiff $diff
     * @param string    $tableName
     *
     * @return MigrationQuery
     */
    public function buildQuery(IndexDiff $diff, string $tableName): MigrationQuery;
}