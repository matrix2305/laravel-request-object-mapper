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
                    $arguments = $attribute->getArguments();

                    $propertyRules = [];
                    if (isset($arguments['rules'])) {
                        $propertyRules = $arguments['rules'];
                    }

                    if (isset($arguments[0])) {
                        $propertyRules = $arguments[0];
                    }

                    $propertyMessages = [];

                    if (isset($arguments['messages'])) {
                        $propertyMessages = $arguments['messages'];
                    }

                    if (isset($arguments[1])) {
                        $propertyMessages = $arguments[1];
                    }

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