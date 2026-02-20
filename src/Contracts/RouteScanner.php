<?php

namespace LaravelGenerators\PostmanGenerator\Contracts;

use LaravelGenerators\PostmanGenerator\DataObjects\RouteData;

interface RouteScanner
{
    /**
     * @return RouteData[]
     */
    public function scan(): array;
}
