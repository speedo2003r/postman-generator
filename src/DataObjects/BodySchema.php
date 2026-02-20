<?php

namespace LaravelGenerators\PostmanGenerator\DataObjects;

readonly class BodySchema
{
    /**
     * @param FormField[]|null $formFields
     */
    public function __construct(
        public string $mode,
        public ?array $json = null,
        public ?array $formFields = null,
        public bool $isEmpty = false,
    ) {
    }

    public static function empty(): self
    {
        return new self(mode: 'none', isEmpty: true);
    }
}

readonly class FormField
{
    public function __construct(
        public string $key,
        public string $type,
        public ?string $value = null,
    ) {
    }
}
