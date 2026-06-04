<?php

namespace App\Models\Concerns;

trait RoutesByUuid
{
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Resolve route binding by UUID; fall back to numeric id for old links.
     */
    public function resolveRouteBinding($value, $field = null): ?static
    {
        $field = $field ?? $this->getRouteKeyName();

        $model = static::query()->where($field, $value)->first();

        if ($model === null && is_numeric($value)) {
            $model = static::query()->whereKey((int) $value)->first();
        }

        return $model;
    }
}
