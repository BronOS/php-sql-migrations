<?php

namespace BronOS\PhpSqlMigrations\Tests\Mock;


use BronOS\PhpSqlSchema\Column\DateTime\DateTimeColumn;
use BronOS\PhpSqlSchema\Column\DateTime\TimestampColumn;
use BronOS\PhpSqlSchema\Column\Numeric\IntColumn;
use BronOS\PhpSqlSchema\Column\Numeric\TinyIntColumn;
use BronOS\PhpSqlSchema\Column\String\TextColumn;
use BronOS\PhpSqlSchema\Column\String\VarCharColumn;
use BronOS\PhpSqlSchema\Exception\ColumnDeclarationException;
use BronOS\PhpSqlSchema\Exception\DuplicateColumnException;
use BronOS\PhpSqlSchema\Exception\DuplicateIndexException;
use BronOS\PhpSqlSchema\Exception\DuplicateIndexFieldException;
use BronOS\PhpSqlSchema\Exception\DuplicateRelationException;
use BronOS\PhpSqlSchema\Exception\DuplicateTableException;
use BronOS\PhpSqlSchema\Exception\EmptyIndexFieldListException;
use BronOS\PhpSqlSchema\Exception\InvalidIndexFieldTypeException;
use BronOS\PhpSqlSchema\Exception\InvalidIndexNameException;
use BronOS\PhpSqlSchema\Exception\SQLTableSchemaDeclarationException;
use BronOS\PhpSqlSchema\Index\Key;
use BronOS\PhpSqlSchema\Index\UniqueKey;
use BronOS\PhpSqlSchema\Relation\Action\CascadeAction;
use BronOS\PhpSqlSchema\Relation\ForeignKey;
use BronOS\PhpSqlSchema\SQLDatabaseSchema;
use BronOS\PhpSqlSchema\SQLTableSchema;
use BronOS\PhpSqlSchema\SQLTableSchemaInterface;

class PostTableSchemaMock extends SQLTableSchema
{
    public const NAME = 'post';

    /**
     * SQLTableSchema constructor.
     *
     * @throws ColumnDeclarationException
     * @throws DuplicateColumnException
     * @throws DuplicateIndexException
     * @throws DuplicateRelationException
     * @throws SQLTableSchemaDeclarationException
     * @throws DuplicateIndexFieldException
     * @throws EmptyIndexFieldListException
     * @throws InvalidIndexFieldTypeException
     * @throws InvalidIndexNameException
     */
    public function __construct()
    {
        parent::__construct(self::NAME, [
            new IntColumn('id', 11, true, true),
            new IntColumn('blog_id', 11, true),
            new VarCharColumn('title', 150),
            new TextColumn('description', false, true, true),
            new DateTimeColumn('created_at', true),
            new TimestampColumn('updated_at', false, true, '0000-00-00 00:00:00'),
            new VarCharColumn('keywords', 155, true, VarCharColumn::NULL_KEYWORD),
            new TinyIntColumn('unq_1', 1, false, false, false, null, false, 'Unique idx 1'),
            new IntColumn('unq_2', 11, true, false, false, null, true, 'Unique idx 2'),
        ], [
            new Key(['keywords'], 'keywords'),
            new UniqueKey(['unq_1', 'unq_2'], 'unq_1'),
        ], [
            new ForeignKey('blog_id', 'blog', 'id', 'post_ibfk_1', new CascadeAction()),
        ]);
    }
}