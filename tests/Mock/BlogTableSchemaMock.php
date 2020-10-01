<?php

namespace BronOS\PhpSqlMigrations\Tests\Mock;


use BronOS\PhpSqlSchema\Column\Numeric\IntColumn;
use BronOS\PhpSqlSchema\Column\String\VarCharColumn;
use BronOS\PhpSqlSchema\Exception\ColumnDeclarationException;
use BronOS\PhpSqlSchema\Exception\DuplicateColumnException;
use BronOS\PhpSqlSchema\Exception\DuplicateIndexException;
use BronOS\PhpSqlSchema\Exception\DuplicateRelationException;
use BronOS\PhpSqlSchema\Exception\DuplicateTableException;
use BronOS\PhpSqlSchema\Exception\SQLTableSchemaDeclarationException;
use BronOS\PhpSqlSchema\SQLDatabaseSchema;
use BronOS\PhpSqlSchema\SQLTableSchema;
use BronOS\PhpSqlSchema\SQLTableSchemaInterface;

class BlogTableSchemaMock extends SQLTableSchema
{
    public const NAME = 'blog';

    /**
     * SQLTableSchema constructor.
     *
     * @throws ColumnDeclarationException
     * @throws DuplicateColumnException
     * @throws DuplicateIndexException
     * @throws DuplicateRelationException
     * @throws SQLTableSchemaDeclarationException
     */
    public function __construct() {
        parent::__construct(
            self::NAME,
            [
                new IntColumn('id', 11, true, true),
                new VarCharColumn('title', 100),
            ]
        );
    }
}