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


use BronOS\PhpSqlMigrations\Exception\PhpSqlMigrationsException;
use BronOS\PhpSqlMigrations\Info\MigrationInfoState;
use BronOS\PhpSqlMigrations\Repository\MigrationModel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Info command.
 * Responsible for finding differences between migration files and database state.
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */
class InfoCommand extends AbstractCommand
{
    /**
     * @var string|null
     */
    protected static $defaultName = 'info';

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Finds differences between migration files and database state')
            ->setHelp(sprintf(
                '%sFinds differences between migration files and database state%s',
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
        $style = new SymfonyStyle($input, $output);

        $infoList = $this->getServiceLocator()->getMigrationInformer()->find();

        if (count($infoList) == 0) {
            $style->error('No info');

            return self::FAILURE;
        }

        $rows = [];

        foreach ($infoList as $migrationInfo) {
            $rows[] = [
                sprintf(
                    '<fg=%s>%s</>',
                    $this->getColor($migrationInfo->getState()),
                    $migrationInfo->getName()
                ),
                sprintf(
                    '<fg=%s>%s</>',
                    $this->getColor($migrationInfo->getState()),
                    $migrationInfo->getState()->getOptionName()
                ),
                sprintf(
                    '<fg=%s>%s</>',
                    $this->getColor($migrationInfo->getState()),
                    $this->getLastUpdatedDate($migrationInfo->getModel())
                ),
            ];
        }

        $style->table([
            'Name',
            'State',
            'Last execution',
        ], $rows);

        return self::SUCCESS;
    }

    /**
     * @param MigrationModel|null $model
     *
     * @return string
     */
    private function getLastUpdatedDate(?MigrationModel $model): string
    {
        if (is_null($model)) {
            return '';
        }

        $dt = $model->getCreatedAt();
        if (!is_null($model->getUpdatedAt())) {
            $dt = $model->getUpdatedAt();
        }

        return $dt->format('Y-m-d H:i:s');
    }

    private function getColor(MigrationInfoState $state): string
    {
        if ($state->isDeleted()) {
            return 'red';
        }

        if ($state->isApplied()) {
            return 'magenta';
        }

        return 'white';
    }
}