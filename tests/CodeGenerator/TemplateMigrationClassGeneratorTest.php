<?php

namespace BronOS\PhpSqlMigrations\Tests\CodeGenerator;


use BronOS\PhpSqlMigrations\CodeGenerator\TemplateMigrationClassGenerator;
use BronOS\PhpSqlMigrations\QueryBuilder\MigrationQuery;
use PHPUnit\Framework\TestCase;

class TemplateMigrationClassGeneratorTest extends TestCase
{
    public function testGenerate()
    {
        $cg = new TemplateMigrationClassGenerator(__DIR__ . '/../../templates/migration.template');
        $migrationFileContent = $cg->generate(new MigrationQuery(
            [
                'ALTER TABLE post ADD COLUMN id INT(11);',
                'ALTER TABLE post ADD COLUMN id INT(11);',
                'ALTER TABLE post ADD COLUMN id INT(11);',
            ],
            [
                'ALTER TABLE post ADD COLUMN id INT(11);',
                'ALTER TABLE post ADD COLUMN id INT(11);',
                'ALTER TABLE post ADD COLUMN id INT(11);',
            ],
        ));

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
        $this->run("ALTER TABLE post ADD COLUMN id INT(11);");
        $this->run("ALTER TABLE post ADD COLUMN id INT(11);");
        $this->run("ALTER TABLE post ADD COLUMN id INT(11);");
    }

    /**
     * Downgrades database state.
     */
    public function down(): void
    {
        $this->run("ALTER TABLE post ADD COLUMN id INT(11);");
        $this->run("ALTER TABLE post ADD COLUMN id INT(11);");
        $this->run("ALTER TABLE post ADD COLUMN id INT(11);");
    }
};
';

        $this->assertEquals($template, $migrationFileContent);
    }
}
