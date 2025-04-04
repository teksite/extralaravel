<?php

namespace Teksite\Extralaravel\Traits;

use Lareon\Modules\Blog\App\Models\Annotation;
use Teksite\Handler\Actions\ServiceResult;
use Teksite\Handler\Actions\ServiceWrapper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Teksite\Handler\Services\FetchDataService;

trait TrashMethods
{
    /**
     * Cached model class name
     *
     * @var string|null
     */
    private static $modelClass = null;

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
        if (self::$modelClass === null) {
            self::$modelClass = $this->getModelClass();
        }
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
     */
    public function trashCount()
    {
        return app(ServiceWrapper::class)(fn () => $this->trashedQuery()->count());
    }

    /**
     * Get paginated trashed records
     */
    public function getTrashes(int $perPage = 25 ,mixed $fetchData=[])
    {
        return app(ServiceWrapper::class)(function () use ($fetchData, $perPage) {
                return app(FetchDataService::class)($this->trashedQuery() ,['title'] ,...$fetchData);
        });
    }

    /**
     * Restore one or multiple records
     */
    public function restore(int|array|null $id = null): ServiceResult
    {
        $ids = $this->normalizeIds($id);
        if (!empty($ids)) {
           return app(ServiceWrapper::class)(fn () => $this->trashedQuery()->whereIn('id', $ids)->restore());
        }
        return new ServiceResult(false ,null);
    }

    /**
     * Restore a single record
     */
    public function restoreOne(int $id): ServiceResult
    {
        return $this->restore($id);
    }

    /**
     * Restore all trashed records
     */
    public function restoreAll(): ServiceResult
    {
       return  app(ServiceWrapper::class)(fn () => $this->trashedQuery()->restore());
    }

    /**
     * Permanently delete records with their relationships
     */
    public function wipe(int|array $id): ServiceResult
    {
        $ids = $this->normalizeIds($id);
        if (!empty($ids)) {
           return app(ServiceWrapper::class)(function () use ($ids) {
                $query = $this->trashedQuery()->whereIn('id', $ids);
                $this->deleteRelationships($query);
                $query->forceDelete();
            });
        }
        return new ServiceResult(false ,null);

    }

    /**
     * Permanently delete a single record
     */
    public function wipeOne(int $id): ServiceResult
    {
        return $this->wipe($id);
    }

    /**
     * Permanently delete all trashed records
     */
    public function wipeAll(): ServiceResult
    {
        return app(ServiceWrapper::class)(fn () => $this->trashedQuery()->forceDelete());
    }

    /**
     * Normalize ID input to array format
     */
    private function normalizeIds(int|array|null $id): array
    {
        if (is_null($id)) {
            return [];
        }
        return is_array($id) ? $id : [$id];
    }

    /**
     * Delete related records based on model relationships
     */
    private function deleteRelationships(Builder $query): void
    {
        $this->initializeModelClass();

        if (method_exists(self::$modelClass, 'comments')) {
            $query->with('comments')->each(fn ($item) => $item->comments()->forceDelete());
        }

        if (method_exists(self::$modelClass, 'tags')) {
            $query->each(fn ($item) => $item->tags()->detach());
        }

        if (method_exists(self::$modelClass, 'categories')) {
            $query->each(fn ($item) => $item->categories()->detach());
        }
    }
}
