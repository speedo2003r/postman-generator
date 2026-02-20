<?php

namespace LaravelGenerators\PostmanGenerator\Services;

class AuthDetector
{
    /**
     * @param string[] $middlewares
     */
    public function detect(array $middlewares): string
    {
        $authMiddlewares = config('postman-generator.auth_middlewares', [
            'auth:sanctum',
            'auth:api',
        ]);

        foreach ($middlewares as $middleware) {
            if (in_array($middleware, $authMiddlewares)) {
                return 'bearer';
            }
        }

        return 'noauth';
    }
}
