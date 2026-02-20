<?php

namespace LaravelGenerators\PostmanGenerator\Services;

use JsonException;
use RuntimeException;

class PostmanJsonSerializer
{
    public function serialize(array $collection): string
    {
        try {
            return json_encode(
                $collection,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            throw new RuntimeException("Failed to serialize Postman collection: " . $e->getMessage(), 0, $e);
        }
    }
}
