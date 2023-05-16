<?php
declare(strict_types=1);
namespace Matrix2305\RequestObjectMapper\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ArrayChildObjectMap
{
    public function __construct(
        public string $objectClass
    ) {}
}