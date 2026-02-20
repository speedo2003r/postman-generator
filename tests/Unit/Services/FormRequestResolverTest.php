<?php

namespace LaravelGenerators\PostmanGenerator\Tests\Unit\Services;

use Illuminate\Foundation\Http\FormRequest;
use LaravelGenerators\PostmanGenerator\Services\FormRequestResolver;

class MockFormRequest extends FormRequest {}

class MockController
{
    public function withFormRequest(MockFormRequest $request) {}
    public function withStandardRequest(\Illuminate\Http\Request $request) {}
    public function withoutParams() {}
}

beforeEach(function () {
    $this->resolver = new FormRequestResolver();
});

it('resolves FQCN when FormRequest is used as parameter', function () {
    $result = $this->resolver->resolve(MockController::class, 'withFormRequest');
    
    expect($result)->toBe(MockFormRequest::class);
});

it('returns null when standard Request is used', function () {
    $result = $this->resolver->resolve(MockController::class, 'withStandardRequest');
    
    expect($result)->toBeNull();
});

it('returns null when no parameters are present', function () {
    $result = $this->resolver->resolve(MockController::class, 'withoutParams');
    
    expect($result)->toBeNull();
});
