<?php

namespace LaravelGenerators\PostmanGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use LaravelGenerators\PostmanGenerator\Contracts\BodyExampleGenerator;
use LaravelGenerators\PostmanGenerator\Contracts\CollectionBuilder;
use LaravelGenerators\PostmanGenerator\Contracts\MetadataExtractor;
use LaravelGenerators\PostmanGenerator\Contracts\RouteScanner;
use LaravelGenerators\PostmanGenerator\DataObjects\BodySchema;
use LaravelGenerators\PostmanGenerator\Services\PostmanJsonSerializer;
use LaravelGenerators\PostmanGenerator\Services\ResponseExampleGenerator;

class GenerateCommand extends Command
{
    protected $signature = 'postman:generate {--output= : Override the default output path}';
    protected $description = 'Generate a Postman Collection from Laravel API routes';

    public function __construct(
        protected RouteScanner $scanner,
        protected MetadataExtractor $extractor,
        protected BodyExampleGenerator $bodyGenerator,
        protected CollectionBuilder $builder,
        protected PostmanJsonSerializer $serializer,
        protected ResponseExampleGenerator $responseGenerator
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Scanning routes...');
        $routes = $this->scanner->scan();
        
        if (empty($routes)) {
            $this->warn('No API routes found matching your configuration.');
            return 0;
        }

        $this->info('Enriching route metadata and capturing examples...');
        $enrichedRoutes = [];
        $bodies = [];

        foreach ($routes as $route) {
            $enriched = $this->extractor->extract($route);
            $enrichedRoutes[] = $enriched;
            
            $bodies[$route->uri] = $this->bodyGenerator->generate($enriched);
        }

        $this->info('Building collection...');
        $collection = $this->builder->build($enrichedRoutes, $bodies);

        $this->info('Finalizing items...');
        
        $enrichedById = collect($enrichedRoutes)->keyBy('id');

        // Finalize by attaching responses
        foreach ($collection['item'] as &$folder) {
            if (!isset($folder['item']) || !is_array($folder['item'])) {
                continue;
            }
            foreach ($folder['item'] as &$item) {
                $routeId = $item['_generator_id'] ?? null;
                $route = $enrichedById->get($routeId);
                
                if ($route) {
                    $this->responseGenerator->attach($item, $route, $bodies[$route->route->uri] ?? BodySchema::empty());
                }

                unset($item['_generator_id']);
            }
        }

        $json = $this->serializer->serialize($collection);

        $outputPath = $this->option('output') ?: config('postman-generator.output_path');
        
        $directory = dirname($outputPath);
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put($outputPath, $json);

        $this->info("Postman collection successfully generated at: $outputPath");
        
        $this->printSummary($enrichedRoutes, $bodies);

        return 0;
    }

    protected function printSummary(array $routes, array $bodies): void
    {
        $total = count($routes);
        $withBody = count(array_filter($bodies, fn($b) => !$b->isEmpty && $b->mode !== 'none'));
        
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Routes Scanned', $total],
                ['Endpoints with Body Examples', $withBody],
                ['Endpoints without Body', $total - $withBody],
            ]
        );
    }
}
