<?php

namespace Teksite\Extralaravel\Traits;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use LogicException;

trait TrashMethods
{

    /**
     * Get the model class used by this trash manager.
     *
     * @return class-string<Model>
     */
    abstract protected function getModelClass(): string;

    /**
     * Number of keys sent to the database in one query.
     *
     * Large IN (...) lists exceed the bound-parameter limits of several drivers, so keys are chunked.
     */
    protected function trashChunkSize(): int
    {
        return 1000;
    }

    /**
     * Largest page size getTrashes() will honor, so a client-supplied value cannot request the whole table.
     */
    protected function trashMaxPerPage(): int
    {
        return 250;
    }

    /**
     * Get a query builder containing only soft-deleted models.
     */
    protected function trashedQuery(): Builder
    {
        $modelClass = $this->getModelClass();

        if (!class_exists($modelClass) || !is_a($modelClass, Model::class, true)) {
            throw new LogicException("Invalid model [{$modelClass}]. The model must exist and extend ".Model::class.'.');
        }

        if (!in_array(SoftDeletes::class, class_uses_recursive($modelClass), true)) {
            throw new LogicException("Model [{$modelClass}] must use the ".SoftDeletes::class.' trait.');
        }

        return $modelClass::onlyTrashed();
    }


    /**
     * Count all soft-deleted records.
     */
    public function trashCount(): int
    {
        return $this->trashedQuery()->count();
    }


    /**
     * Get paginated soft-deleted records.
     *
     * Validate any client-supplied $perPage value before passing it in.
     */
    public function getTrashes(?int $perPage = 25, array|string $columns = ['*'], string $pageName = 'page', ?int $page = null, Closure|int|null $total = null): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage ?? 25, $this->trashMaxPerPage()));

        return $this->trashedQuery()->paginate($perPage, $columns, $pageName, $page, $total);
    }

    /**
     * Restore one or multiple soft-deleted records.
     */
    public function restore(int|string|array $ids): int
    {
        return $this->restoreKeys($this->normalizeIds($ids));
    }


    /**
     * Restore a single soft-deleted record.
     */
    public function restoreOne(int|string $id): int
    {
        return $this->restoreKeys($this->normalizeIds($id));
    }


    /**
     * Restore all soft-deleted records.
     */
    public function restoreAll(): int
    {
        return $this->trashedQuery()->restore();
    }


    /**
     * Permanently delete one or multiple soft-deleted records.
     */
    public function wipe(int|string|array $ids): int
    {
        return $this->wipeKeys($this->normalizeIds($ids));
    }


    /**
     * Permanently delete a single soft-deleted record.
     */
    public function wipeOne(int|string $id): int
    {
        return $this->wipeKeys($this->normalizeIds($id));
    }

    /**
     * Permanently delete all soft-deleted records.
     */
    public function wipeAll(): int
    {
        return $this->trashedQuery()->forceDelete();
    }

    /**
     * @param list<int|string> $ids
     */
    protected function restoreKeys(array $ids): int
    {
        return $this->applyToTrashedKeys($ids, static fn(Builder $query): int => $query->restore());
    }

    /**
     * @param list<int|string> $ids
     */
    protected function wipeKeys(array $ids): int
    {
        return $this->applyToTrashedKeys($ids, static fn(Builder $query): int => $query->forceDelete());
    }

    /**
     * Run an operation against the trashed records matching the given keys, in chunks.
     *
     * @param list<int|string>      $ids
     * @param Closure(Builder): int $operation
     */
    private function applyToTrashedKeys(array $ids, Closure $operation): int
    {
        $affected = 0;

        foreach (array_chunk($ids, max(1, $this->trashChunkSize())) as $chunk) {
            $affected += $operation($this->trashedQuery()->whereKey($chunk));
        }

        return $affected;
    }

    /**
     * Normalize one or multiple IDs into a list, dropping null and empty values.
     *
     * @return list<int|string>
     */
    protected function normalizeIds(int|string|array $ids): array
    {
        return array_values(array_filter(
            (array)$ids,
            static fn(mixed $id): bool => $id !== null && $id !== '',
        ));
    }


    /**
     * Restore one or multiple soft-deleted records with model events.
     */
    public function restoreWithEvents(int|string|array $ids): int
    {
        return $this->restoreModels($this->normalizeIds($ids));
    }

    /**
     * Restore a single soft-deleted record with model events.
     */
    public function restoreOneWithEvents(int|string $id): int
    {
        return $this->restoreModels($this->normalizeIds($id));
    }

    /**
     * Restore all soft-deleted records with model events.
     */
    public function restoreAllWithEvents(): int
    {
        return $this->restoreModels();
    }

    /**
     * Restore trashed models individually, firing model events.
     *
     * @param list<int|string>|null $ids
     */
    private function restoreModels(?array $ids = null): int
    {
        return $this->eachTrashedModel($ids, static fn(Model $model): bool => (bool)$model->restore());
    }

    /**
     * Permanently delete one or multiple soft-deleted records with model events.
     */
    public function wipeWithEvents(int|string|array $ids): int
    {
        return $this->wipeModels($this->normalizeIds($ids));
    }

    /**
     * Permanently delete a single soft-deleted record with model events.
     */
    public function wipeOneWithEvents(int|string $id): int
    {
        return $this->wipeModels($this->normalizeIds($id));
    }

    /**
     * Permanently delete all soft-deleted records with model events.
     */
    public function wipeAllWithEvents(): int
    {
        return $this->wipeModels();
    }

    /**
     * Permanently delete trashed models individually, firing model events.
     *
     * @param list<int|string>|null $ids
     */
    private function wipeModels(?array $ids = null): int
    {
        return $this->eachTrashedModel($ids, static fn(Model $model): bool => (bool)$model->forceDelete());
    }

    /**
     * Run an operation against trashed models individually in chunks.
     *
     * @param list<int|string>|null $ids
     */
    private function eachTrashedModel(?array $ids, Closure $operation): int
    {
        $query = $this->trashedQuery();

        if ($ids !== null) {
            if ($ids === [])  return 0;

            $query->whereKey($ids);
        }

        $affected = 0;
        $keyName = $query->getModel()->getKeyName();

        $query->chunkById( max(1, $this->trashChunkSize()),
            function ($models) use ($operation, &$affected): void {
                foreach ($models as $model) {
                    if ($operation($model)) $affected++;
                }
            },
            $keyName
        );

        return $affected;
    }

}
