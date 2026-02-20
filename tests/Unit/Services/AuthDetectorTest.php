<?php

use LaravelGenerators\PostmanGenerator\Services\AuthDetector;

beforeEach(function () {
    $this->detector = new AuthDetector();
});

it('detects bearer auth for sanctum middleware', function () {
    config(['postman-generator.auth_middlewares' => ['auth:sanctum']]);
    
    expect($this->detector->detect(['auth:sanctum']))->toBe('bearer');
});

it('detects bearer auth for api middleware', function () {
    config(['postman-generator.auth_middlewares' => ['auth:api']]);
    
    expect($this->detector->detect(['auth:api']))->toBe('bearer');
});

it('detects noauth for public endpoints', function () {
    expect($this->detector->detect(['web', 'throttle']))->toBe('noauth');
});

it('detects bearer for custom auth middleware', function () {
    config(['postman-generator.auth_middlewares' => ['custom.auth']]);
    
    expect($this->detector->detect(['custom.auth']))->toBe('bearer');
});
