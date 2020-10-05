<?php

namespace BronOS\PhpSqlMigrations\Tests\QueryBuilder;


use BronOS\PhpSqlDiff\SQLColumnDiffer;
use BronOS\PhpSqlMigrations\QueryBuilder\ColumnDiffQueryBuilder;
use BronOS\PhpSqlSchema\Column\Numeric\IntColumn;
use BronOS\PhpSqlSchema\Column\String\EnumColumn;
use BronOS\PhpSqlSchema\Column\String\SetColumn;
use BronOS\PhpSqlSchema\Column\String\TextColumn;
use BronOS\PhpSqlSchema\Column\String\VarCharColumn;
use PHPUnit\Framework\TestCase;

class ColumnDiffQueryBuilderTest extends TestCase
{

    public function testBuildSignature()
    {
        $qb = new ColumnDiffQueryBuilder();

        $sig = $qb->buildSignature(new IntColumn(
            'id',
            11,
            true,
            true,
            false,
            null,
            false,
            "My ID"
        ), "");

        $this->assertEquals("`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'My ID'", $sig);
    }

    public function testBuildSignatureVarChar()
    {
        $qb = new ColumnDiffQueryBuilder();

        $sig = $qb->buildSignature(new VarCharColumn(
            'title',
            100,
            true,
            VarCharColumn::NULL_KEYWORD,
            "utf8",
            'utf8_czech_ci',
            "My Title"
        ), "latin1");

        $this->assertEquals("`title` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'My Title'", $sig);
    }

    public function testBuildSignatureBinary()
    {
        $qb = new ColumnDiffQueryBuilder();

        $sig = $qb->buildSignature(new TextColumn(
            'title',
            true,
            false,
            false,
            "utf8",
            null
        ), "latin1");

        $this->assertEquals("`title` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL", $sig);
    }

    public function testBuildSignatureEnum()
    {
        $qb = new ColumnDiffQueryBuilder();

        $sig = $qb->buildSignature(new EnumColumn(
            'enm',
            ['a', 'b'],
            false,
            'a',
        ), "latin1");

        $this->assertEquals("`enm` ENUM('a','b') NOT NULL DEFAULT 'a'", $sig);
    }

    public function testBuildSignatureSet()
    {
        $qb = new ColumnDiffQueryBuilder();

        $sig = $qb->buildSignature(new SetColumn(
            'st',
            ['a', 'b'],
            true,
        ), "latin1");

        $this->assertEquals("`st` SET('a','b') DEFAULT NULL", $sig);
    }

    public function testBuildQuery()
    {
        $qb = new ColumnDiffQueryBuilder();
        $differ = new SQLColumnDiffer();

        $clm1 = new IntColumn(
            'id',
            11,
            true,
            true,
            false
        );
        $clm2 = new IntColumn(
            'id',
            11,
            false,
            true,
            false
        );

        $diff = $differ->diff($clm1, $clm2, '', '');

        $q = $qb->buildQuery($diff, 'post', 'latin1');

        $this->assertEquals("ALTER TABLE `post` CHANGE COLUMN `id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY;", $q->getUpQueries()[0]);
        $this->assertEquals("ALTER TABLE `post` CHANGE COLUMN `id` `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY;", $q->getDownQueries()[0]);
    }
}
