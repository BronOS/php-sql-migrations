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


use BronOS\PhpSqlDiff\Diff\RelationDiff;
use BronOS\PhpSqlSchema\Relation\ForeignKeyInterface;

/**
 * SQL diff query builder.
 * Responsible for building SQL query based on relation diff object.
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */
class RelationDiffQueryBuilder implements RelationDiffQueryBuilderInterface
{
    /**
     * Represents SQL relation object as a SQL signature, such as:
     *      CONSTRAINT `fk_name` FOREIGN KEY (`fk_table2_id`) REFERENCES `table2` (`t2`) ON DELETE CASCADE;
     *
     * @param ForeignKeyInterface $relation
     *
     * @return string
     */
    public function buildSignature(ForeignKeyInterface $relation): string
    {
        $sig = sprintf("CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s` (`%s`)",
            $relation->getName(),
            $relation->getSourceField(),
            $relation->getTargetTable(),
            $relation->getTargetField()
        );

        if (!is_null($relation->getOnDeleteAction())) {
            $sig .= sprintf(" ON DELETE %s", $relation->getOnDeleteAction()->getKeyword());
        }

        if (!is_null($relation->getOnUpdateAction())) {
            $sig .= sprintf(" ON UPDATE %s", $relation->getOnUpdateAction()->getKeyword());
        }

        return $sig;
    }

    /**
     * Builds a SQL "UP" and "DOWN" queries based on relation diff object. Example:
     *      UP:   ALTER TABLE `table1` ADD CONSTRAINT `fk_name` FOREIGN KEY (`fk_table2_id`) REFERENCES `table2` (`t2`);
     *      DOWN: ALTER TABLE `table1` DROP FOREIGN KEY `fk_name`;
     *
     * @param RelationDiff $diff
     * @param string       $tableName
     *
     * @return MigrationQuery
     */
    public function buildQuery(RelationDiff $diff, string $tableName): MigrationQuery
    {
        if ($diff->getDiffType()->isDeleted()) {
            return new MigrationQuery(
                [
                    sprintf("ALTER TABLE `%s` DROP FOREIGN KEY `%s`;", $tableName, $diff->getTargetObject()->getName()),
                ],
                [
                    sprintf("ALTER TABLE `%s` ADD %s;", $tableName, $this->buildSignature($diff->getTargetObject())),
                ],
            );
        }

        if ($diff->getDiffType()->isNew()) {
            return new MigrationQuery(
                [
                    sprintf("ALTER TABLE `%s` ADD %s;", $tableName, $this->buildSignature($diff->getSourceObject())),
                ],
                [
                    sprintf("ALTER TABLE `%s` DROP FOREIGN KEY `%s`;", $tableName, $diff->getSourceObject()->getName()),
                ],
            );
        }

        return new MigrationQuery(
            [
                sprintf("ALTER TABLE `%s` DROP FOREIGN KEY `%s`;", $tableName, $diff->getTargetObject()->getName()),
                sprintf("ALTER TABLE `%s` ADD %s;", $tableName, $this->buildSignature($diff->getSourceObject())),
            ],
            [
                sprintf("ALTER TABLE `%s` DROP FOREIGN KEY `%s`;", $tableName, $diff->getSourceObject()->getName()),
                sprintf("ALTER TABLE `%s` ADD %s;", $tableName, $this->buildSignature($diff->getTargetObject())),
            ],
        );
    }
}