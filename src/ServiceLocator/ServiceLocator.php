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

namespace BronOS\PhpSqlMigrations\ServiceLocator;


use BronOS\PhpSqlDiff\SQLDatabaseDifferInterface;
use BronOS\PhpSqlDiscovery\SQLDatabaseScannerInterface;
use BronOS\PhpSqlMigrations\CodeGenerator\MigrationClassGeneratorInterface;
use BronOS\PhpSqlMigrations\Config\PsmConfig;
use BronOS\PhpSqlMigrations\Factory\DatabaseDifferFactoryInterface;
use BronOS\PhpSqlMigrations\Factory\DatabaseDiffQueryBuilderFactoryInterface;
use BronOS\PhpSqlMigrations\Factory\DatabaseScannerFactoryInterface;
use BronOS\PhpSqlMigrations\Factory\DatabaseSchemaFactoryInterface;
use BronOS\PhpSqlMigrations\Factory\MigrationBuilderFactoryInterface;
use BronOS\PhpSqlMigrations\Factory\MigrationClassGeneratorFactoryInterface;
use BronOS\PhpSqlMigrations\Factory\MigrationInformerFactoryInterface;
use BronOS\PhpSqlMigrations\Factory\MigrationsDirFactoryInterface;
use BronOS\PhpSqlMigrations\Factory\PDOFactoryInterface;
use BronOS\PhpSqlMigrations\FS\MigrationsDirInterface;
use BronOS\PhpSqlMigrations\Info\MigrationInformerInterface;
use BronOS\PhpSqlMigrations\MigrationBuilderInterface;
use BronOS\PhpSqlMigrations\QueryBuilder\DatabaseDiffQueryBuilderInterface;
use BronOS\PhpSqlMigrations\Repository\MigrationModelFactory;
use BronOS\PhpSqlMigrations\Repository\MigrationsRepository;
use BronOS\PhpSqlMigrations\Repository\MigrationsRepositoryInterface;
use BronOS\PhpSqlSchema\SQLDatabaseSchema;
use PDO;

/**
 * Service locator.
 * Responsible for instantiating and handling of dependencies.
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */
class ServiceLocator implements ServiceLocatorInterface
{
    private PsmConfig $config;
    private array $registry = [];

