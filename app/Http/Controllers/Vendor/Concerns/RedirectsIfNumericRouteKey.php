<?php

namespace App\Http\Controllers\Vendor\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait RedirectsIfNumericRouteKey
{
    /**
     * Redirect legacy numeric URLs to the model UUID route.
     *
     * @param  array<string, mixed>|Model  $routeParameters
     */
    protected function redirectIfNumericRouteKey(
        Request $request,
        Model $model,
        string $routeName,
        array|Model $routeParameters = [],
        int $segmentIndex = 3,
    ): ?RedirectResponse {
        if (! isset($model->uuid)) {
            return null;
        }

        $urlSegment = $request->segment($segmentIndex);
        if ($urlSegment !== null && $urlSegment !== $model->uuid && ctype_digit((string) $urlSegment)) {
            return redirect()->route($routeName, $routeParameters ?: $model);
        }

        return null;
    }
}
