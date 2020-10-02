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

namespace BronOS\PhpSqlMigrations\Info;


use BronOS\PhpSqlMigrations\Exception\PhpSqlMigrationsException;
use BronOS\PhpSqlMigrations\FS\MigrationsDirInterface;
use BronOS\PhpSqlMigrations\Repository\MigrationModel;
use BronOS\PhpSqlMigrations\Repository\MigrationsRepositoryInterface;

/**
 * Responsible for finding differences between migration files and database state.
 *
 * @package   bronos\php-sql-migrations
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2020
 * @license   https://opensource.org/licenses/MIT
 */
class MigrationInformer implements MigrationInformerInterface
{
    private MigrationsRepositoryInterface $repository;
    private MigrationsDirInterface $dir;

    /**
     * MigrationInformer constructor.
     *
     * @param MigrationsRepositoryInterface $repository
     * @param MigrationsDirInterface        $dir
     */
    public function __construct(MigrationsRepositoryInterface $repository, MigrationsDirInterface $dir)
    {
        $this->repository = $repository;
        $this->dir = $dir;
    }

    /**
     * Find differences between migration files and database state.
     *
     * @param bool $changesOnly
     *
     * @return MigrationInfo[]
     *
     * @throws PhpSqlMigrationsException
     */
    public function find(bool $changesOnly = false): array
    {
        $list = [];
        $processed = [];

        $files = $this->dir->scan();
        $models = $this->repository->findAll();

        foreach ($files as $file) {
            $name = $file->getBasename('.php');
            $processed[] = $name;
            $model = $this->findModel($models, $name);

            if ($changesOnly && !is_null($model)) {
                continue;
            }

            $list[] = new MigrationInfo(
                $name,
                is_null($model) ? MigrationInfoState::NEW() : MigrationInfoState::APPLIED(),
                $model,
                $file
            );
        }

        foreach ($models as $model) {
            if (in_array($model->getName(), $processed)) {
                continue;
            }

            $list[] = new MigrationInfo(
                $model->getName(),
                MigrationInfoState::DELETED(),
                $model,
                null
            );
        }

        return $list;
    }

    /**
     * @param MigrationModel[] $models
     * @param string           $name
     *
     * @return MigrationModel|null
     */
    private function findModel(array $models, string $name): ?MigrationModel
    {
        foreach ($models as $model) {
            if ($model->getName() == $name) {
                return $model;
            }
        }

        return null;
    }
}