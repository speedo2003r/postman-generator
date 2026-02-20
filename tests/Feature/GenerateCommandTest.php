<?php

namespace LaravelGenerators\PostmanGenerator\Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use LaravelGenerators\PostmanGenerator\Tests\TestCase;

class GenerateCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        File::deleteDirectory(storage_path('postman'));
    }

    /** @test */
    public function it_generates_a_collection_file()
    {
        Route::get('api/ping', fn() => 'pong')->middleware('api');

        $this->artisan('postman:generate')
            ->expectsOutput('Scanning routes...')
            ->expectsOutput('Enriching route metadata and capturing examples...')
            ->expectsOutput('Building collection...')
            ->expectsOutput('Finalizing items...')
            ->assertExitCode(0);

        $path = storage_path('postman/collection.json');
        $this->assertFileExists($path);
        
        $content = json_decode(File::get($path), true);
        $this->assertEquals('Laravel API', $content['info']['name']);
        $this->assertCount(1, $content['item']);
    }

    /** @test */
    public function it_respects_custom_output_option()
    {
        Route::get('api/ping', fn() => 'pong')->middleware('api');
        $customPath = storage_path('custom/api.json');

        $this->artisan('postman:generate', ['--output' => $customPath])
            ->assertExitCode(0);

        $this->assertFileExists($customPath);
        File::deleteDirectory(storage_path('custom'));
    }

    /** @test */
    public function it_creates_non_existent_directories_recursively()
    {
        Route::get('api/ping', fn() => 'pong')->middleware('api');
        $deepPath = storage_path('deeply/nested/dir/postman.json');

        if (File::isDirectory(storage_path('deeply'))) {
            File::deleteDirectory(storage_path('deeply'));
        }

        $this->artisan('postman:generate', ['--output' => $deepPath])
            ->assertExitCode(0);

        $this->assertFileExists($deepPath);
        File::deleteDirectory(storage_path('deeply'));
    }

    /** @test */
    public function it_skips_response_generation_in_production_even_if_enabled()
    {
        config(['postman-generator.generate_responses' => true]);
        app()->detectEnvironment(fn() => 'production');
        
        \Illuminate\Support\Facades\Http::fake();

        Route::get('api/ping', fn() => 'pong')->middleware('api');

        $this->artisan('postman:generate')
            ->assertExitCode(0);

        \Illuminate\Support\Facades\Http::assertNothingSent();
    }
}
