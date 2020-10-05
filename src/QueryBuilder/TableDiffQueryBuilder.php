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


use BronOS\PhpSqlDiff\Diff\TableDiff;
use BronOS\PhpSqlSchema\Index\PrimaryKeyInterface;
use BronOS\PhpSqlSchema\SQLTableSchemaInterface;

/**
 * SQL diff query builder.
 * Responsible for building SQL query based on table diff object.
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */
class TableDiffQueryBuilder implements TableDiffQueryBuilderInterface
{
    private ColumnDiffQueryBuilderInterface $columnQB;
    private IndexDiffQueryBuilderInterface $indexQB;
    private RelationDiffQueryBuilderInterface $relationQB;

    /**
     * TableDiffQueryBuilder constructor.
     *
     * @param ColumnDiffQueryBuilderInterface   $columnQB
     * @param IndexDiffQueryBuilderInterface    $indexQB
     * @param RelationDiffQueryBuilderInterface $relationQB
     */
    public function __construct(
        ColumnDiffQueryBuilderInterface $columnQB,
        IndexDiffQueryBuilderInterface $indexQB,
        RelationDiffQueryBuilderInterface $relationQB
    ) {
        $this->columnQB = $columnQB;
        $this->indexQB = $indexQB;
        $this->relationQB = $relationQB;
    }

    /**
     * Builds a SQL "UP" and "DOWN" queries based on table diff object.
     *
     * @param TableDiff $diff
     * @param string    $defaultEngine
     * @param string    $defaultCharset
     * @param string    $defaultCollation
     *
     * @return MigrationQuery
     */
    public function buildQuery(
        TableDiff $diff,
        string $defaultEngine,
        string $defaultCharset,
        string $defaultCollation
    ): MigrationQuery {
        if ($diff->getDiffType()->isNew()) {
            return new MigrationQuery(
                [$this->getCreateTable($diff->getSourceObject(), $defaultCharset)],
                [$this->getDropTable($diff->getSourceObject()->getName())],
            );
        }

        if ($diff->getDiffType()->isDeleted()) {
            return new MigrationQuery(
                [$this->getDropTable($diff->getTargetObject()->getName())],
                [$this->getCreateTable($diff->getTargetObject(), $defaultCharset)],
            );
        }

        $up = [];
        $down = [];

        if ($diff->isEngine()) {
            $pattern = "ALTER TABLE %s ENGINE = %s;";
            $up[] = sprintf(
                $pattern,
                $diff->getSourceObject()->getName(),
                $diff->getSourceObject()->getEngine() ?? $defaultEngine
            );
            $down[] = sprintf(
                $pattern,
                $diff->getTargetObject()->getName(),
                $diff->getTargetObject()->getEngine() ?? $defaultEngine
            );
        }

        if ($diff->isCharset()) {
            $pattern = "ALTER TABLE %s CONVERT TO CHARACTER SET %s;";
            $up[] = sprintf(
                $pattern,
                $diff->getSourceObject()->getName(),
                $diff->getSourceObject()->getCharset() ?? $defaultCharset
            );
            $down[] = sprintf(
                $pattern,
                $diff->getTargetObject()->getName(),
                $diff->getTargetObject()->getCharset() ?? $defaultCharset
            );
        }

        if ($diff->isCollate()) {
            $pattern = "ALTER TABLE %s CONVERT TO CHARACTER SET %s COLLATE %s;";
            $up[] = sprintf(
                $pattern,
                $diff->getSourceObject()->getName(),
                $diff->getSourceObject()->getCharset() ?? $defaultCharset,
                $diff->getSourceObject()->getCollation() ?? $defaultCollation
            );
            $down[] = sprintf(
                $pattern,
                $diff->getTargetObject()->getName(),
                $diff->getTargetObject()->getCharset() ?? $defaultCharset,
                $diff->getTargetObject()->getCollation() ?? $defaultCollation
            );
        }

        foreach ($diff->getColumnDiffs() as $columnDiff) {
            $mq = $this->columnQB->buildQuery(
                $columnDiff,
                $diff->getSourceObject()->getName(),
                $diff->getSourceObject()->getCharset() ?? $defaultCharset
            );
            $up = array_merge($up, $mq->getUpQueries());
            $down = array_merge($down, $mq->getDownQueries());
        }

        foreach ($diff->getIndexDiffs() as $indexDiff) {
            $mq = $this->indexQB->buildQuery(
                $indexDiff,
                $diff->getSourceObject()->getName(),
            );
            $up = array_merge($up, $mq->getUpQueries());
            $down = array_merge($down, $mq->getDownQueries());
        }

        foreach ($diff->getRelationDiffs() as $relationDiff) {
            $mq = $this->relationQB->buildQuery(
                $relationDiff,
                $diff->getSourceObject()->getName(),
            );
            $up = array_merge($up, $mq->getUpQueries());
            $down = array_merge($down, $mq->getDownQueries());
        }

        return new MigrationQuery($this->sort($up), $this->sort($down));
    }

    /**
     * @param SQLTableSchemaInterface $schema
     * @param string                  $defaultCharset
     *
     * @return string
     */
    private function getCreateTable(SQLTableSchemaInterface $schema, string $defaultCharset): string
    {
        $query = sprintf("SET AUTOCOMMIT = 0;\n" .
            "SET FOREIGN_KEY_CHECKS = 0;\n" .
            "SET UNIQUE_CHECKS = 0;\n" .
            "DROP TABLE IF EXISTS `%s`;\n",
            $schema->getName()
        );
        $query .= sprintf("CREATE TABLE `%s` (\n", $schema->getName());

        $parts = [];
        $pkFlag = false;

        foreach ($schema->getColumns() as $column) {
            $q = $this->columnQB->buildSignature($column, $schema->getCharset() ?? $defaultCharset);

            if (strpos($q, 'PRIMARY KEY') !== false) {
                $pkFlag = true;
            }

            $parts[] = $q;
        }

        foreach ($schema->getIndexes() as $index) {
            if ($pkFlag && $index instanceof PrimaryKeyInterface) {
                continue;
            }

            $parts[] = $this->indexQB->buildSignature($index);
        }

        foreach ($schema->getRelations() as $rel) {
            $parts[] = $this->relationQB->buildSignature($rel);
        }

        $query .= implode(",\n", $this->addIndent($parts)) . "\n)";

        if (!is_null($schema->getEngine())) {
            $query .= sprintf(" ENGINE=%s", $schema->getEngine());
        }

        if (!is_null($schema->getCharset())) {
            $query .= sprintf(" DEFAULT CHARSET=%s", $schema->getCharset());
        }

        if (!is_null($schema->getCollation())) {
            $query .= sprintf(" COLLATE=%s", $schema->getCollation());
        }

        return $query . ";\n" .
            "SET AUTOCOMMIT = 1;\n" .
            "SET FOREIGN_KEY_CHECKS = 1;\n" .
            "SET UNIQUE_CHECKS = 1;\n";
    }

    /**
     * @param array $parts
     *
     * @return array
     */
    private function addIndent(array $parts): array
    {
        return array_map(function (string $part) {
            return '  ' . $part;
        }, $parts);
    }

    /**
     * @param string $tableName
     *
     * @return string
     */
    private function getDropTable(string $tableName): string
    {
        return sprintf("DROP TABLE `%s`;", $tableName);
    }

    /**
     * @param string[] $list
     *
     * @return array
     */
    private function sort(array $list): array
    {
        usort($list, function (string $query1, string $query2) {
            if (strpos($query1, "DROP") === false) {
                return 1;
            }

            return -1;
        });

        return $list;
    }
}