<?php
declare(strict_types=1);
namespace Matrix2305\RequestObjectMapper\Enums;

enum ArrayChildType : string
{
    case STRING = 'string';
    case INTEGER = 'integer';
    case FLOAT = 'float';

    case BOOLEAN = 'boolean';
}