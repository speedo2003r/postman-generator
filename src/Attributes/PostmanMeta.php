<?php

namespace LaravelGenerators\PostmanGenerator\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class PostmanMeta
{
    /**
     * @param string[] $tags
     */
    public function __construct(
        public string $name = '',
        public string $description = '',
        public string $folder = '',
        public array $tags = [],
    ) {
    }
}
