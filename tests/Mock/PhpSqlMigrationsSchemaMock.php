<?php

namespace BronOS\PhpSqlMigrations\Tests\Mock;


use BronOS\PhpSqlSchema\Exception\DuplicateTableException;
use BronOS\PhpSqlSchema\Exception\SQLTableSchemaDeclarationException;
use BronOS\PhpSqlSchema\SQLDatabaseSchema;
use BronOS\PhpSqlSchema\SQLTableSchemaInterface;

class PhpSqlMigrationsSchemaMock extends SQLDatabaseSchema
{
    public const NAME = 'php-sql-migration';

    /**
     * SQLDatabaseSchema constructor.
     *
     * @throws SQLTableSchemaDeclarationException
     * @throws DuplicateTableException
     */
    public function __construct()
    {
        parent::__construct(self::NAME, [
            new BlogTableSchemaMock(),
            new PostTableSchemaMock(),
        ]);
    }
}