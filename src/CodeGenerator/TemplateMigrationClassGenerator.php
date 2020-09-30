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

namespace BronOS\PhpSqlMigrations\CodeGenerator;


use BronOS\PhpSqlMigrations\Exception\PhpSqlMigrationsException;
use BronOS\PhpSqlMigrations\QueryBuilder\MigrationQuery;

/**
 * Migration class generator.
 * Responsible for generation of migration PHP class based on MigrationQuery.
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */
class TemplateMigrationClassGenerator implements MigrationClassGeneratorInterface
{
    private const MARKER_UP = '{up}';
    private const MARKER_DOWN = '{down}';

    private string $templateFilePath;
    private ?string $template = null;

    /**
     * MigrationClassGenerator constructor.
     *
     * @param string $templateFilePath
     */
    public function __construct(string $templateFilePath)
    {
        $this->templateFilePath = $templateFilePath;
    }

    /**
     * @return string
     *
     * @throws PhpSqlMigrationsException
     */
    private function getTemplate(): string
    {
        if (!is_readable($this->templateFilePath)) {
            throw new PhpSqlMigrationsException('Cannot find migration template file.');
        }

        if (is_null($this->template)) {
            $template = file_get_contents($this->templateFilePath);

            if ($template === false) {
                throw new PhpSqlMigrationsException('Cannot read migration template file.');
            }

            $this->template =  $template;
        }

        return $this->template;
    }

    /**
     * Generates migration PHP class based on MigrationQuery and returns it as a string.
     *
     * @param MigrationQuery $migrationQuery
     *
     * @return string
     *
     * @throws PhpSqlMigrationsException
     */
    public function generate(MigrationQuery $migrationQuery): string
    {
        $template = $this->getTemplate();
        $up = [];
        $down = [];

        foreach ($migrationQuery->getUpQueries() as $upQuery) {
            $up[] = $this->getRunStatement($upQuery, self::MARKER_UP);
        }

        foreach ($migrationQuery->getDownQueries() as $downQuery) {
            $down[] = $this->getRunStatement($downQuery, self::MARKER_DOWN);
        }

        $template = str_replace(
            $this->getIndent(self::MARKER_UP) . self::MARKER_UP,
            implode("\n", $up),
            $template
        );

        $template = str_replace(
            $this->getIndent(self::MARKER_DOWN) . self::MARKER_DOWN,
            implode("\n", $down),
            $template
        );

        return $template;
    }

    /**
     * @param string $query
     * @param string $marker
     *
     * @return string
     *
     * @throws PhpSqlMigrationsException
     */
    private function getRunStatement(string $query, string $marker): string
    {
        return sprintf(
            '%s$this->run("%s");',
            $this->getIndent($marker),
            str_replace('"', '\\"', $query)
        );
    }

    /**
     * @param string $marker
     *
     * @return string
     *
     * @throws PhpSqlMigrationsException
     */
    private function getIndent(string $marker): string
    {
        preg_match('/\n?(\s+)' . $marker . '/', $this->getTemplate(), $matches);
        return $matches[1] ?? '';
    }
}