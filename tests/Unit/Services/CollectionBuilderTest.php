<?php

use LaravelGenerators\PostmanGenerator\DataObjects\EnrichedRouteData;
use LaravelGenerators\PostmanGenerator\DataObjects\RouteData;
use LaravelGenerators\PostmanGenerator\Services\CollectionBuilder;

beforeEach(function () {
    $this->builder = new CollectionBuilder();
});

it('builds valid postman structure with variables', function () {
    config(['postman-generator.collection_name' => 'Test API']);
    
    $route = new EnrichedRouteData(
        new RouteData('GET', 'api/users', 'UserController', 'index', ['api'], 'users.index', []),
        'List Users', 'Desc', 'Users', 'noauth', null
    );

    $collection = $this->builder->build([$route], []);
    
    expect($collection['info']['name'])->toBe('Test API')
        ->and($collection['item'])->toHaveCount(1)
        ->and($collection['item'][0]['name'])->toBe('Users')
        ->and($collection['item'][0]['item'][0]['name'])->toBe('List Users')
        ->and($collection['variable'])->toHaveCount(2)
        ->and($collection['variable'][0]['key'])->toBe('base_url');
});

it('handles duplicate request names', function () {
    $route1 = new EnrichedRouteData(
        new RouteData('GET', 'api/users/1', 'UserController', 'show', ['api'], null, []),
        'Get User', '', 'Users', 'noauth', null
    );
    $route2 = new EnrichedRouteData(
        new RouteData('POST', 'api/users/find', 'UserController', 'find', ['api'], null, []),
        'Get User', '', 'Users', 'noauth', null
    );

    $collection = $this->builder->build([$route1, $route2], []);
    
    $items = $collection['item'][0]['item'];
    expect($items[0]['name'])->toBe('Get User')
        ->and($items[1]['name'])->toBe('Get User (2)');
});

it('converts laravel route parameters to postman variables', function () {
    $route = new EnrichedRouteData(
        new RouteData('GET', 'api/orders/{order_id}', 'OrderController', 'show', ['api'], null, ['order_id']),
        'Get Order', '', 'Orders', 'noauth', null
    );

    $collection = $this->builder->build([$route], []);
    $request = $collection['item'][0]['item'][0]['request'];


    expect($request['url']['raw'])->toBe('{{base_url}}/api/orders/:order_id')
        ->and($request['url']['path'])->toContain(':order_id')
        ->and($request['url']['variable'])->toHaveCount(1)
        ->and($request['url']['variable'][0]['key'])->toBe('order_id');
});
