<?php

namespace Teksite\Extralaravel\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use LogicException;

trait TrashMethods
{
    /**
     * Get the model class used by this trash manager.
     */
    abstract protected function getModelClass(): string;


    /**
     * Get a query builder containing only soft-deleted models.
     */
    private function trashedQuery(): Builder
    {
        $modelClass = $this->getModelClass();

        if (!class_exists($modelClass) || !is_a($modelClass, Model::class, true)) {
            throw new \LogicException("Invalid model [{$modelClass}]. " . "The model must exist and extend " . Model::class . '.');
        }

        if (!in_array(SoftDeletes::class, class_uses_recursive($modelClass), true)) {
            throw new LogicException("Model [{$modelClass}] must use the " . SoftDeletes::class . ' trait.');
        }

        return $modelClass::onlyTrashed();
    }


    /**
     * Get the model's primary key name.
     *
     * This supports models whose primary key is not "id".
     */
    private function getModelKeyName(): string
    {
        return $this->trashedQuery()->getModel()->getKeyName();
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
     */
    public function getTrashes(?int $perPage = 25, array|string $columns = ['*'], string $pageName = 'page', ?int $page = null, \Closure|int|null $total = null): LengthAwarePaginator
    {
        return $this->trashedQuery()->paginate(
            $perPage,
            $columns,
            $pageName,
            $page,
            $total
        );
    }

    /**
     * Restore one or multiple soft-deleted records.
     */
    public function restore(int|string|array $ids): int
    {
        $ids = $this->normalizeIds($ids);

        if ($ids === []) return 0;

        return $this->trashedQuery()
                    ->whereKey($ids)
                    ->restore();
    }


    /**
     * Restore a single soft-deleted record.
     */
    public function restoreOne(int|string $id): int
    {
        return $this->restore($id);
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
        $ids = $this->normalizeIds($ids);

        if ($ids === []) return 0;

        return $this->trashedQuery()->whereKey($ids)->forceDelete();
    }


    /**
     * Permanently delete a single soft-deleted record.
     */
    public function wipeOne(int|string $id): int
    {
        return $this->wipe($id);
    }

    /**
     * Permanently delete all soft-deleted records.
     */
    public function wipeAll(): int
    {
        return $this->trashedQuery()->forceDelete();
    }

    /**
     * Normalize one or multiple IDs into an array.
     */
    private function normalizeIds(int|string|array $ids): array
    {
        return is_array($ids) ? array_values($ids) : [$ids];
    }

}
