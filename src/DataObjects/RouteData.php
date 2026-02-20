<?php

namespace LaravelGenerators\PostmanGenerator\DataObjects;

readonly class RouteData
{
    /**
     * @param string[] $middlewares
     * @param string[] $parameterNames
     */
    public function __construct(
        public string $method,
        public string $uri,
        public ?string $controllerClass,
        public ?string $actionMethod,
        public array $middlewares,
        public ?string $routeName,
        public array $parameterNames,
    ) {
    }
}
