<?php
declare(strict_types=1);
namespace Matrix2305\RequestObjectMapper\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class PropertyValidationRules
{
    public function __construct(
        public array|string $rules,
        public array $messages = []
    ) {}
}