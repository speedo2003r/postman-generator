<?php

namespace LaravelGenerators\PostmanGenerator\Tests\Unit\Services;

use Illuminate\Support\Facades\Http;
use LaravelGenerators\PostmanGenerator\DataObjects\BodySchema;
use LaravelGenerators\PostmanGenerator\DataObjects\EnrichedRouteData;
use LaravelGenerators\PostmanGenerator\DataObjects\RouteData;
use LaravelGenerators\PostmanGenerator\Services\ResponseExampleGenerator;

beforeEach(function () {
    $this->generator = new ResponseExampleGenerator();
    config([
        'postman-generator.generate_responses' => true,
        'postman-generator.base_url' => 'http://localhost'
    ]);
});

it('attaches response when request is successful', function () {
    Http::fake([
        'http://localhost/api/ping' => Http::response(['success' => true], 200)
    ]);

    $route = new EnrichedRouteData(
        new RouteData('GET', 'api/ping', null, null, ['api'], null, []),
        'Ping', '', 'Misc', 'noauth', null
    );

    $item = ['request' => []];
    $this->generator->attach($item, $route, BodySchema::empty());

    expect($item)->toHaveKey('response')
        ->and($item['response'][0]['code'])->toBe(200);
});

it('skips when generate_responses is disabled', function () {
    config(['postman-generator.generate_responses' => false]);
    Http::fake();

    $item = ['request' => []];
    $route = new EnrichedRouteData(
        new RouteData('GET', 'api/ping', null, null, ['api'], null, []),
        'Ping', '', 'Misc', 'noauth', null
    );

    $this->generator->attach($item, $route, BodySchema::empty());

    expect($item)->not->toHaveKey('response');
});

it('skips when environment is production', function () {
    app()->detectEnvironment(fn() => 'production');
    // Ensure it's not local or testing
    Http::fake();

    $item = ['request' => []];
    $route = new EnrichedRouteData(
        new RouteData('GET', 'api/ping', null, null, ['api'], null, []),
        'Ping', '', 'Misc', 'noauth', null
    );

    $this->generator->attach($item, $route, BodySchema::empty());

    expect($item)->not->toHaveKey('response');
});
