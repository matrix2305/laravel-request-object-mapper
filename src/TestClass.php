<?php
declare(strict_types=1);
namespace Matrix2305\RequestObjectMapper;

use Matrix2305\RequestObjectMapper\Attributes\PropertyValidationRules;

class TestClass extends BaseRequestObjectMapper
{
    #[PropertyValidationRules(rules: 'required|integer', messages: ['required' => 'Id is required field.'])]
    public int $id;

    #[PropertyValidationRules(rules: 'required|string|max:255', messages: ['required' => 'Name is required field.', 'max' => 'Name can contains maximum 255 characters.'])]
    public string $name;
}