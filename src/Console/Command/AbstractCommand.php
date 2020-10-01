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

namespace BronOS\PhpSqlMigrations\Console\Command;


use BronOS\PhpSqlMigrations\Config\PsmConfig;
use BronOS\PhpSqlMigrations\Exception\PhpSqlMigrationsException;
use BronOS\PhpSqlMigrations\Factory\DatabaseDifferFactoryInterface;
use BronOS\PhpSqlMigrations\Factory\DatabaseScannerFactoryInterface;
use BronOS\PhpSqlMigrations\Factory\DatabaseSchemaFactoryInterface;
use BronOS\PhpSqlMigrations\Factory\MigrationClassGeneratorFactoryInterface;
use BronOS\PhpSqlMigrations\Factory\MigrationsDirFactoryInterface;
use BronOS\PhpSqlMigrations\Factory\PDOFactoryInterface;
use BronOS\PhpSqlMigrations\MigrationBuilderInterface;
use BronOS\PhpSqlMigrations\ServiceLocator\ServiceLocator;
use BronOS\PhpSqlMigrations\ServiceLocator\ServiceLocatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

defined('__WORKDIR__') OR define('__WORKDIR__', getcwd());

/**
 * Abstract command.
 * Responsible for handling of config.
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */
abstract class AbstractCommand extends Command
{
    private PsmConfig $config;
    private ServiceLocatorInterface $serviceLocator;

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->addOption(
            '--config',
            '-c',
            InputOption::VALUE_REQUIRED,
            'The configuration file to load'
        );
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int 0 if everything went fine, or an exit code
     *
     * @see setCode()
     */
    abstract protected function exec(InputInterface $input, OutputInterface $output): int;

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int 0 if everything went fine, or an exit code
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->config = $this->loadConfig($input->getOption('config'));
        $this->serviceLocator = new ServiceLocator($this->config);

        return $this->exec($input, $output);
    }

    /**
     * @param string|null $path
     *
     * @return PsmConfig
     *
     * @throws PhpSqlMigrationsException
     */
    private function loadConfig(?string $path): PsmConfig
    {
        if (is_null($path)) {
            $path = __WORKDIR__ . '/psm.config.php';
        }

        if (!file_exists($path)) {
            throw new PhpSqlMigrationsException('Cannot find config file');
        }

        try {
            return require_once $path;
        } catch (\Throwable $e) {
            throw new PhpSqlMigrationsException(
                'Config file must return instance of BronOS\PhpSqlMigrations\Config\PsmConfig',
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @return PsmConfig
     */
    public function getConfig(): PsmConfig
    {
        return $this->config;
    }

    /**
     * @return ServiceLocatorInterface
     */
    protected function getServiceLocator(): ServiceLocatorInterface
    {
        return $this->serviceLocator;
    }
}