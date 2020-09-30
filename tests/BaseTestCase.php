<?php

namespace BronOS\PhpSqlMigrations\Tests;


use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    protected static ?\PDO $pdo = null;

    protected function getPdo(): \PDO
    {
        if (is_null(self::$pdo)) {
            $this->setEnv();

            self::$pdo = new \PDO(
                sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    getenv('TEST_DB_HOST'),
                    getenv('TEST_DB_PORT'),
                    getenv('TEST_DB_NAME'),
                    'utf8',
                ),
                getenv('TEST_DB_USER'),
                getenv('TEST_DB_PASS'),
                [
                    \PDO::ATTR_PERSISTENT => true,
                    \PDO::ATTR_EMULATE_PREPARES => true,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                ]
            );

            $this->applyFixtures(self::$pdo);
        }

        return self::$pdo;
    }

    private function setEnv(): void
    {
        $fp = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
        if (file_exists($fp)) {
            $fc = file_get_contents($fp);

            foreach (explode("\n", $fc) as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    putenv($line);
                }
            }
        }
    }

    private function applyFixtures(\PDO $pdo): void
    {
        $sql = <<<SQL
            SET FOREIGN_KEY_CHECKS=0;

            DROP TABLE IF EXISTS `blog`;
            CREATE TABLE `blog` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `title` varchar(100) NOT NULL DEFAULT '',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
            
            DROP TABLE IF EXISTS `post`;
            CREATE TABLE `post` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `blog_id` int(11) unsigned NOT NULL,
              `title` varchar(200) NOT NULL DEFAULT '',
              `description` text DEFAULT NULL,
              `created_at` datetime NOT NULL DEFAULT current_timestamp(),
              `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
              `keywords` varchar(255) DEFAULT NULL,
              `unq_1` tinyint(1) NOT NULL COMMENT 'Unique idx 1',
              `unq_2` int(11) unsigned zerofill NOT NULL COMMENT 'Unique idx 2',
              PRIMARY KEY (`id`),
              UNIQUE KEY `unq_1` (`unq_1`,`unq_2`) KEY_BLOCK_SIZE=125,
              KEY `keywords` (`keywords`),
              CONSTRAINT `post_ibfk_1` FOREIGN KEY (`blog_id`) REFERENCES `blog` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;

            SET FOREIGN_KEY_CHECKS=1;
        SQL;

        $sth = $pdo->prepare($sql);
        $sth->execute();
    }
}
