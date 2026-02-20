<?php

namespace LaravelGenerators\PostmanGenerator\Tests\Unit\Services;

use Illuminate\Foundation\Http\FormRequest;
use LaravelGenerators\PostmanGenerator\DataObjects\EnrichedRouteData;
use LaravelGenerators\PostmanGenerator\DataObjects\RouteData;
use LaravelGenerators\PostmanGenerator\Services\BodyExampleGenerator;

class FlatRulesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'age' => 'required|integer',
            'is_active' => 'boolean',
            'email' => 'email',
            'birthday' => 'date',
            'category' => 'in:electronics,books',
        ];
    }
}

class NestedRulesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user.profile.first_name' => 'required|string',
            'user.profile.last_name' => 'required|string',
            'user.settings.theme' => 'string',
        ];
    }
}

class WildcardRulesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.qty' => 'required|integer',
        ];
    }
}

class FileRulesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'avatar' => 'required|file|image',
            'username' => 'required|string',
        ];
    }
}

class FailingRulesRequest extends FormRequest
{
    public function rules(): array
    {
        throw new \Exception("Database error in rules");
    }
}

beforeEach(function () {
    $this->generator = new BodyExampleGenerator(app());
});

it('generates flat body examples correctly', function () {
    $route = new EnrichedRouteData(
        new RouteData('POST', 'api/users', null, null, ['api'], null, []),
        'Store User', '', 'Users', 'noauth', FlatRulesRequest::class
    );

    $schema = $this->generator->generate($route);

    expect($schema->mode)->toBe('raw')
        ->and($schema->json)->toBe([
            'name' => 'example_string',
            'age' => 1,
            'is_active' => true,
            'email' => 'user@example.com',
            'birthday' => '2024-01-01',
            'category' => 'electronics',
        ]);
});

it('generates nested body examples correctly', function () {
    $route = new EnrichedRouteData(
        new RouteData('POST', 'api/profile', null, null, ['api'], null, []),
        'Update Profile', '', 'Profile', 'noauth', NestedRulesRequest::class
    );

    $schema = $this->generator->generate($route);

    expect($schema->json)->toBe([
        'user' => [
            'profile' => [
                'first_name' => 'example_string',
                'last_name' => 'example_string',
            ],
            'settings' => [
                'theme' => 'example_string',
            ],
        ],
    ]);
});

it('generates wildcard body examples correctly', function () {
    $route = new EnrichedRouteData(
        new RouteData('POST', 'api/orders', null, null, ['api'], null, []),
        'Store Order', '', 'Orders', 'noauth', WildcardRulesRequest::class
    );

    $schema = $this->generator->generate($route);

    expect($schema->json)->toBe([
        'items' => [
            [
                'id' => 1,
                'qty' => 1,
            ],
        ],
    ]);
});

it('switches to formdata mode for files', function () {
    $route = new EnrichedRouteData(
        new RouteData('POST', 'api/avatar', null, null, ['api'], null, []),
        'Upload Avatar', '', 'Users', 'noauth', FileRulesRequest::class
    );

    $schema = $this->generator->generate($route);

    expect($schema->mode)->toBe('formdata')
        ->and($schema->formFields)->toHaveCount(2)
        ->and($schema->formFields[0]->key)->toBe('avatar')
        ->and($schema->formFields[0]->type)->toBe('file')
        ->and($schema->formFields[1]->key)->toBe('username')
        ->and($schema->formFields[1]->type)->toBe('text');
});

it('returns empty schema when rules() throws exception', function () {
    $route = new EnrichedRouteData(
        new RouteData('POST', 'api/fail', null, null, ['api'], null, []),
        'Fail', '', 'Debug', 'noauth', FailingRulesRequest::class
    );

    $schema = $this->generator->generate($route);

    expect($schema->isEmpty)->toBeTrue();
});

it('returns none mode for GET requests', function () {
    $route = new EnrichedRouteData(
        new RouteData('GET', 'api/users', null, null, ['api'], null, []),
        'List Users', '', 'Users', 'noauth', FlatRulesRequest::class
    );

    $schema = $this->generator->generate($route);

    expect($schema->mode)->toBe('none');
});
