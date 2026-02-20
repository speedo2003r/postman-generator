<?php

namespace LaravelGenerators\PostmanGenerator\Contracts;

use LaravelGenerators\PostmanGenerator\DataObjects\EnrichedRouteData;
use LaravelGenerators\PostmanGenerator\DataObjects\BodySchema;

interface BodyExampleGenerator
{
    public function generate(EnrichedRouteData $route): BodySchema;
}
