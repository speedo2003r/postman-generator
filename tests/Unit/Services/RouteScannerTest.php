<?php

use Illuminate\Support\Facades\Route;
use LaravelGenerators\PostmanGenerator\Services\RouteScanner;

beforeEach(function () {
    $this->scanner = new RouteScanner();
    Route::get('api/users', fn() => [])->middleware('api');
    Route::post('api/users', fn() => [])->middleware('api');
    Route::get('web/home', fn() => [])->middleware('web');
});

it('includes routes with target middleware group', function () {
    config(['postman-generator.route_groups' => 'api']);
    
    $routes = $this->scanner->scan();
    
    expect($routes)->toHaveCount(2)
        ->and($routes[0]->uri)->toBe('api/users')
        ->and($routes[1]->uri)->toBe('api/users');
});

it('excludes routes matched by pattern', function () {
    config([
        'postman-generator.route_groups' => 'api',
        'postman-generator.exclude_routes' => ['api/users*']
    ]);
    
    $routes = $this->scanner->scan();
    
    expect($routes)->toBeEmpty();
});

it('skips HEAD and OPTIONS methods', function () {
    Route::options('api/options', fn() => [])->middleware('api');
    
    $routes = $this->scanner->scan();
    
    foreach ($routes as $route) {
        expect($route->method)->not->toBe('OPTIONS');
    }
});