    /**
     * ServiceLocator constructor.
     *
     * @param PsmConfig $config
     */
    public function __construct(PsmConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @return PDOFactoryInterface
     */
    public function getPdoFactory(): PDOFactoryInterface
    {
        if (!isset($this->registry[PDOFactoryInterface::class])) {
            $this->registry[PDOFactoryInterface::class] = new $this->config->pdoFactoryClass;
        }

        return $this->registry[PDOFactoryInterface::class];
    }

    /**
     * @return MigrationsDirFactoryInterface
     */
    public function getMigrationsDirFactory(): MigrationsDirFactoryInterface
    {
        if (!isset($this->registry[MigrationsDirFactoryInterface::class])) {
            $this->registry[MigrationsDirFactoryInterface::class] = new $this->config->migrationsDirFactoryClass;
        }

        return $this->registry[MigrationsDirFactoryInterface::class];
    }

    /**
     * @return MigrationClassGeneratorFactoryInterface
     */
    public function getMigrationClassGeneratorFactory(): MigrationClassGeneratorFactoryInterface
    {
        if (!isset($this->registry[MigrationClassGeneratorFactoryInterface::class])) {
            $this->registry[MigrationClassGeneratorFactoryInterface::class] = new $this->config->migrationsClassGeneratorFactoryClass;
        }

        return $this->registry[MigrationClassGeneratorFactoryInterface::class];
    }

    /**
     * @return MigrationBuilderFactoryInterface
     */
    public function getMigrationBuilderFactory(): MigrationBuilderFactoryInterface
    {
        if (!isset($this->registry[MigrationBuilderFactoryInterface::class])) {
            $this->registry[MigrationBuilderFactoryInterface::class] = new $this->config->migrationBuilderFactoryClass;
        }

        return $this->registry[MigrationBuilderFactoryInterface::class];
    }

    /**
     * @return DatabaseSchemaFactoryInterface
     */
    public function getDatabaseSchemaFactory(): DatabaseSchemaFactoryInterface
    {
        if (!isset($this->registry[DatabaseSchemaFactoryInterface::class])) {
            $this->registry[DatabaseSchemaFactoryInterface::class] = new $this->config->databaseSchemaFactoryClass;
        }

        return $this->registry[DatabaseSchemaFactoryInterface::class];
    }

    /**
     * @return DatabaseScannerFactoryInterface
     */
    public function getDatabaseScannerFactory(): DatabaseScannerFactoryInterface
    {
        if (!isset($this->registry[DatabaseScannerFactoryInterface::class])) {
            $this->registry[DatabaseScannerFactoryInterface::class] = new $this->config->databaseScannerFactoryClass;
        }

        return $this->registry[DatabaseScannerFactoryInterface::class];
    }

    /**
     * @return DatabaseDiffQueryBuilderFactoryInterface
     */
    public function getDatabaseDiffQueryBuilderFactory(): DatabaseDiffQueryBuilderFactoryInterface
    {
        if (!isset($this->registry[DatabaseDiffQueryBuilderFactoryInterface::class])) {
            $this->registry[DatabaseDiffQueryBuilderFactoryInterface::class] = new $this->config->databaseDiffQueryBuilderFactoryClass;
        }

        return $this->registry[DatabaseDiffQueryBuilderFactoryInterface::class];
    }

    /**
     * @return DatabaseDifferFactoryInterface
     */
    public function getDatabaseDifferFactory(): DatabaseDifferFactoryInterface
    {
        if (!isset($this->registry[DatabaseDifferFactoryInterface::class])) {
            $this->registry[DatabaseDifferFactoryInterface::class] = new $this->config->databaseDifferFactoryClass;
        }

        return $this->registry[DatabaseDifferFactoryInterface::class];
    }

    /**
     * @return MigrationInformerFactoryInterface
     */
    public function getMigrationInformerFactory(): MigrationInformerFactoryInterface
    {
        if (!isset($this->registry[MigrationInformerFactoryInterface::class])) {
            $this->registry[MigrationInformerFactoryInterface::class] = new $this->config->migrationInformerFactoryClass;
        }

        return $this->registry[MigrationInformerFactoryInterface::class];
    }

    /**
     * @return PDO
     */
    public function getPdo(): PDO
    {
        if (!isset($this->registry[PDO::class])) {
            $this->registry[PDO::class] = $this->getPdoFactory()->make(
                $this->config,
                $this->getDatabaseSchema()->getName()
            );
        }

        return $this->registry[PDO::class];
    }

    /**
     * @return MigrationsDirInterface
     */
    public function getMigrationsDir(): MigrationsDirInterface
    {
        if (!isset($this->registry[MigrationsDirInterface::class])) {
            $this->registry[MigrationsDirInterface::class] = $this->getMigrationsDirFactory()->make(
                $this->config,
            );
        }

        return $this->registry[MigrationsDirInterface::class];
    }

    /**
     * @return MigrationClassGeneratorInterface
     */
    public function getMigrationClassGenerator(): MigrationClassGeneratorInterface
    {
        if (!isset($this->registry[MigrationClassGeneratorInterface::class])) {
            $this->registry[MigrationClassGeneratorInterface::class] = $this->getMigrationClassGeneratorFactory()->make(
                $this->config,
            );
        }

        return $this->registry[MigrationClassGeneratorInterface::class];
    }

    /**
     * @return MigrationBuilderInterface
     */
    public function getMigrationBuilder(): MigrationBuilderInterface
    {
        if (!isset($this->registry[MigrationBuilderInterface::class])) {
            $this->registry[MigrationBuilderInterface::class] = $this->getMigrationBuilderFactory()->make(
                $this->config,
                $this->getPdo(),
                $this->getDatabaseDiffQueryBuilder(),
                $this->getMigrationClassGenerator(),
                $this->getDatabaseScanner(),
                $this->getDatabaseDiffer(),
                $this->getMigrationsDir()
            );
        }

        return $this->registry[MigrationBuilderInterface::class];
    }

    /**
     * @return SQLDatabaseSchema
     */
    public function getDatabaseSchema(): SQLDatabaseSchema
    {
        if (!isset($this->registry[SQLDatabaseSchema::class])) {
            $this->registry[SQLDatabaseSchema::class] = $this->getDatabaseSchemaFactory()->make(
                $this->config,
            );
        }

        return $this->registry[SQLDatabaseSchema::class];
    }

    /**
     * @return SQLDatabaseScannerInterface
     */
    public function getDatabaseScanner(): SQLDatabaseScannerInterface
    {
        if (!isset($this->registry[SQLDatabaseScannerInterface::class])) {
            $this->registry[SQLDatabaseScannerInterface::class] = $this->getDatabaseScannerFactory()->make(
                $this->config,
                $this->getPdo()
            );
        }

        return $this->registry[SQLDatabaseScannerInterface::class];
    }

    /**
     * @return DatabaseDiffQueryBuilderInterface
     */
    public function getDatabaseDiffQueryBuilder(): DatabaseDiffQueryBuilderInterface
    {
        if (!isset($this->registry[DatabaseDiffQueryBuilderInterface::class])) {
            $this->registry[DatabaseDiffQueryBuilderInterface::class] = $this->getDatabaseDiffQueryBuilderFactory()->make(
                $this->config,
            );
        }

        return $this->registry[DatabaseDiffQueryBuilderInterface::class];
    }

    /**
     * @return SQLDatabaseDifferInterface
     */
    public function getDatabaseDiffer(): SQLDatabaseDifferInterface
    {
        if (!isset($this->registry[SQLDatabaseDifferInterface::class])) {
            $this->registry[SQLDatabaseDifferInterface::class] = $this->getDatabaseDifferFactory()->make(
                $this->config,
            );
        }

        return $this->registry[SQLDatabaseDifferInterface::class];
    }

    /**
     * @return MigrationInformerInterface
     */
    public function getMigrationInformer(): MigrationInformerInterface
    {
        if (!isset($this->registry[MigrationInformerInterface::class])) {
            $this->registry[MigrationInformerInterface::class] = $this->getMigrationInformerFactory()->make(
                $this->config,
                $this->getMigrationsRepository(),
                $this->getMigrationsDir()
            );
        }

        return $this->registry[MigrationInformerInterface::class];
    }

    /**
     * @return MigrationsRepositoryInterface
     */
    public function getMigrationsRepository(): MigrationsRepositoryInterface
    {
        if (!isset($this->registry[MigrationsRepositoryInterface::class])) {
            $this->registry[MigrationsRepositoryInterface::class] = new MigrationsRepository(
                $this->getPdo(),
                new MigrationModelFactory(),
                $this->config->migrationsTable
            );
        }

        return $this->registry[MigrationsRepositoryInterface::class];
    }
}