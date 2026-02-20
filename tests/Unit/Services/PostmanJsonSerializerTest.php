<?php

namespace LaravelGenerators\PostmanGenerator\Tests\Unit\Services;

use LaravelGenerators\PostmanGenerator\Services\PostmanJsonSerializer;
use RuntimeException;

it('serializes collection to json correctly', function () {
    $serializer = new PostmanJsonSerializer();
    $data = ['name' => 'Test'];
    
    $json = $serializer->serialize($data);
    
    expect($json)->toContain('"name": "Test"');
});

it('throws runtime exception on invalid utf8', function () {
    $serializer = new PostmanJsonSerializer();
    
    // Invalid UTF-8 sequence
    $data = ["name" => "\xB1\x31"];
    
    expect(fn() => $serializer->serialize($data))->toThrow(RuntimeException::class, 'Failed to serialize Postman collection');
});
