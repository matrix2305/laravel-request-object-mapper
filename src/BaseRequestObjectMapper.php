<?php
declare(strict_types=1);
namespace Matrix2305\RequestObjectMapper;

use Matrix2305\RequestObjectMapper\Attributes\ArrayChildObjectMap;
use Matrix2305\RequestObjectMapper\Attributes\ArrayChildTypeMap;
use Matrix2305\RequestObjectMapper\Enums\ArrayChildType;
use ReflectionProperty;
use ReflectionClass;
use RuntimeException;

abstract class BaseRequestObjectMapper
{
    use RequestValidator;
    private ?string $propertyPrefix = null;

    public function __construct(array|null $customData = null, ?string $propertyPrefix = null)
    {
        $requestBody = $customData ?? request()->all();
        $this->propertyPrefix = $propertyPrefix;

        $this->validateRequest($requestBody, $propertyPrefix);

        $class = new ReflectionClass(static::class);
        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty){
            $this->mapReflectionProperty($reflectionProperty, $requestBody);
        }
    }

    private function mapReflectionProperty(ReflectionProperty $property, array $requestBody) : void
    {

        $type = $property->getType()?->getName();
        $propertyName = $property->getName();

        $nullable = !!$property->getType()?->allowsNull();

        $value = $requestBody[$propertyName] ?? null;


        if ($nullable && is_null($value)) {
            $this->{$propertyName} = null;
            return;
        }

        if (!$nullable && (is_null($value) || $value === "")) {
            throw new RuntimeException("Property $propertyName not allows append null value.");
        }

        if ($type === 'string') {
            $this->mapStringProperty($propertyName, $value);
        } elseif ($type === 'bool' || $type === 'boolean') {
            $this->mapBooleanProperty($propertyName, $value);
        } elseif ($type === 'int' || $type === 'integer') {
            $this->mapIntegerProperty($propertyName, $value);
        } elseif ($type === 'float') {
            $this->mapFloatProperty($propertyName, $value);
        } elseif ($type === 'array') {
            $this->mapArrayProperty($property, $value);
        } else {
            $this->mapObjectProperty($propertyName, $type, $value);
        }
    }

    private function mapObjectProperty(string $property, string $class, array $value) : void
    {
        $object = new $class($value, $this->propertyPrefix ? $this->propertyPrefix.'.'.$property : $property);
        if (!($object instanceof BaseRequestObjectMapper)) {
            throw new RuntimeException("$class is not extends BaseRequestMapper");
        }
        $this->{$property} = $object;
    }

    private function mapStringProperty(string $property, $value) : void
    {
        $this->{$property} = is_null($value) ? null : (string)$value;
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
        $this->{$property} = is_null($value) ? null : (int)$value;
    }

    private function mapFloatProperty(string $property, $value) : void
    {
        $this->{$property} = is_null($value) ? $value : (float)$value;
    }

    private function mapArrayProperty(ReflectionProperty $property, array $value) : void
    {
        $this->{$property->getName()} = [];

        if (count($value) > 0) {
            if ($this->hasStringKeys($value)) {
                $this->mapAssociativeArray($property, $value);
            } else {
                $this->mapNumericArray($property, $value);
            }
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

        $this->{$propertyName} = [];

        foreach ($value as $objectData) {
            $object = new $objectClass($objectData, $this->propertyPrefix ? $this->propertyPrefix.'.'.$propertyName.'.*' : $propertyName.'.*');
            if (!($object instanceof BaseRequestObjectMapper)) {
                throw new RuntimeException("$objectClass is not extends BaseRequestMapper");
            }
            $this->{$propertyName}[] = $object;
        }
    }

    private function mapAssociativeArrayWithReturn(array $value, string $objectClass) : object
    {
        $object = new $objectClass($value);
        if (!($object instanceof BaseRequestObjectMapper)) {
            throw new RuntimeException("$objectClass is not extends BaseRequestMapper");
        }

        return $object;
    }

    private function mapNumericArray(ReflectionProperty $property, array $values) : void
    {
        $propertyName = $property->getName();
        $attributes = $property->getAttributes();
        /** @var ArrayChildType $arrayItemType */
        $arrayItemType = false;
        foreach ($attributes as $attribute) {
            if ($attribute->getName() === ArrayChildTypeMap::class) {
                if (isset($attribute->getArguments()['type'])) {
                    $arrayItemType = $attribute->getArguments()['type'];
                }

                if (isset($attribute->getArguments()[0])) {
                    $arrayItemType = $attribute->getArguments()[0];
                }
            }
        }

        if (!$arrayItemType) {
            throw new RuntimeException("$propertyName must implement ArrayChildTypeMap attribute!");
        }

        $this->{$propertyName} = [];

        foreach ($values as $value) {
            if ($arrayItemType instanceof ArrayChildType) {
                if ($arrayItemType->value === 'string') {
                    $this->{$propertyName}[] = (string)$value;
                }
                if ($arrayItemType->value === 'float') {
                    $this->{$propertyName}[] = (float)$value;
                }
                if ($arrayItemType->value === 'integer') {
                    $this->{$propertyName}[] = (int)$value;
                }
                if ($arrayItemType->value === 'boolean') {
                    $this->{$propertyName}[] = (bool)$value;
                }

                continue;
            }

            if (class_exists($arrayItemType) && is_array($value)) {
                $this->{$propertyName}[] = $this->mapAssociativeArrayWithReturn($value, $arrayItemType);
            }
        }
    }

    private function hasStringKeys(array $array): bool
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }
}