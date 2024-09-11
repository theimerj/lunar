<?php

namespace Lunar\Base\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Lunar\Facades\ModelManifest;

trait HasModelExtending
{
    public function newModelQuery(): Builder
    {
        $realClass = static::modelClass();

        // If they are both the same class i.e. they haven't changed
        // then just call the parent method.
        if ($this instanceof $realClass) {
            return parent::newModelQuery();
        }

        return $this->newEloquentBuilder(
            $this->newBaseQueryBuilder()
        )->setModel(new $realClass($this->toArray()));
    }

    public static function __callStatic($method, $parameters)
    {
        if (
            ! static::isLunarInstance()
        ) {
            return (new (static::modelClass()))->$method(...$parameters);
        }

        return (new static)->$method(...$parameters);
    }

    /**
     * Returns the model class registered in the model manifest.
     */
    public static function modelClass(): string
    {
        $contractClass = ModelManifest::guessContractClass(static::class);

        return ModelManifest::get($contractClass) ?? static::class;
    }

    /**
     * Returns the morph class for a model class registered in the model manifest.
     */
    public function getMorphClass(): string
    {
        $morphMap = Relation::morphMap();

        if ($customModelMorphMap = array_search(static::modelClass(), $morphMap, true)) {
            return $customModelMorphMap;
        }

        foreach (class_parents(static::class) as $ancestorClass) {
            if (ModelManifest::isLunarModel($ancestorClass) && $ancestorModelMorphMap = array_search($ancestorClass, $morphMap, true)) {
                return $ancestorModelMorphMap;
            }
        }

        return parent::getMorphClass();
    }

    public static function isLunarInstance(): bool
    {
        return static::class == static::modelClass();
    }

    public static function observe($classes): void
    {
        $instance = new static;

        if (
            ! static::isLunarInstance()
        ) {
            $instance = new (static::modelClass());
        }

        foreach (Arr::wrap($classes) as $class) {
            $instance->registerObserver($class);
        }
    }
}
