<?php

namespace BronOS\PhpSqlMigrations\Tests\QueryBuilder;


use BronOS\PhpSqlDiff\DefaultSQLTableDiffer;
use BronOS\PhpSqlDiff\Diff\DiffTypeEnum;
use BronOS\PhpSqlDiff\Diff\TableDiff;
use BronOS\PhpSqlMigrations\QueryBuilder\DefaultTableDiffQueryBuilder;
use BronOS\PhpSqlSchema\Column\Numeric\IntColumn;
use BronOS\PhpSqlSchema\Column\String\VarCharColumn;
use BronOS\PhpSqlSchema\Index\Key;
use BronOS\PhpSqlSchema\Index\PrimaryKey;
use BronOS\PhpSqlSchema\Relation\ForeignKey;
use BronOS\PhpSqlSchema\SQLTableSchema;
use PHPUnit\Framework\TestCase;

class TableDiffQueryBuilderTest extends TestCase
{
    public function testBuildQuery()
    {
        $differ = new DefaultSQLTableDiffer();
        $qb = new DefaultTableDiffQueryBuilder();
        $table1 = new SQLTableSchema(
            'tbl1',
            [
                new IntColumn(
                    'id',
                    11,
                    true,
                    true
                ),
                new IntColumn(
                    'tbl2_id',
                    11,
                ),
                new VarCharColumn(
                    'nickname',
                    100
                ),
            ],
            [
                new Key(
                    ['nickname']
                ),
            ],
            [
                new ForeignKey(
                    'tbl2_id',
                    'tbl2',
                    'id',
                    'tbl2_to_tbl1'
                ),
            ]
        );
        $table2 = new SQLTableSchema(
            'tbl1',
            [
                new IntColumn(
                    'id2',
                    11,
                    true,
                    true
                ),
                new IntColumn(
                    'tbl2_id',
                    11,
                ),
                new VarCharColumn(
                    'nickname',
                    100
                ),
            ],
            [
                new PrimaryKey(
                    ['id2']
                ),
                new Key(
                    ['nickname']
                ),
                new Key(
                    ['tbl2_id'],
                    'tbl2_to_tbl1'
                ),
            ],
            [
                new ForeignKey(
                    'tbl2_id',
                    'tbl2',
                    'id',
                    'tbl2_to_tbl1'
                ),
            ],
            'InnoDB',
            'latin1',
            'latin1_general_ci'
        );

        $diff = $differ->diff(
            $table1,
            $table2,
            'InnoDB',
            'latin1',
            'latin1_general_ci'
        );
        $mq = $qb->buildQuery(
            $diff,
            'InnoDB',
            'latin1',
            'latin1_general_ci'
        );

        $this->assertCount(2, $mq->getUpQueries());
        $this->assertCount(2, $mq->getDownQueries());

        $this->assertEquals(
            "ALTER TABLE `tbl1` DROP COLUMN `id2`;",
            $mq->getUpQueries()[0]
        );
        $this->assertEquals(
            "ALTER TABLE `tbl1` ADD COLUMN `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY;",
            $mq->getUpQueries()[1]
        );

        $this->assertEquals(
            "ALTER TABLE `tbl1` DROP COLUMN `id`;",
            $mq->getDownQueries()[0]
        );
        $this->assertEquals(
            "ALTER TABLE `tbl1` ADD COLUMN `id2` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY;",
            $mq->getDownQueries()[1]
        );
    }
    public function testBuildQueryCreate()
    {
        $qb = new DefaultTableDiffQueryBuilder();
        $table1 = new SQLTableSchema(
            'tbl1',
            [
                new IntColumn(
                    'id',
                    11,
                    true,
                    true
                ),
            ],
        );

        $diff = new TableDiff(DiffTypeEnum::NEW(), $table1);

        $mq = $qb->buildQuery(
            $diff,
            'InnoDB',
            'latin1',
            'latin1_general_ci'
        );

        $this->assertCount(1, $mq->getUpQueries());
        $this->assertCount(1, $mq->getDownQueries());

        $this->assertEquals(
            "CREATE TABLE `tbl1` (\n  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY\n);",
            $mq->getUpQueries()[0]
        );

        $this->assertEquals(
            "DROP TABLE `tbl1`;",
            $mq->getDownQueries()[0]
        );
    }
}
