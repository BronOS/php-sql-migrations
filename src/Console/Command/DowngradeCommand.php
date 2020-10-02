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
use BronOS\PhpSqlMigrations\AbstractMigration;
use BronOS\PhpSqlMigrations\Exception\PhpSqlMigrationsException;
use BronOS\PhpSqlMigrations\Info\MigrationInfo;
use BronOS\PhpSqlMigrations\Info\MigrationInfoState;
use BronOS\PhpSqlMigrations\Repository\MigrationModel;
use BronOS\PhpSqlSchema\Exception\DuplicateColumnException;
use BronOS\PhpSqlSchema\Exception\DuplicateIndexException;
use BronOS\PhpSqlSchema\Exception\DuplicateRelationException;
use BronOS\PhpSqlSchema\Exception\DuplicateTableException;
use BronOS\PhpSqlSchema\Exception\SQLTableSchemaDeclarationException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Downgrade command.
 * Responsible for downgrade database state (executions of migrations).
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */
class DowngradeCommand extends AbstractCommand
{
    /**
     * @var string|null
     */
    protected static $defaultName = 'downgrade';

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Downgrades database state')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Name of the migration to downgrade'
            )->addOption(
                '--number',
                '-m',
                InputOption::VALUE_REQUIRED,
                'Number of migrations to execute'
            )->addOption(
                '--dry-run',
                '-x',
                InputOption::VALUE_NONE,
                'Dumps queries (if any) instead of execute migration scripts'
            )->setHelp(sprintf(
                '%sDowngrades database state%s',
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
     * @throws PhpSqlMigrationsException
     *
     * @see setCode()
     */
    protected function exec(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $number = $input->getOption('number');

        $style = new SymfonyStyle($input, $output);
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        if (!$this->getServiceLocator()->getMigrationsRepository()->isTableExists()) {
            $this->getServiceLocator()->getMigrationsRepository()->createTable();
        }

        $infoList = $this->filterInfoList(
            $this->getServiceLocator()->getMigrationInformer()->find(),
            $name,
            is_null($number) ? $number : (int)$number,
        );

        if (count($infoList) == 0) {
            if (!is_null($name) && strlen($name) > 0) {
                throw new PhpSqlMigrationsException(sprintf("Migration %s not found", $name));
            }

            $style->error('No changes');
            return self::FAILURE;
        }

        $style->warning("Downgrade");

        if ((is_null($name) || strlen($name) == 0) && (is_null($number) || strlen($number) == 0) && $input->getOption('dry-run') !== true) {
            $res = $helper->ask(
                $input,
                $output,
                new ChoiceQuestion(
                    'Are you sure to run all migrations?',
                    ['Y', 'n'],
                    0
                )
            );
            if ($res === 'n') {
                return self::FAILURE;
            }
        }

        // process deleted first
        foreach ($infoList as $info) {
            if ($info->getState()->isDeleted()) {
                $style->title($info->getName());

                if ($input->getOption('dry-run')) {
                    $output->writeln([$info->getModel()->getDownQueries(), '', '']);
                } else {
                    $this->getServiceLocator()->getMigrationsRepository()->execute($info->getModel()->getDownQueries());
                    $this->getServiceLocator()->getMigrationsRepository()->delete($info->getName());
                    $output->writeln($info->getModel()->getDownQueries());
                    $output->writeln('');
                    $style->success('Success');
                }
            }
        }

        foreach ($infoList as $info) {
            if ($info->getState()->isApplied()) {
                $style->title($info->getName());

                $migrationObject = $this->getMigrationObject($info->getFileInfo()->getPathname());
                if ($input->getOption('dry-run')) {
                    $output->writeln($migrationObject->dryRunDown());
                    $output->writeln(['', '']);
                } else {
                    $migrationObject->down();
                    $output->writeln($migrationObject->getExecutedQueries());
                    $this->getServiceLocator()->getMigrationsRepository()->delete($info->getName());
                    $output->writeln('');
                    $style->success('Success');
                }
            }
        }

        return self::SUCCESS;
    }

    /**
     * @param MigrationInfo[] $infoList
     * @param string|null     $name
     * @param int|null        $number
     *
     * @return MigrationInfo[]
     */
    private function filterInfoList(array $infoList, ?string $name, ?int $number): array
    {
        $infoList = array_reverse($infoList);
        $list = [];

        $c = count($infoList);
        if ((!is_null($name) && strlen($name) > 0) || is_null($number) || $number > $c || $number == 0) {
            $number = $c;
        }

        reset($infoList);
        for ($i = 0; $i < $number; $i++) {
            $info = current($infoList);

            if ($info->getName() == $name) {
                return [$info];
            }

            if ($info->getState()->isApplied() || $info->getState()->isDeleted()) {
                $list[] = $info;
            }

            next($infoList);
        }

        return $list;
    }

    /**
     * @param string $path
     *
     * @return AbstractMigration
     *
     * @throws PhpSqlMigrationsException
     */
    private function getMigrationObject(string $path): AbstractMigration
    {
        $pdo = $this->getServiceLocator()->getPdo();

        $migrationObject = require $path;

        if (!$migrationObject instanceof AbstractMigration) {
            throw new PhpSqlMigrationsException(sprintf(
                'Migration script %s must return AbstractMigration object',
                $path
            ));
        }

        return $migrationObject;
    }
}