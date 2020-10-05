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
use BronOS\PhpSqlSchema\Column\Attribute\AutoincrementColumnAttributeInterface;
use BronOS\PhpSqlSchema\Column\Attribute\BinaryColumnAttributeInterface;
use BronOS\PhpSqlSchema\Column\Attribute\CharsetColumnAttributeInterface;
use BronOS\PhpSqlSchema\Column\Attribute\CollateColumnAttributeInterface;
use BronOS\PhpSqlSchema\Column\Attribute\DecimalSizeColumnAttributeInterface;
use BronOS\PhpSqlSchema\Column\Attribute\DefaultTimestampColumnAttributeInterface;
use BronOS\PhpSqlSchema\Column\Attribute\FloatSizeColumnAttributeInterface;
use BronOS\PhpSqlSchema\Column\Attribute\OnUpdateTimestampColumnAttributeInterface;
use BronOS\PhpSqlSchema\Column\Attribute\OptionsColumnAttributeInterface;
use BronOS\PhpSqlSchema\Column\Attribute\SizeColumnAttributeInterface;
use BronOS\PhpSqlSchema\Column\Attribute\UnsignedColumnAttributeInterface;
use BronOS\PhpSqlSchema\Column\Attribute\ZerofillColumnAttributeInterface;
use BronOS\PhpSqlSchema\Column\ColumnInterface;
use BronOS\PhpSqlSchema\Column\DateTime\TimestampColumnInterface;

/**
 * SQL diff query builder.
 * Responsible for building SQL query based on column diff object.
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */
class ColumnDiffQueryBuilder implements ColumnDiffQueryBuilderInterface
{
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
    public function buildQuery(ColumnDiff $diff, string $tableName, string $defaultCharset): MigrationQuery
    {
        if ($diff->getDiffType()->isDeleted()) {
            return new MigrationQuery(
                [
                    sprintf("ALTER TABLE `%s` DROP COLUMN `%s`;",
                        $tableName,
                        $diff->getTargetObject()->getName()
                    ),
                ],
                [
                    sprintf("ALTER TABLE `%s` ADD COLUMN %s;",
                        $tableName,
                        $this->buildSignature($diff->getTargetObject(), $defaultCharset)
                    ),
                ]
            );
        }

        if ($diff->getDiffType()->isNew()) {
            return new MigrationQuery(
                [
                    sprintf("ALTER TABLE `%s` ADD COLUMN %s;",
                        $tableName,
                        $this->buildSignature($diff->getSourceObject(), $defaultCharset)
                    ),
                ],
                [
                    sprintf("ALTER TABLE `%s` DROP COLUMN `%s`;",
                        $tableName,
                        $diff->getSourceObject()->getName()
                    ),
                ]
            );
        }

        return new MigrationQuery(
            [
                sprintf("ALTER TABLE `%s` CHANGE COLUMN `%s` %s;",
                    $tableName,
                    $diff->getSourceObject()->getName(),
                    $this->buildSignature($diff->getSourceObject(), $defaultCharset)
                ),
            ],
            [
                sprintf("ALTER TABLE `%s` CHANGE COLUMN `%s` %s;",
                    $tableName,
                    $diff->getTargetObject()->getName(),
                    $this->buildSignature($diff->getTargetObject(), $defaultCharset)
                ),
            ],
        );
    }

    /**
     * Represents SQL column object as a SQL signature, such as:
     *      BIGINT(20) NOT NULL DEFAULT 1000000 COMMENT 'my big int'
     *
     * @param ColumnInterface $column
     * @param string          $defaultCharset
     *
     * @return string
     */
    public function buildSignature(ColumnInterface $column, string $defaultCharset): string
    {
        $sig = sprintf('`%s` %s', $column->getName(), $this->getType($column));

        $sig .= $this->getUnsigned($column);
        $sig .= $this->getZerofill($column);
        $sig .= $this->getCharset($column, $defaultCharset);
        $sig .= $this->getCollate($column, $defaultCharset);
        $sig .= $this->getNullable($column);
        $sig .= $this->getDefault($column);
        $sig .= $this->getAutoincrement($column);
        $sig .= $this->getOnUpdateTimestamp($column);
        $sig .= $this->getComment($column);

        return $sig;
    }

    /**
     * @param ColumnInterface $column
     *
     * @return string
     */
    private function getComment(ColumnInterface $column): string
    {
        if (!is_null($column->getComment()) && strlen($column->getComment()) > 0) {
            return sprintf(" COMMENT '%s'", $column->getComment());
        }

        return '';
    }

