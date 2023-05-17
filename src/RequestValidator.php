<?php
declare(strict_types=1);
namespace Matrix2305\RequestObjectMapper;

use Matrix2305\RequestObjectMapper\Attributes\PropertyValidationRules;
use ReflectionProperty;
use ReflectionClass;
use Illuminate\Support\Facades\Validator;

trait RequestValidator
{
    private function validateRequest(array $data) : void
    {
        $rules = [];
        $messages = [];

        $class = new ReflectionClass(static::class);
        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            $attributes = $reflectionProperty->getAttributes();
            foreach ($attributes as $attribute) {
                if ($attribute->getName() === PropertyValidationRules::class) {
                    $propertyRules = $attribute->getArguments()['rules'];
                    $propertyMessages = $attribute->getArguments()['messages'];

                    $rules[$reflectionProperty->getName()] = $propertyRules;
                    $parsedMessages = $this->parseValidationMessages($reflectionProperty->getName(), $propertyMessages);
                    $messages = array_merge_recursive($messages, $parsedMessages);
                }
            }
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new Exceptions\FailedValidationException($validator->getMessageBag());
        }
    }

    private function parseValidationMessages(string $propertyName, array $messages) : array
    {
        $parsedValidationMessages = [];
        foreach ($messages as $messageKey => $message) {
            if (str_contains($messageKey, $propertyName)) {
                $parsedValidationMessages[$messageKey] = $message;
            } else {
                $parsedValidationMessages[$propertyName.'.'.$messageKey] = $message;
            }
        }

        return $parsedValidationMessages;
    }
}