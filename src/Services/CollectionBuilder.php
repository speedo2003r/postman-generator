<?php

namespace LaravelGenerators\PostmanGenerator\Services;

use LaravelGenerators\PostmanGenerator\Contracts\CollectionBuilder as Contract;
use LaravelGenerators\PostmanGenerator\DataObjects\BodySchema;
use LaravelGenerators\PostmanGenerator\DataObjects\EnrichedRouteData;

class CollectionBuilder implements Contract
{
    /**
     * @param EnrichedRouteData[] $routes
     * @param BodySchema[] $bodies Keyed by route URI
     */
    public function build(array $routes, array $bodies): array
    {
        $collectionName = config('postman-generator.collection_name', 'Laravel API');
        $baseUrl = config('postman-generator.base_url', '{{base_url}}');

        $items = $this->groupIntoFolders($routes, $bodies);

        $variables = [
            ['key' => 'base_url', 'value' => $baseUrl, 'type' => 'string'],
            ['key' => 'token', 'value' => '', 'type' => 'string'],
        ];

        if (config('postman-generator.include_tenant_id')) {
            $variables[] = ['key' => 'tenant_id', 'value' => '', 'type' => 'string'];
        }

        return [
            'info' => [
                'name' => $collectionName,
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'item' => $items,
            'variable' => $variables,
        ];
    }

    protected function groupIntoFolders(array $routes, array $bodies): array
    {
        $folders = [];
        $names = [];

        foreach ($routes as $route) {
            $folderName = $route->folder;
            $requestName = $this->getUniqueName($route->name, $names);
            
            $item = $this->buildRequestItem($route, $requestName, $bodies[$route->route->uri] ?? null);

            if (!isset($folders[$folderName])) {
                $folders[$folderName] = [
                    'name' => $folderName,
                    'item' => [],
                ];
            }

            $folders[$folderName]['item'][] = $item;
        }

        return array_values($folders);
    }

    protected function getUniqueName(string $name, array &$names): string
    {
        if (!isset($names[$name])) {
            $names[$name] = 1;
            return $name;
        }

        // Counter starts at 1 for the first occurrence. 
        // Subsequent occurrences will have suffixes starting from (2).
        $names[$name]++;
        return "$name (" . $names[$name] . ")";
    }

    protected function buildRequestItem(EnrichedRouteData $route, string $name, ?BodySchema $body): array
    {
        $baseUrlVar = '{{base_url}}';
        
        // Convert Laravel {param} to Postman :param, avoiding {{postman_vars}}
        $uriWithVars = preg_replace('/(?<!\{)\{(\w+)\}(?!\})/', ':$1', $route->route->uri);
        
        $path = explode('/', trim($uriWithVars, '/'));
        $rawUrl = $baseUrlVar . '/' . ltrim($uriWithVars, '/');
        
        $variables = [];
        foreach ($route->route->parameterNames as $param) {
            $variables[] = ['key' => $param, 'value' => ''];
        }

        $request = [
            'header' => [
                ['key' => 'Accept', 'value' => 'application/json', 'type' => 'text'],
                ['key' => 'Content-Type', 'value' => 'application/json', 'type' => 'text'],
            ],
            'url' => [
                'raw' => $rawUrl,
                'host' => [$baseUrlVar],
                'path' => $path,
                'variable' => $variables,
            ],
            'method' => $route->route->method,
        ];

        if ($route->authType === 'bearer') {
            $request['auth'] = [
                'type' => 'bearer',
                'bearer' => [
                    ['key' => 'token', 'value' => '{{token}}', 'type' => 'string'],
                ],
            ];
        }

        if ($body && !$body->isEmpty) {
            $request['body'] = $this->buildBody($body);
        }

        return [
            'name' => $name,
            'description' => $route->description,
            'request' => $request,
            '_generator_id' => $route->id, // Internal use for response attachment matching
        ];
    }

    protected function buildBody(BodySchema $body): array
    {
        if ($body->mode === 'raw') {
            return [
                'mode' => 'raw',
                'raw' => json_encode($body->json, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR),
                'options' => [
                    'raw' => ['language' => 'json'],
                ],
            ];
        }

        if ($body->mode === 'formdata') {
            $fields = [];
            foreach ($body->formFields as $field) {
                $fields[] = [
                    'key' => $field->key,
                    'value' => $field->value,
                    'type' => $field->type,
                ];
            }
            return [
                'mode' => 'formdata',
                'formdata' => $fields,
            ];
        }

        return [];
    }
}
