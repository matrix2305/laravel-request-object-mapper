<?php
declare(strict_types=1);
namespace Matrix2305\RequestObjectMapper;

use Matrix2305\RequestObjectMapper\Attributes\ArrayChildObjectMap;
use Matrix2305\RequestObjectMapper\Attributes\ArrayChildTypeMap;
use ReflectionProperty;
use ReflectionClass;
use RuntimeException;

abstract class BaseRequestObjectMapper
{
    use RequestValidator;

    public function __construct(array|null $customData = null)
    {
        $requestBody = $customData ?? request()->all();

        $this->validateRequest($requestBody);

        $class = new ReflectionClass(static::class);
        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty){
            $this->mapReflectionProperty($reflectionProperty, $requestBody);
        }
    }

    private function mapReflectionProperty(ReflectionProperty $property, array $requestBody) : void
    {

        $type = $property->getType()?->getName();
        $propertyName = $property->getName();

        if (!array_key_exists($propertyName, $requestBody)) {
            throw new RuntimeException("$propertyName does not exists in request body!");
        }

        $value = $requestBody[$propertyName];

        $nullable = !!$property->getType()?->allowsNull();
        if (!$nullable && (is_null($value) || $value === "")) {
            throw new RuntimeException('Property not allows null value.');
        }

        if ($type === 'string') {
            $this->mapStringProperty($propertyName, $value);
        }

        if ($type === 'bool' || $type === 'boolean') {
            $this->mapBooleanProperty($propertyName, $value);
        }

        if ($type === 'int' || $type === 'integer') {
            $this->mapIntegerProperty($propertyName, $value);
        }

        if ($type === 'float') {
            $this->mapFloatProperty($propertyName, $value);
        }

        if ($type === 'array') {
            $this->mapArrayProperty($property, $value);
        }
    }

    private function mapStringProperty(string $property, $value) : void
    {
        $this->{$property} = empty($value) ? null : (string)$value;
    }

    private function mapBooleanProperty(string $property, $value) : void
    {
        $result = false;
        if ($value === 'true') {
            $result = true;
        }

        if ($value === 'false') {
            $result = false;
        }

        if (is_bool($value)) {
            $result = $value;
        }

        $this->{$property} = $result;
    }

    private function mapIntegerProperty(string $property, $value) : void
    {
        $this->{$property} = (int)$value;
    }

    private function mapFloatProperty(string $property, $value) : void
    {
        $this->{$property} = (float)$value;
    }

    private function mapArrayProperty(ReflectionProperty $property, array $value) : void
    {
        if ($this->hasArrayStringKeys($value)) {
            $this->mapAssociativeArray($property, $value);
        } else {
            $this->mapNumericArray($property, $value);
        }
    }

    private function mapAssociativeArray(ReflectionProperty $property, array $value) : void
    {
        $propertyName = $property->getName();
        $attributes = $property->getAttributes();
        $objectClass = false;
        foreach ($attributes as $attribute) {
            if ($attribute->getName() === ArrayChildObjectMap::class) {
                $objectClass = $attribute->getArguments()['objectClass'] ?? false;
            }
        }

        if (!$objectClass) {
            throw new RuntimeException("$propertyName must implement ArrayChildObjectMap attribute!");
        }

        if (!class_exists($objectClass)) {
            throw new RuntimeException("$objectClass is not valid class!");
        }

        foreach ($value as $objectData) {
            $object = new $objectClass($objectData);
            if (!($object instanceof BaseRequestObjectMapper)) {
                throw new RuntimeException("$objectClass is not extends BaseRequestMapper");
            }
            $this->{$propertyName}[] = $object;
        }
    }

    private function mapNumericArray(ReflectionProperty $property, array $values) : void
    {
        $propertyName = $property->getName();
        $attributes = $property->getAttributes();
        $arrayItemType = false;
        foreach ($attributes as $attribute) {
            if ($attribute->getName() === ArrayChildTypeMap::class) {
                $arrayItemType = $attribute->getArguments()['type'];
            }
        }

        if (!$arrayItemType) {
            throw new RuntimeException("$propertyName must implement ArrayChildTypeMap attribute!");
        }

        foreach ($values as $value) {
            if ($arrayItemType === 'string') {
                $this->{$propertyName}[] = (string)$value;
            }
            if ($arrayItemType === 'float') {
                $this->{$propertyName}[] = (float)$value;
            }
            if ($arrayItemType === 'integer') {
                $this->{$propertyName}[] = (int)$value;
            }
            if ($arrayItemType === 'boolean') {
                $this->{$propertyName}[] = (bool)$value;
            }
        }


    }

    private function hasArrayStringKeys(array $array) : bool
    {
        return array_values($array) === $array;
    }
}