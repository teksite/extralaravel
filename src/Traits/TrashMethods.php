<?php

namespace Teksite\Extralaravel\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait TrashMethods
{
    /**
     * Cached model class name
     *
     * @var string|null
     */
    private static ?string $modelClass = null;

    /**
     * Get the model class name - must be implemented by the using class
     *
     * @return string
     */
    abstract protected function getModelClass(): string;

    /**
     * Initialize and cache the model class name
     */
    private function initializeModelClass(): void
    {
        if (self::$modelClass === null) self::$modelClass = $this->getModelClass();
    }

    /**
     * Get the trashed model query builder instance
     */
    private function trashedQuery(): Builder
    {
        $this->initializeModelClass();

        return self::$modelClass::onlyTrashed();
    }

    /**
     * Count all trashed records
     *
     * @return int
     */
    public function trashCount(): int
    {
        return $this->trashedQuery()->count();
    }
    /**
     * @param int|null $perPage
     * @param string|string[] $columns
     * @param string $pageName
     * @param int|null $page
     * @param \Closure|int|null $total
     * @return LengthAwarePaginator
     */
    public function getTrashes(int|null $perPage = 25, array|string $columns = ['*'], string $pageName = 'page', null|int $page = null, \Closure|int|null $total = null): LengthAwarePaginator
    {
        return $this->trashedQuery()->paginate($perPage, $columns, $pageName, $page, $total);
    }
    /**
     * Restore one or multiple records
     *
     * @param int|array|null $id
     * @return mixed
     */
    public function restore(int|array|null $id = null): mixed
    {
        $ids = $this->normalizeIds($id);

        return $this->trashedQuery()->whereIn('id', $ids)->restore();
    }


    /**
     *  Restore a single record
     *
     * @param int $id
     * @return bool
     */
    public function restoreOne(int $id): bool
    {
        return $this->restore($id);
    }


    /**
     * Restore all trashed records
     *
     * @return bool
     */
    public function restoreAll(): bool
    {
        return $this->trashedQuery()->restore();
    }


    /**
     *  Permanently delete records with their relationships
     *
     * @param int|array $id
     * @return mixed
     */
    public function wipe(int|array $id): mixed
    {
        $ids = $this->normalizeIds($id);

        return $this->trashedQuery()->whereIn('id', $ids)->forceDelete();
    }


    /**
     * Permanently delete a single record
     *
     * @param int $id
     * @return mixed
     */
    public function wipeOne(int $id): mixed
    {
        return $this->wipe($id);
    }

    /**
     * delete all soft deleted items
     *
     * @return mixed
     */
    public function wipeAll():  mixed
    {
        return $this->trashedQuery()->forceDelete();
    }

    /**
     * Normalize ID input to array format
     *
     * @param int|array|null $id
     * @return array|int[]
     */
    private function normalizeIds(int|array|null $id): array
    {
        if (is_null($id)) return [];
        return is_array($id) ? $id : [$id];
    }

}
