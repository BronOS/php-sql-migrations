<?php

namespace BronOS\PhpSqlMigrations\Tests;


use BronOS\PhpSqlDiff\DefaultSQLDatabaseDiffer;
use BronOS\PhpSqlDiscovery\DefaultSQLColumnScanner;
use BronOS\PhpSqlDiscovery\DefaultSQLDatabaseScanner;
use BronOS\PhpSqlDiscovery\DefaultSQLIndexScanner;
use BronOS\PhpSqlDiscovery\DefaultSQLRelationScanner;
use BronOS\PhpSqlDiscovery\Factory\DatabaseFactory;
use BronOS\PhpSqlDiscovery\Factory\TableFactory;
use BronOS\PhpSqlDiscovery\Repository\DefaultsRepository;
use BronOS\PhpSqlDiscovery\SQLDatabaseScanner;
use BronOS\PhpSqlDiscovery\SQLTableScanner;
use BronOS\PhpSqlMigrations\CodeGenerator\DefaultTemplateMigrationClassGenerator;
use BronOS\PhpSqlMigrations\DefaultMigrationBuilder;
use BronOS\PhpSqlMigrations\Exception\PhpSqlMigrationsException;
use BronOS\PhpSqlMigrations\FS\MigrationsDir;
use BronOS\PhpSqlMigrations\MigrationBuilder;
use BronOS\PhpSqlMigrations\MigrationBuilderInterface;
use BronOS\PhpSqlMigrations\QueryBuilder\DefaultDatabaseDiffQueryBuilder;
use BronOS\PhpSqlMigrations\Repository\TableRepository;
use BronOS\PhpSqlSchema\Column\DateTime\DateTimeColumn;
use BronOS\PhpSqlSchema\Column\DateTime\TimestampColumn;
use BronOS\PhpSqlSchema\Column\Numeric\IntColumn;
use BronOS\PhpSqlSchema\Column\Numeric\TinyIntColumn;
use BronOS\PhpSqlSchema\Column\String\TextColumn;
use BronOS\PhpSqlSchema\Column\String\VarCharColumn;
use BronOS\PhpSqlSchema\Index\Key;
use BronOS\PhpSqlSchema\Index\UniqueKey;
use BronOS\PhpSqlSchema\Relation\Action\CascadeAction;
use BronOS\PhpSqlSchema\Relation\ForeignKey;
use BronOS\PhpSqlSchema\SQLDatabaseSchema;
use BronOS\PhpSqlSchema\SQLTableSchema;
use PDOException;

class MigrationBuilderTest extends BaseTestCase
{
    public function testBuildQueriesNoDiff()
    {
        $builder = $this->getBuilder();

        $mq = $builder->buildQueries(new SQLDatabaseSchema('php-sql-migration', [
            new SQLTableSchema('blog', [
                new IntColumn('id', 11, true, true),
                new VarCharColumn('title', 100),
            ]),
            new SQLTableSchema('post', [
                new IntColumn('id', 11, true, true),
                new IntColumn('blog_id', 11, true),
                new VarCharColumn('title', 200),
                new TextColumn('description', false, true, true),
                new DateTimeColumn('created_at', true),
                new TimestampColumn('updated_at', false, false, true, '0000-00-00 00:00:00'),
                new VarCharColumn('keywords', 255, true, VarCharColumn::NULL_KEYWORD),
                new TinyIntColumn('unq_1', 1, false, false, false, null, false, 'Unique idx 1'),
                new IntColumn('unq_2', 11, true, false, false, null, true, 'Unique idx 2'),
            ], [
                new Key(['keywords'], 'keywords'),
                new UniqueKey(['unq_1', 'unq_2'], 'unq_1'),
            ], [
                new ForeignKey('blog_id', 'blog', 'id', 'post_ibfk_1', new CascadeAction()),
            ]),
        ]));

        $this->assertNull($mq);
    }

    public function testGenerate()
    {
        $builder = $this->getBuilder();

        $mfp = $builder->generate(null, new SQLDatabaseSchema('php-sql-migration', [
            new SQLTableSchema('blog', [
                new IntColumn('id', 11, true, true),
                new VarCharColumn('title', 100),
            ]),
            new SQLTableSchema('post', [
                new IntColumn('id', 11, true, true),
                new IntColumn('blog_id', 11, true),
                new VarCharColumn('title', 250),
                new TextColumn('description', false, true, true),
                new DateTimeColumn('created_at', true),
                new TimestampColumn('updated_at', false, false, true, '0000-00-00 00:00:00'),
                new VarCharColumn('keywords', 255, true, VarCharColumn::NULL_KEYWORD),
                new TinyIntColumn('unq_1', 1, false, false, false, null, false, 'Unique idx 1'),
                new IntColumn('unq_2', 11, true, false, false, null, true, 'Unique idx 2'),
            ], [
                new Key(['keywords'], 'keywords'),
                new UniqueKey(['unq_1', 'unq_2'], 'unq_1'),
            ], [
                new ForeignKey('blog_id', 'blog', 'id', 'post_ibfk_1', new CascadeAction()),
            ]),
        ]));

        $this->assertNotNull($mfp);
        $this->assertTrue(file_exists($mfp));

        $template = '<?php

use BronOS\PhpSqlMigrations\AbstractMigration;

/**
 * @var \PDO $pdo
 */
return new class($pdo) extends AbstractMigration
{
    /**
     * Upgrades database state.
     */
    public function up(): void
    {
        $this->run("ALTER TABLE `post` CHANGE COLUMN `title` `title` VARCHAR(250) NOT NULL;");
    }

    /**
     * Downgrades database state.
     */
    public function down(): void
    {
        $this->run("ALTER TABLE `post` CHANGE COLUMN `title` `title` VARCHAR(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT \'\';");
    }
};
';
        $this->assertEquals($template, file_get_contents($mfp));

        unlink($mfp);
    }

    public function testEmpty()
    {
        $builder = $this->getBuilder();

        $mfp = $builder->generateEmpty('MyTest Migration');
        $res = file_get_contents($mfp);

        $template = '<?php

use BronOS\PhpSqlMigrations\AbstractMigration;

/**
 * @var \PDO $pdo
 */
return new class($pdo) extends AbstractMigration
{
    /**
     * Upgrades database state.
     */
    public function up(): void
    {

    }

    /**
     * Downgrades database state.
     */
    public function down(): void
    {

    }
};
';

        $this->assertEquals($template, $res);

        unlink($mfp);
    }

    private function getBuilder(): MigrationBuilderInterface
    {
        $pdo = $this->getPdo();
        return new MigrationBuilder(
            $pdo,
            new DefaultDatabaseDiffQueryBuilder(),
            new DefaultTemplateMigrationClassGenerator(),
            new SQLDatabaseScanner(
                new SQLTableScanner(
                    new TableRepository(
                        $pdo,
                        'migrations',
                        ['user']
                    ),
                    new TableFactory(),
                    new DefaultSQLIndexScanner($pdo),
                    new DefaultSQLRelationScanner($pdo),
                    new DefaultSQLColumnScanner($pdo)
                ),
                new DefaultsRepository($pdo),
                new DatabaseFactory()
            ),
            new DefaultSQLDatabaseDiffer(),
            new MigrationsDir(__DIR__ . DIRECTORY_SEPARATOR . 'migrations'),
        );
    }
}
