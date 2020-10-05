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

namespace BronOS\PhpSqlMigrations\Console;


use BronOS\PhpSqlMigrations\Console\Command\CreateCommand;
use BronOS\PhpSqlMigrations\Console\Command\DowngradeCommand;
use BronOS\PhpSqlMigrations\Console\Command\GenerateCommand;
use BronOS\PhpSqlMigrations\Console\Command\InfoCommand;
use BronOS\PhpSqlMigrations\Console\Command\MigrateCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Psm CLI Application.
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */
class PsmApplication extends Application
{
    public static array $commands = [
        CreateCommand::class,
        GenerateCommand::class,
        InfoCommand::class,
        MigrateCommand::class,
        DowngradeCommand::class,
    ];

    /**
     * Initialize the Psm console application.
     */
    public function __construct()
    {
        parent::__construct('Php SQL Migrations');

        $this->addCommands(array_map(function (string $cls) {
            return new $cls();
        }, static::$commands));
    }

    /**
     * Runs the current application.
     *
     * @param InputInterface  $input  An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return int 0 if everything went fine, or an error code
     *
     * @throws Throwable
     */
    public function doRun(InputInterface $input, OutputInterface $output): int
    {
        $this->setEnv();
        $output->writeln('');
        // always show the version information except when the user invokes the help
        // command as that already does it
        if (($input->hasParameterOption(['--help', '-h']) !== false)
            || ($input->getFirstArgument() !== null && $input->getFirstArgument() !== 'list')
        ) {
            $output->writeln([$this->getLongVersion(), '']);
        }

        return parent::doRun($input, $output);
    }

    /**
     * Applied ENV from ".env" file.
     */
    private function setEnv(): void
    {
        $fp = __WORKDIR__ . DIRECTORY_SEPARATOR . '.env';
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
}