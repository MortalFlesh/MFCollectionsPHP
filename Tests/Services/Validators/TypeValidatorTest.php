<?php

namespace MFCollections\Tests\Services\Validators;

use MFCollections\Collections\ListCollection;
use MFCollections\Collections\Enhanced\ListCollection as EnhancedListCollection;
use MFCollections\Collections\ListInterface;
use MFCollections\Collections\Map;
use MFCollections\Services\Validators\TypeValidator;

class TypeValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $keyType
     * @param string $valueType
     * @param array $allowedKeyTypes
     * @param array $allowedValueTypes
     *
     * @expectedException \InvalidArgumentException
     *
     * @dataProvider invalidCreationParamsProvider
     */
    public function testShouldThrowExceptionWhenBadTypeValidatorIsCreated($keyType, $valueType, array $allowedKeyTypes, array $allowedValueTypes)
    {
        new TypeValidator($keyType, $valueType, $allowedKeyTypes, $allowedValueTypes);
    }

    public function invalidCreationParamsProvider()
    {
        return [
            [
                'keyType' => 'string',
                'valueType' => 'int',
                'allowedKeyTypes' => [],
                'allowedValueTypes' => ['string'],
            ],
            [
                'keyType' => 'string',
                'valueType' => 'string',
                'allowedKeyTypes' => ['int'],
                'allowedValueTypes' => ['string', 'int'],
            ],
            [
                'keyType' => 'string',
                'valueType' => TypeValidator::TYPE_INSTANCE_OF,
                'allowedKeyTypes' => ['int'],
                'allowedValueTypes' => ['string', 'int', TypeValidator::TYPE_INSTANCE_OF],
            ],
            [
                'keyType' => 'string',
                'valueType' => TypeValidator::TYPE_INSTANCE_OF . 'badClass',
                'allowedKeyTypes' => ['int'],
                'allowedValueTypes' => ['string', 'int', TypeValidator::TYPE_INSTANCE_OF],
            ],
            [
                'keyType' => TypeValidator::TYPE_INSTANCE_OF . 'badClass',
                'valueType' => 'string',
                'allowedKeyTypes' => ['int', TypeValidator::TYPE_INSTANCE_OF],
                'allowedValueTypes' => ['string', 'int'],
            ],
        ];
    }

    /**
     * @param string $keyType
     * @param string $valueType
     * @param array $allowedKeyTypes
     * @param array $allowedValueTypes
     *
     * @dataProvider creationParamsProvider
     */
    public function testShouldCreateTypeValidator($keyType, $valueType, array $allowedKeyTypes, array $allowedValueTypes)
    {
        $typeValidator = new TypeValidator($keyType, $valueType, $allowedKeyTypes, $allowedValueTypes);
        
        $this->assertEquals($keyType, $typeValidator->getKeyType());
        $this->assertEquals($valueType, $typeValidator->getValueType());
    }

    public function creationParamsProvider()
    {
        return [
            [
                'keyType' => 'string',
                'valueType' => 'string',
                'allowedKeyTypes' => ['string'],
                'allowedValueTypes' => ['string', 'int'],
            ],
            [
                'keyType' => 'int',
                'valueType' => 'instance_of_' . Map::class,
                'allowedKeyTypes' => ['string', 'float', 'int'],
                'allowedValueTypes' => ['string', 'bool', 'instance_of_'],
            ],
        ];
    }

    /**
     * @param string $type
     * @param mixed $key
     * @param mixed $value
     *
     * @dataProvider validKeyValuesProvider
     */
    public function testShouldAssertKeyValueType($type, $key, $value)
    {
        $validator = $this->createValidator($type);

        $validator->assertKeyType($key);
        $validator->assertValueType($value);
    }

    /**
     * @param string $type
     * @return TypeValidator
     */
    private function createValidator($type)
    {
        return new TypeValidator($type, $type, [$type], [$type]);
    }

    public function validKeyValuesProvider()
    {
        return [
            [
                'type' => TypeValidator::TYPE_STRING,
                'key' => 'string',
                'value' => 'string',
            ],
            [
                'type' => TypeValidator::TYPE_INT,
                'key' => 1,
                'value' => 2,
            ],
            [
                'type' => TypeValidator::TYPE_FLOAT,
                'key' => 1.2,
                'value' => 2.3,
            ],
            [
                'type' => TypeValidator::TYPE_BOOL,
                'key' => true,
                'value' => false,
            ],
            [
                'type' => TypeValidator::TYPE_ARRAY,
                'key' => [],
                'value' => [1,2,3],
            ],
            [
                'type' => TypeValidator::TYPE_OBJECT,
                'key' => new \stdClass(),
                'value' => new \stdClass(),
            ],
            [
                'type' => TypeValidator::TYPE_INSTANCE_OF . Map::class,
                'key' => new Map(),
                'value' => new Map(),
            ],
            [
                'type' => TypeValidator::TYPE_INSTANCE_OF . ListInterface::class,
                'key' => new ListCollection(),
                'value' => new EnhancedListCollection(),
            ],
        ];
    }

    /**
     * @param string $type
     * @param mixed $key
     * 
     * @expectedException \InvalidArgumentException
     *
     * @dataProvider invalidTypesProvider
     */
    public function testShouldThrowInvalidArgumentExceptionWhenAssertingInvalidKeyTypes($type, $key)
    {
        $validator = $this->createValidator($type);
        $validator->assertKeyType($key);
    }

    public function invalidTypesProvider()
    {
        return [
            'string|int' => [
                'type' => TypeValidator::TYPE_STRING,
                'invalid' => 1,
            ],
            'string|null' => [
                'type' => TypeValidator::TYPE_STRING,
                'invalid' => null,
            ],
            'int|string' => [
                'type' => TypeValidator::TYPE_INT,
                'invalid' => '',
            ],
            'int|bool' => [
                'type' => TypeValidator::TYPE_INT,
                'invalid' => true,
            ],
            'float|int' => [
                'type' => TypeValidator::TYPE_FLOAT,
                'invalid' => 2,
            ],
            'float|bool' => [
                'type' => TypeValidator::TYPE_FLOAT,
                'invalid' => true,
            ],
            'bool|int' => [
                'type' => TypeValidator::TYPE_BOOL,
                'invalid' => 1,
            ],
            'array|null' => [
                'type' => TypeValidator::TYPE_ARRAY,
                'invalid' => null,
            ],
            'array|string' => [
                'type' => TypeValidator::TYPE_ARRAY,
                'invalid' => '',
            ],
            'object|array' => [
                'type' => TypeValidator::TYPE_OBJECT,
                'invalid' => [],
            ],
            'instance_of_map|instance_of_list' => [
                'type' => TypeValidator::TYPE_INSTANCE_OF . Map::class,
                'invalid' => new ListCollection(),
            ],
        ];
    }

    /**
     * @param string $type
     * @param mixed $value
     *
     * @expectedException \InvalidArgumentException
     *
     * @dataProvider invalidTypesProvider
     */
    public function testShouldThrowInvalidArgumentExceptionWhenAssertingInvalidValueTypes($type, $value)
    {
        $validator = $this->createValidator($type);
        $validator->assertValueType($value);
    }
}
