<?php

namespace LaravelGenerators\PostmanGenerator\Tests;

use LaravelGenerators\PostmanGenerator\PostmanGeneratorServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            PostmanGeneratorServiceProvider::class,
        ];
    }
}
