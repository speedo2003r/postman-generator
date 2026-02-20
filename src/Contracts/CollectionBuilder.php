<?php

namespace LaravelGenerators\PostmanGenerator\Contracts;

use LaravelGenerators\PostmanGenerator\DataObjects\EnrichedRouteData;
use LaravelGenerators\PostmanGenerator\DataObjects\BodySchema;

interface CollectionBuilder
{
    /**
     * @param EnrichedRouteData[] $routes
     * @param BodySchema[] $bodies
     * @return array
     */
    public function build(array $routes, array $bodies): array;
}
