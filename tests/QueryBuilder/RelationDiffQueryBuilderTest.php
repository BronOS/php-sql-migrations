<?php

namespace BronOS\PhpSqlMigrations\Tests\QueryBuilder;


use BronOS\PhpSqlDiff\SQLRelationDiffer;
use BronOS\PhpSqlMigrations\QueryBuilder\RelationDiffQueryBuilder;
use BronOS\PhpSqlSchema\Relation\Action\NoAction;
use BronOS\PhpSqlSchema\Relation\ForeignKey;
use PHPUnit\Framework\TestCase;

class RelationDiffQueryBuilderTest extends TestCase
{
    public function testBuildSignature()
    {
        $qb = new RelationDiffQueryBuilder();

        $sig = $qb->buildSignature(new ForeignKey(
            'sf1',
            'table2',
            'tf1',
        ));

        $this->assertEquals(
            "CONSTRAINT `sf1_table2_tf1_fk` FOREIGN KEY (`sf1`) REFERENCES `table2` (`tf1`)",
            $sig
        );
    }

    public function testBuildQuery()
    {
        $qb = new RelationDiffQueryBuilder();
        $differ = new SQLRelationDiffer();

        $fk1 = new ForeignKey(
            'sf1',
            'table2',
            'tf1',
            'fk1'
        );
        $fk2 = new ForeignKey(
            'sf1',
            'table2',
            'tf2',
            'fk1',
            new NoAction()
        );

        $diff = $differ->diff($fk1, $fk2);

        $q = $qb->buildQuery($diff, 'post');

        $this->assertEquals(
            "ALTER TABLE `post` DROP FOREIGN KEY `fk1`;",
            $q->getUpQueries()[0]
        );
        $this->assertEquals(
            "ALTER TABLE `post` ADD CONSTRAINT `fk1` FOREIGN KEY (`sf1`) REFERENCES `table2` (`tf1`);",
            $q->getUpQueries()[1]
        );
        $this->assertEquals(
            "ALTER TABLE `post` DROP FOREIGN KEY `fk1`;",
            $q->getDownQueries()[0]
        );
        $this->assertEquals(
            "ALTER TABLE `post` ADD CONSTRAINT `fk1` FOREIGN KEY (`sf1`) REFERENCES `table2` (`tf2`) ON DELETE NO ACTION;",
            $q->getDownQueries()[1]
        );
    }
}
