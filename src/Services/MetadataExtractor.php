<?php

namespace LaravelGenerators\PostmanGenerator\Services;

use LaravelGenerators\PostmanGenerator\Attributes\PostmanMeta;
use LaravelGenerators\PostmanGenerator\Contracts\MetadataExtractor as Contract;
use LaravelGenerators\PostmanGenerator\DataObjects\EnrichedRouteData;
use LaravelGenerators\PostmanGenerator\DataObjects\RouteData;
use ReflectionMethod;

class MetadataExtractor implements Contract
{
    public function __construct(
        protected AuthDetector $authDetector,
        protected FormRequestResolver $formRequestResolver
    ) {
    }

    public function extract(RouteData $route): EnrichedRouteData
    {
        $metadata = $this->getRawMetadata($route);

        return new EnrichedRouteData(
            route: $route,
            name: $metadata['name'],
            description: $metadata['description'],
            folder: $metadata['folder'],
            authType: $this->authDetector->detect($route->middlewares),
            formRequestClass: $route->controllerClass && $route->actionMethod
                ? $this->formRequestResolver->resolve($route->controllerClass, $route->actionMethod)
                : null
        );
    }

    protected function getRawMetadata(RouteData $route): array
    {
        $defaults = [
            'name' => $this->deriveName($route),
            'description' => '',
            'folder' => $this->deriveFolder($route),
        ];

        if (!$route->controllerClass || !$route->actionMethod) {
            return $defaults;
        }

        $reflection = new ReflectionMethod($route->controllerClass, $route->actionMethod);

        // 1. Check Attributes
        $attributes = $reflection->getAttributes(PostmanMeta::class);
        if (!empty($attributes)) {
            /** @var PostmanMeta $instance */
            $instance = $attributes[0]->newInstance();
            return [
                'name' => $instance->name ?: $defaults['name'],
                'description' => $instance->description ?: $defaults['description'],
                'folder' => $instance->folder ?: $defaults['folder'],
            ];
        }

        // 2. Check DocBlock
        $docComment = $reflection->getDocComment();
        if ($docComment) {
            $parsed = $this->parseDocBlock($docComment);
            return [
                'name' => $parsed['name'] ?: $defaults['name'],
                'description' => $parsed['description'] ?: $defaults['description'],
                'folder' => $parsed['folder'] ?: $defaults['folder'],
            ];
        }

        return $defaults;
    }

    protected function deriveName(RouteData $route): string
    {
        if ($route->routeName) {
            return (string) str($route->routeName)->replace('.', ' ')->title();
        }

        $uriParts = explode('/', $route->uri);
        $lastPart = end($uriParts);
        
        return $route->method . ' ' . (string) str($lastPart)->title();
    }

    protected function deriveFolder(RouteData $route): string
    {
        // Use first segment after 'api' if possible
        $parts = explode('/', trim($route->uri, '/'));
        $targetGroup = config('postman-generator.route_groups', 'api');
        
        $foundApi = false;
        foreach ($parts as $part) {
            if ($foundApi) {
                return (string) str($part)->plural()->title();
            }
            if ($part === $targetGroup) {
                $foundApi = true;
            }
        }

        if ($route->controllerClass) {
            $className = class_basename($route->controllerClass);
            return (string) str($className)->replace('Controller', '')->plural()->title();
        }

        return 'Misc';
    }

    protected function parseDocBlock(string $docComment): array
    {
        $tags = ['name' => '', 'description' => '', 'folder' => ''];
        
        $patterns = [
            'name' => '/@postman-name\s+(.+)/',
            'description' => '/@postman-description\s+(.+)/',
            'folder' => '/@postman-folder\s+(.+)/',
        ];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $docComment, $matches)) {
                $tags[$key] = trim($matches[1]);
            }
        }

        return $tags;
    }
}
