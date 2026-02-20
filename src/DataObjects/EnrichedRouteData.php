<?php

namespace LaravelGenerators\PostmanGenerator\DataObjects;

readonly class EnrichedRouteData
{
    public string $id;

    public function __construct(
        public RouteData $route,
        public string $name,
        public string $description,
        public string $folder,
        public string $authType,
        public ?string $formRequestClass,
    ) {
        $this->id = strtoupper($this->route->method) . ' ' . $this->route->uri;
    }
}
