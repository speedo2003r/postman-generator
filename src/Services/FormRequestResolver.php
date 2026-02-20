<?php

namespace LaravelGenerators\PostmanGenerator\Services;

use Illuminate\Foundation\Http\FormRequest;
use ReflectionClass;
use ReflectionMethod;

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

            if (!$type || $type->isBuiltin()) {
                continue;
            }

            $className = $type->getName();

            if (is_subclass_of($className, FormRequest::class)) {
                return $className;
            }
        }

        return null;
    }
}
