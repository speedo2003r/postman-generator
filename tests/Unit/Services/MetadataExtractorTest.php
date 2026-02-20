<?php

namespace LaravelGenerators\PostmanGenerator\Tests\Unit\Services;

use LaravelGenerators\PostmanGenerator\Attributes\PostmanMeta;
use LaravelGenerators\PostmanGenerator\DataObjects\RouteData;
use LaravelGenerators\PostmanGenerator\Services\AuthDetector;
use LaravelGenerators\PostmanGenerator\Services\FormRequestResolver;
use LaravelGenerators\PostmanGenerator\Services\MetadataExtractor;
use Mockery;

class TestController
{
    #[PostmanMeta(name: 'Attribute Name', description: 'Attribute Description', folder: 'Attribute Folder')]
    public function withAttribute() {}

    /**
     * @postman-name DocBlock Name
     * @postman-description DocBlock Description
     * @postman-folder DocBlock Folder
     */
    public function withDocBlock() {}

    public function plainAction() {}
}

beforeEach(function () {
    $this->authDetector = Mockery::mock(AuthDetector::class);
    $this->resolver = Mockery::mock(FormRequestResolver::class);
    $this->extractor = new MetadataExtractor($this->authDetector, $this->resolver);
    
    $this->authDetector->shouldReceive('detect')->andReturn('noauth');
    $this->resolver->shouldReceive('resolve')->andReturn(null);
});

it('prioritizes attributes over docblocks and derivation', function () {
    $route = new RouteData('GET', 'api/test', TestController::class, 'withAttribute', ['api'], null, []);
    
    $enriched = $this->extractor->extract($route);
    
    expect($enriched->name)->toBe('Attribute Name')
        ->and($enriched->description)->toBe('Attribute Description')
        ->and($enriched->folder)->toBe('Attribute Folder');
});

it('falls back to docblocks when attribute is missing', function () {
    $route = new RouteData('GET', 'api/test', TestController::class, 'withDocBlock', ['api'], null, []);
    
    $enriched = $this->extractor->extract($route);
    
    expect($enriched->name)->toBe('DocBlock Name')
        ->and($enriched->description)->toBe('DocBlock Description')
        ->and($enriched->folder)->toBe('DocBlock Folder');
});

it('derives values when no metadata is present', function () {
    $route = new RouteData('GET', 'api/test-route', TestController::class, 'plainAction', ['api'], 'test.index', []);
    
    $enriched = $this->extractor->extract($route);
    
    expect($enriched->name)->toBe('Test Index')
        ->and($enriched->folder)->toBe('Test-Routes'); 
});

it('handles closure routes correctly', function () {
    $route = new RouteData('GET', 'api/ping', null, null, ['api'], null, []);
    
    $enriched = $this->extractor->extract($route);
    
    expect($enriched->name)->toBe('GET Ping')
        ->and($enriched->folder)->toBe('Pings'); 
});

it('defaults to Misc folder for closure routes with no segments after api', function () {
    $route = new RouteData('GET', 'api', null, null, ['api'], null, []);
    
    $enriched = $this->extractor->extract($route);
    
    expect($enriched->folder)->toBe('Misc');
});
