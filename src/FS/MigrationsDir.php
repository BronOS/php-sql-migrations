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

namespace BronOS\PhpSqlMigrations\FS;


use BronOS\PhpSqlMigrations\Exception\PhpSqlMigrationsException;
use FilesystemIterator;
use SplFileInfo;

/**
 * SQL migrations directory.
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */
class MigrationsDir implements MigrationsDirInterface
{
    private string $path;

    /**
     * MigrationsDir constructor.
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Scans dir for migration files and returns it as an array.
     *
     * @return SplFileInfo[]
     */
    public function scan(): array
    {
        $migrationFileList = [];

        foreach (new FilesystemIterator($this->getPath()) as $fileInfo) {
            if ($fileInfo->isFile() && $fileInfo->getExtension() === 'php') {
                $migrationFileList[$fileInfo->getFilename()] = $fileInfo;
            }
        }

        ksort($migrationFileList);

        return array_values($migrationFileList);
    }

    /**
     * Creates new migration file.
     *
     * @param string $migrationName
     * @param string $content
     *
     * @return string
     *
     * @throws PhpSqlMigrationsException
     */
    public function create(string $migrationName, string $content): string
    {
        if (!is_writable($this->path)) {
            throw new PhpSqlMigrationsException("Migrations dir is not writable.");
        }

        if ($this->isExists($migrationName)) {
            throw new PhpSqlMigrationsException("Migration file already exists.");
        }

        $migrationPath = $this->generateMigrationPath($migrationName);

        if (!file_put_contents($migrationPath, $content)) {
            throw new PhpSqlMigrationsException("Cannot create migration file.");
        }

        return $migrationPath;
    }

    /**
     * Check whether migration is already exists.
     *
     * @param string $migrationName
     *
     * @return bool
     */
    public function isExists(string $migrationName): bool
    {
        return file_exists($this->generateMigrationPath($migrationName));
    }

    /**
     * @param string $migrationName
     *
     * @return string
     */
    private function generateMigrationPath(string $migrationName): string
    {
        return $this->path . DIRECTORY_SEPARATOR . $migrationName . '.php';
    }
}