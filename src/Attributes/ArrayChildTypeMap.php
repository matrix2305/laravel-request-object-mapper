<?php
declare(strict_types=1);
namespace Matrix2305\RequestObjectMapper\Attributes;

use Attribute;
use Matrix2305\RequestObjectMapper\Enums\ArrayChildType;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ArrayChildTypeMap
{
    public function __construct(
        public ArrayChildType $type
    ) {}
}