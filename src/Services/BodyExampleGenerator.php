<?php

namespace LaravelGenerators\PostmanGenerator\Services;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use LaravelGenerators\PostmanGenerator\Contracts\BodyExampleGenerator as Contract;
use LaravelGenerators\PostmanGenerator\DataObjects\BodySchema;
use LaravelGenerators\PostmanGenerator\DataObjects\EnrichedRouteData;
use LaravelGenerators\PostmanGenerator\DataObjects\FormField;
use Throwable;

class BodyExampleGenerator implements Contract
{
    public function __construct(
        protected Application $app
    ) {
    }

    public function generate(EnrichedRouteData $route): BodySchema
    {
        if (!config('postman-generator.auto_examples', true)) {
            return BodySchema::empty();
        }

        if (in_array($route->route->method, ['GET', 'DELETE', 'HEAD', 'OPTIONS'])) {
            return new BodySchema(mode: 'none');
        }

        if (!$route->formRequestClass) {
            return BodySchema::empty();
        }

        try {
            $rules = $this->getRules($route->formRequestClass, $route->route->method);
            return $this->parseRules($rules);
        } catch (Throwable $e) {
            // FR-6.5/NFR-7: Log warning to console and continue
            // In a real Laravel command, this would be $this->warn(). 
            // Here we return an empty schema as per spec E1.
            return BodySchema::empty();
        }
    }

    protected function getRules(string $formRequestClass, string $method): array
    {
        /** @var FormRequest $instance */
        $instance = $this->app->build($formRequestClass);
        
        // Mock request state to avoid errors in rules() method if it checks method/input
        $instance->setMethod($method);
        $instance->replace([]);

        return $instance->rules();
    }

    protected function parseRules(array $rules): BodySchema
    {
        $data = [];
        $isMultipart = false;

        foreach ($rules as $field => $fieldRules) {
            $rulesArray = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
            
            if ($this->isFileType($rulesArray)) {
                $isMultipart = true;
            }

            $value = $this->exampleValueFromRules($rulesArray);
            
            // Handle dot notation and wildcards
            $this->normalizeAndSet($data, $field, $value);
        }

        if ($isMultipart) {
            $formFields = [];
            foreach (Arr::dot($data) as $key => $value) {
                // Map numeric indexes back to wildcards for rule lookup (e.g. items.0.photo -> items.*.photo)
                $ruleKey = preg_replace('/\.\d+/', '.*', $key);
                $type = $this->deriveBinaryType($rules[$ruleKey] ?? $rules[$key] ?? []);
                
                $formFields[] = new FormField($key, $type, $type === 'text' ? (string)$value : null);
            }
            return new BodySchema(mode: 'formdata', formFields: $formFields);
        }

        return new BodySchema(mode: 'raw', json: $data);
    }

    protected function normalizeAndSet(array &$data, string $key, mixed $value): void
    {
        // Replace * with 0 to indicate first element of an array
        $normalizedKey = str_replace('.*.', '.0.', $key);
        if (str_ends_with($normalizedKey, '.*')) {
            $normalizedKey = substr($normalizedKey, 0, -2) . '.0';
        }

        Arr::set($data, $normalizedKey, $value);
    }

    protected function exampleValueFromRules(array $rules): mixed
    {
        $rules = array_map('trim', $rules);

        if (in_array('numeric', $rules) || in_array('integer', $rules)) {
            return 1;
        }

        if (in_array('boolean', $rules)) {
            return true;
        }

        if (in_array('email', $rules)) {
            return 'user@example.com';
        }

        if (in_array('date', $rules) || Arr::first($rules, fn($r) => str_starts_with($r, 'date_format:'))) {
            return '2024-01-01';
        }

        if (in_array('uuid', $rules)) {
            return '00000000-0000-0000-0000-000000000000';
        }

        if (in_array('url', $rules)) {
            return 'https://example.com';
        }

        if (in_array('ip', $rules)) {
            return '127.0.0.1';
        }

        foreach ($rules as $rule) {
            if (str_starts_with($rule, 'in:')) {
                $options = explode(',', substr($rule, 3));
                return trim($options[0] ?? 'value');
            }
        }

        if (in_array('array', $rules)) {
            return [];
        }

        if (in_array('nullable', $rules) && count($rules) === 1) {
            return null;
        }

        return 'example_string';
    }

    protected function isFileType(array $rules): bool
    {
        foreach ($rules as $rule) {
            $rule = trim($rule);
            if (in_array($rule, ['file', 'image'])) {
                return true;
            }
            if (str_starts_with($rule, 'mimes:') || str_starts_with($rule, 'mimetypes:')) {
                return true;
            }
        }
        return false;
    }

    protected function deriveBinaryType(mixed $fieldRules): string
    {
        $rules = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
        return $this->isFileType($rules) ? 'file' : 'text';
    }
}
