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


use BronOS\PhpSqlDiscovery\Exception\PhpSqlDiscoveryException;
use BronOS\PhpSqlMigrations\Exception\PhpSqlMigrationsException;
use BronOS\PhpSqlSchema\Exception\DuplicateColumnException;
use BronOS\PhpSqlSchema\Exception\DuplicateIndexException;
use BronOS\PhpSqlSchema\Exception\DuplicateRelationException;
use BronOS\PhpSqlSchema\Exception\DuplicateTableException;
use BronOS\PhpSqlSchema\Exception\SQLTableSchemaDeclarationException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Generate command.
 * Responsible for generation of new migration script
 * based on diff between current database state and schema object.
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */
class GenerateCommand extends AbstractCommand
{
    /**
     * @var string|null
     */
    protected static $defaultName = 'generate';

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Generates new migration script based on diff between current database state and schema object')
            ->addOption(
                '--dry-run',
                '-x',
                InputOption::VALUE_NONE,
                'Dumps diff queries (if any) instead of generate migration script'
            )->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'The name of the migration'
            )->setHelp(sprintf(
                '%sGenerates new migration script based on diff between current database state and schema object%s',
                PHP_EOL,
                PHP_EOL
            ));

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
     * @throws PhpSqlDiscoveryException
     * @throws DuplicateColumnException
     * @throws DuplicateIndexException
     * @throws DuplicateRelationException
     * @throws DuplicateTableException
     * @throws SQLTableSchemaDeclarationException
     * @throws PhpSqlMigrationsException
     *
     * @see setCode()
     */
    protected function exec(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        if ($input->getOption('dry-run')) {
            $style->warning("Dry run mode. Migration script hasn't been generated.");

            $mq = $this->getServiceLocator()->getMigrationBuilder()->buildQueries(
                $this->getServiceLocator()->getDatabaseSchema(),
            );

            if (is_null($mq)) {
                $style->error('No any diff queries has been found');
                return self::FAILURE;
            }

            $output->writeln([
                '<info>Diff queries:</info>',
                '----------------------------------------------------',
            ]);

            $output->writeln($mq->getUpQueries());

            return self::SUCCESS;
        }

        $path = $this->getServiceLocator()->getMigrationBuilder()->generate(
            $input->getArgument('name'),
            $this->getServiceLocator()->getDatabaseSchema(),
        );

        if (is_null($path)) {
            $style->error('No any diff queries has been found');
            return self::FAILURE;
        }

        $style->success($path);

        return self::SUCCESS;
    }
}