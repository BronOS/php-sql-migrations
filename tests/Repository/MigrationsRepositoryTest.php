<?php

namespace BronOS\PhpSqlMigrations\Tests\Repository;


use BronOS\PhpSqlMigrations\Repository\MigrationModel;
use BronOS\PhpSqlMigrations\Repository\MigrationModelFactory;
use BronOS\PhpSqlMigrations\Repository\MigrationsRepository;
use BronOS\PhpSqlMigrations\Tests\BaseTestCase;

class MigrationsRepositoryTest extends BaseTestCase
{
    public function testAll()
    {
        $pdo = $this->getPdo();
        $pdo->query("DROP TABLE IF EXISTS `migrations`");

        $repository = new MigrationsRepository(
            $pdo,
            new MigrationModelFactory(),
            'migrations'
        );

        $this->assertFalse($repository->isTableExists());
        $this->assertTrue($repository->createTable());

        $this->assertTrue($repository->insertOrUpdate('test', 'q1'));
        $rows = $pdo->query('SELECT * FROM migrations')->fetchAll();
        $this->assertCount(1, $rows);
        $row = $rows[0];
        $this->assertCount(4, $row);
        $this->assertArrayHasKey('name', $row);
        $this->assertArrayHasKey('created_at', $row);
        $this->assertArrayHasKey('updated_at', $row);
        $this->assertArrayHasKey('down_queries', $row);
        $this->assertEquals('test', $row['name']);
        $this->assertTrue(is_string($row['created_at']) && strlen($row['created_at']) > 0);
        $this->assertNull($row['updated_at']);
        $this->assertEquals('q1', $row['down_queries']);

        $rows = $repository->findAll();
        $this->assertCount(1, $rows);
        $model = $rows[0];
        $this->assertInstanceOf(MigrationModel::class, $model);
        $this->assertEquals('test', $model->getName());
        $this->assertInstanceOf(\DateTime::class, $model->getCreatedAt());
        $this->assertEquals(date('Y'), $model->getCreatedAt()->format('Y'));
        $this->assertNull($model->getUpdatedAt());
        $this->assertEquals('q1', $model->getDownQueries());

        $this->assertTrue($repository->insertOrUpdate('test', 'q2'));
        $rows = $pdo->query('SELECT * FROM migrations')->fetchAll();
        $this->assertCount(1, $rows);
        $row = $rows[0];
        $this->assertCount(4, $row);
        $this->assertArrayHasKey('name', $row);
        $this->assertArrayHasKey('created_at', $row);
        $this->assertArrayHasKey('updated_at', $row);
        $this->assertArrayHasKey('down_queries', $row);
        $this->assertEquals('test', $row['name']);
        $this->assertTrue(is_string($row['created_at']) && strlen($row['created_at']) > 0);
        $this->assertTrue(is_string($row['updated_at']) && strlen($row['updated_at']) > 0);
        $this->assertNotEquals('0000-00-00 00:00:00', $row['updated_at']);
        $this->assertEquals('q2', $row['down_queries']);

        $this->assertTrue($repository->delete('test'));
        $this->assertCount(0, $pdo->query('SELECT * FROM migrations')->fetchAll());
    }
}
