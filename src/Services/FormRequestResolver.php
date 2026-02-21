<?php

namespace LaravelGenerators\PostmanGenerator\Services;

use Illuminate\Foundation\Http\FormRequest;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;

class FormRequestResolver
{
    public function resolve(string $controllerClass, string $actionMethod): ?string
    {
        if (!class_exists($controllerClass) || !method_exists($controllerClass, $actionMethod)) {
            return null;
        }

        $reflection = new ReflectionMethod($controllerClass, $actionMethod);

        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (!$type) {
                continue;
            }

            // Handle union types (e.g., FooRequest|int|null)
            if ($type instanceof ReflectionUnionType) {
                foreach ($type->getTypes() as $unionType) {
                    if ($unionType instanceof ReflectionNamedType && !$unionType->isBuiltin()) {
                        $className = $unionType->getName();
                        if (is_subclass_of($className, FormRequest::class)) {
                            return $className;
                        }
                    }
                }
                continue;
            }

            // Handle named types
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $className = $type->getName();

                if (is_subclass_of($className, FormRequest::class)) {
                    return $className;
                }
            }
        }

        return null;
    }
}
