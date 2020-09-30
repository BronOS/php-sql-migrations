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

namespace BronOS\PhpSqlMigrations;


use BronOS\PhpSqlDiff\SQLDatabaseDifferInterface;
use BronOS\PhpSqlDiscovery\Exception\PhpSqlDiscoveryException;
use BronOS\PhpSqlDiscovery\SQLDatabaseScannerInterface;
use BronOS\PhpSqlMigrations\CodeGenerator\MigrationClassGeneratorInterface;
use BronOS\PhpSqlMigrations\Exception\PhpSqlMigrationsException;
use BronOS\PhpSqlMigrations\FS\MigrationsDirInterface;
use BronOS\PhpSqlMigrations\QueryBuilder\DatabaseDiffQueryBuilderInterface;
use BronOS\PhpSqlMigrations\QueryBuilder\MigrationQuery;
use BronOS\PhpSqlSchema\Exception\DuplicateColumnException;
use BronOS\PhpSqlSchema\Exception\DuplicateIndexException;
use BronOS\PhpSqlSchema\Exception\DuplicateRelationException;
use BronOS\PhpSqlSchema\Exception\DuplicateTableException;
use BronOS\PhpSqlSchema\Exception\SQLTableSchemaDeclarationException;
use BronOS\PhpSqlSchema\SQLDatabaseSchemaInterface;
use DateTime;
use PDO;

/**
 * SQL migration builder.
 * Responsible for finding differences between defined schema and database state,
 * and building migration file based on it.
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */
class MigrationBuilder implements MigrationBuilderInterface
{
    private PDO $pdo;
    private DatabaseDiffQueryBuilderInterface $queryBuilder;
    private MigrationClassGeneratorInterface $classGenerator;
    private SQLDatabaseScannerInterface $scanner;
    private SQLDatabaseDifferInterface $differ;
    private MigrationsDirInterface $migrationsDir;

    /**
     * SQLMigrationBuilder constructor.
     *
     * @param PDO                               $pdo
     * @param DatabaseDiffQueryBuilderInterface $queryBuilder
     * @param MigrationClassGeneratorInterface  $classGenerator
     * @param SQLDatabaseScannerInterface       $scanner
     * @param SQLDatabaseDifferInterface        $differ
     * @param MigrationsDirInterface            $migrationsDir
     */
    public function __construct(
        PDO $pdo,
        DatabaseDiffQueryBuilderInterface $queryBuilder,
        MigrationClassGeneratorInterface $classGenerator,
        SQLDatabaseScannerInterface $scanner,
        SQLDatabaseDifferInterface $differ,
        MigrationsDirInterface $migrationsDir
    ) {
        $this->pdo = $pdo;
        $this->queryBuilder = $queryBuilder;
        $this->scanner = $scanner;
        $this->differ = $differ;
        $this->classGenerator = $classGenerator;
        $this->migrationsDir = $migrationsDir;
    }

    /**
     * Finds diff between passed schema and DB state (with PDO).
     * Builds SQL queries if diff has been found.
     *
     * @param SQLDatabaseSchemaInterface $schema
     *
     * @return MigrationQuery|null Source database schema
     *
     * @throws PhpSqlDiscoveryException
     * @throws DuplicateColumnException
     * @throws DuplicateIndexException
     * @throws DuplicateRelationException
     * @throws DuplicateTableException
     * @throws SQLTableSchemaDeclarationException
     */
    public function buildQueries(SQLDatabaseSchemaInterface $schema): ?MigrationQuery
    {
        $dbSchema = $this->scanner->scan();
        $diff = $this->differ->diff(
            $schema,
            $dbSchema,
            $dbSchema->getDefaultEngine(),
            $dbSchema->getDefaultCharset(),
            $dbSchema->getDefaultCollation()
        );

        if (is_null($diff)) {
            return null;
        }

        return $this->queryBuilder->buildQuery(
            $diff,
            $dbSchema->getDefaultEngine(),
            $dbSchema->getDefaultCharset(),
            $dbSchema->getDefaultCollation()
        );
    }

    /**
     * Finds diff between passed schema and DB state (with PDO).
     * Generates new migration file if diff has been found
     * and return file path of it or null otherwise.
     *
     * @param string|null                $name   Migration name
     * @param SQLDatabaseSchemaInterface $schema Source database schema
     *
     * @return string|null
     *
     * @throws DuplicateColumnException
     * @throws DuplicateIndexException
     * @throws DuplicateRelationException
     * @throws DuplicateTableException
     * @throws PhpSqlDiscoveryException
     * @throws SQLTableSchemaDeclarationException
     * @throws PhpSqlMigrationsException
     */
    public function generate(?string $name, SQLDatabaseSchemaInterface $schema): ?string
    {
        $mq = $this->buildQueries($schema);

        if (is_null($mq)) {
            return null;
        }

        return $this->migrationsDir->create(
            $this->generateMigrationName($name),
            $this->classGenerator->generate($mq)
        );
    }

    /**
     * Generates new empty migration file and returns file path of it.
     *
     * @param string|null $name Migration name
     *
     * @return string
     *
     * @throws PhpSqlMigrationsException
     */
    public function generateEmpty(?string $name = null): string
    {
        return $this->migrationsDir->create(
            $this->generateMigrationName($name),
            $this->classGenerator->generate(new MigrationQuery([], []))
        );
    }

    /**
     * @param string|null $name
     * @param int         $i
     *
     * @return string
     */
    private function generateMigrationName(?string $name, int $i = 1): string
    {
        $migrationName = DateTime::createFromFormat(
                'U.u',
                (string)microtime(true)
            )->format('Y-z-') . (time() % 86400);


        if (!is_null($name)) {
            $migrationName .= "_" . $this->normalizeName($name);
        }

        if ($i > 1) {
            $migrationName .= "-$i";
        }

        if ($this->migrationsDir->isExists($migrationName)) {
            $i++;
            return $this->generateMigrationName($name, $i);
        }

        return $migrationName;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function normalizeName(string $name): string
    {
        return preg_replace('/[^A-Za-z0-9\-]/', '_', $name);
    }
}