    /**
     * @param ColumnInterface $column
     *
     * @return string
     */
    private function getNullable(ColumnInterface $column): string
    {
        if (!$column->isNullable()) {
            return ' NOT NULL';
        }

        if ($column instanceof TimestampColumnInterface) {
            return ' NULL';
        }

        return '';
    }

    /**
     * @param ColumnInterface $column
     *
     * @return string
     */
    private function getAutoincrement(ColumnInterface $column): string
    {
        if ($column instanceof AutoincrementColumnAttributeInterface && $column->isAutoincrement()) {
            return ' AUTO_INCREMENT PRIMARY KEY';
        }

        return '';
    }

    /**
     * @param ColumnInterface $column
     *
     * @return string
     */
    private function getOnUpdateTimestamp(ColumnInterface $column): string
    {
        if ($column instanceof OnUpdateTimestampColumnAttributeInterface && $column->isOnUpdateTimestamp()) {
            return ' ON UPDATE current_timestamp()';
        }

        return '';
    }

    /**
     * @param ColumnInterface $column
     *
     * @return string
     */
    private function getUnsigned(ColumnInterface $column): string
    {
        if ($column instanceof UnsignedColumnAttributeInterface && $column->isUnsigned()) {
            return ' UNSIGNED';
        }

        return '';
    }

    /**
     * @param ColumnInterface $column
     *
     * @return string
     */
    private function getZerofill(ColumnInterface $column): string
    {
        if ($column instanceof ZerofillColumnAttributeInterface && $column->isZerofill()) {
            return ' ZEROFILL';
        }

        return '';
    }

    /**
     * @param ColumnInterface $column
     *
     * @return string
     */
    private function getDefault(ColumnInterface $column): string
    {
        if ($column instanceof DefaultTimestampColumnAttributeInterface && $column->isDefaultTimestamp()) {
            return ' DEFAULT current_timestamp()';
        } elseif ($column->isDefaultNull() || ($column->isNullable() && is_null($column->getDefault()))) {
            return ' DEFAULT NULL';
        } elseif (!is_null($column->getDefault())) {
            return sprintf(" DEFAULT '%s'", $column->getDefault());
        }

        return '';
    }

    /**
     * @param ColumnInterface $column
     *
     * @return string
     */
    private function getType(ColumnInterface $column): string
    {
        $str = $column->getType();

        if ($column instanceof OptionsColumnAttributeInterface) {
            $str .= sprintf(
                '(%s)',
                implode(
                    ',',
                    array_map(
                        function (string $option) {
                            return sprintf("'%s'", trim($option));
                        },
                        $column->getOptions()
                    )
                )
            );
        } elseif ($column instanceof SizeColumnAttributeInterface) {
            $str .= sprintf('(%s)', $column->getSize());
        } elseif ($column instanceof DecimalSizeColumnAttributeInterface
            || ($column instanceof FloatSizeColumnAttributeInterface && !is_null($column->getPrecision()))
        ) {
            $str .= sprintf('(%s,%s)', $column->getPrecision(), $column->getScale());
        }

        return $str;
    }

    /**
     * @param ColumnInterface $column
     * @param string          $defaultCharset
     *
     * @return string
     */
    private function getCharset(ColumnInterface $column, string $defaultCharset): string
    {
        if (!$column instanceof CharsetColumnAttributeInterface) {
            return '';
        }

        $pattern = ' CHARACTER SET %s';

        if (!is_null($column->getCharset()) && strlen($column->getCharset()) > 0) {
            return sprintf($pattern, $column->getCharset());
        }

        if ($column instanceof BinaryColumnAttributeInterface && $column->isBinary()) {
            return sprintf($pattern, $defaultCharset);
        }

        return '';
    }

    /**
     * @param ColumnInterface $column
     * @param string          $defaultCharset
     *
     * @return string
     */
    private function getCollate(ColumnInterface $column, string $defaultCharset): string
    {
        $strPattern = ' COLLATE %s%s';
        $suffix = '_bin';

        if (!$column instanceof CollateColumnAttributeInterface) {
            return '';
        }

        if ($column instanceof CharsetColumnAttributeInterface
            && $column instanceof BinaryColumnAttributeInterface
            && $column->isBinary()
        ) {
            if (is_null($column->getCharset()) || strlen($column->getCharset()) == 0) {
                return sprintf($strPattern, $defaultCharset, $suffix);
            }
            return sprintf($strPattern, $column->getCharset(), $suffix);
        }

        if (is_null($column->getCollate()) || strlen($column->getCollate()) == 0) {
            return '';
        }

        return sprintf($strPattern, $column->getCollate(), '');
    }
}