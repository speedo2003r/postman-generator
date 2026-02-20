<?php

namespace LaravelGenerators\PostmanGenerator;

use Illuminate\Support\ServiceProvider;
use LaravelGenerators\PostmanGenerator\Commands\GenerateCommand;
use LaravelGenerators\PostmanGenerator\Contracts\BodyExampleGenerator as BodyExampleGeneratorContract;
use LaravelGenerators\PostmanGenerator\Contracts\CollectionBuilder as CollectionBuilderContract;
use LaravelGenerators\PostmanGenerator\Contracts\MetadataExtractor as MetadataExtractorContract;
use LaravelGenerators\PostmanGenerator\Contracts\RouteScanner as RouteScannerContract;
use LaravelGenerators\PostmanGenerator\Services\BodyExampleGenerator;
use LaravelGenerators\PostmanGenerator\Services\CollectionBuilder;
use LaravelGenerators\PostmanGenerator\Services\MetadataExtractor;
use LaravelGenerators\PostmanGenerator\Services\RouteScanner;

class PostmanGeneratorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/postman-generator.php', 'postman-generator');

        $this->app->singleton(RouteScannerContract::class, RouteScanner::class);
        $this->app->singleton(MetadataExtractorContract::class, MetadataExtractor::class);
        $this->app->singleton(BodyExampleGeneratorContract::class, BodyExampleGenerator::class);
        $this->app->singleton(CollectionBuilderContract::class, CollectionBuilder::class);
        
        $this->app->singleton(Services\AuthDetector::class);
        $this->app->singleton(Services\FormRequestResolver::class);
        $this->app->singleton(Services\PostmanJsonSerializer::class);
        $this->app->singleton(Services\ResponseExampleGenerator::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/postman-generator.php' => config_path('postman-generator.php'),
            ], 'postman-generator-config');

            $this->commands([
                GenerateCommand::class,
            ]);
        }
    }
}
