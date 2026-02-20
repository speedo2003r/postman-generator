<?php

namespace LaravelGenerators\PostmanGenerator\Contracts;

use LaravelGenerators\PostmanGenerator\DataObjects\RouteData;
use LaravelGenerators\PostmanGenerator\DataObjects\EnrichedRouteData;

interface MetadataExtractor
{
    public function extract(RouteData $route): EnrichedRouteData;
}
