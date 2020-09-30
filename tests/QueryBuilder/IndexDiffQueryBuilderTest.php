<?php

namespace BronOS\PhpSqlMigrations\Tests\QueryBuilder;


use BronOS\PhpSqlDiff\SQLIndexDiffer;
use BronOS\PhpSqlMigrations\QueryBuilder\IndexDiffQueryBuilder;
use BronOS\PhpSqlSchema\Index\Key;
use BronOS\PhpSqlSchema\Index\PrimaryKey;
use BronOS\PhpSqlSchema\Index\UniqueKey;
use PHPUnit\Framework\TestCase;

class IndexDiffQueryBuilderTest extends TestCase
{
    public function testBuildSignature()
    {
        $qb = new IndexDiffQueryBuilder();

        $sig = $qb->buildSignature(new Key(
            ['f1', 'f2'],
            'idx1',
        ));

        $this->assertEquals("KEY `idx1` (`f1`,`f2`)", $sig);
    }

    public function testBuildSignaturePrimary()
    {
        $qb = new IndexDiffQueryBuilder();

        $sig = $qb->buildSignature(new PrimaryKey(
            ['f1', 'f2'],
        ));

        $this->assertEquals("PRIMARY KEY (`f1`,`f2`)", $sig);
    }

    public function testBuildSignatureUnique()
    {
        $qb = new IndexDiffQueryBuilder();

        $sig = $qb->buildSignature(new UniqueKey(
            ['f1', 'f2'],
            'idx1',
        ));

        $this->assertEquals("UNIQUE KEY `idx1` (`f1`,`f2`)", $sig);
    }

    public function testBuildQuery()
    {
        $qb = new IndexDiffQueryBuilder();
        $differ = new SQLIndexDiffer();

        $idx1 = new UniqueKey(
            ['f1', 'f2'],
            'idx'
        );
        $idx2 = new Key(
            ['f2', 'f1'],
            'idx'
        );

        $diff = $differ->diff($idx1, $idx2);

        $q = $qb->buildQuery($diff, 'post');

        $this->assertEquals(
            "ALTER TABLE 'post' DROP KEY `idx`;",
            $q->getUpQueries()[0]
        );
        $this->assertEquals(
            "ALTER TABLE 'post' ADD UNIQUE KEY `idx` (`f1`,`f2`);",
            $q->getUpQueries()[1]
        );
        $this->assertEquals(
            "ALTER TABLE 'post' DROP UNIQUE KEY `idx`;",
            $q->getDownQueries()[0]
        );
        $this->assertEquals(
            "ALTER TABLE 'post' ADD KEY `idx` (`f2`,`f1`);",
            $q->getDownQueries()[1]
        );
    }
}
