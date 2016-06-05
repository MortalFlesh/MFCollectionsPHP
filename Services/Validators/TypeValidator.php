<?php

namespace MFCollections\Services\Validators;

class TypeValidator
{
    const TYPE_STRING = 'string';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_BOOL = 'bool';
    const TYPE_ARRAY = 'array';
    const TYPE_OBJECT = 'object';
    const TYPE_INSTANCE_OF = 'instance_of_';

    private static $types = [
        self::TYPE_STRING,
        self::TYPE_INT,
        self::TYPE_FLOAT,
        self::TYPE_BOOL,
        self::TYPE_ARRAY,
        self::TYPE_OBJECT,
        self::TYPE_INSTANCE_OF,
    ];

    /** @var string */
    private $keyType;

    /** @var string */
    private $valueType;

    /**
     * @param string $keyType
     * @param string $valueType
     * @param array $allowedKeyTypes
     * @param array $allowedValueTypes
     */
    public function __construct($keyType, $valueType, array $allowedKeyTypes, array $allowedValueTypes)
    {
        $keyType = $this->normalizeType($keyType);
        $valueType = $this->normalizeType($valueType);

        $this->assertValidType($keyType, 'key', $allowedKeyTypes);
        $this->assertValidType($valueType, 'value', $allowedValueTypes);

        $this->keyType = $keyType;
        $this->valueType = $valueType;
    }

    /**
     * @param string $type
     * @return string
     */
    private function normalizeType($type)
    {
        if (!$this->isInstanceOfType($type) && !in_array($type, self::$types, true)) {
            return self::TYPE_INSTANCE_OF . $type;
        }

        return $type;
    }

    /**
     * @param string $type
     * @param string $typeTitle
     * @param array $allowedTypes
     */
    private function assertValidType($type, $typeTitle, array $allowedTypes)
    {
        if (in_array(self::TYPE_INSTANCE_OF, $allowedTypes, true) && $this->isInstanceOfType($type)) {
            $this->assertValidInstanceOf($type);
        } elseif (!in_array($type, $allowedTypes, true)) {
            throw new \InvalidArgumentException(sprintf('Not allowed %s type given', $typeTitle));
        }
    }

    /**
     * @param string $valueType
     * @return bool
     */
    private function isInstanceOfType($valueType)
    {
        return (substr($valueType, 0, strlen(self::TYPE_INSTANCE_OF)) === self::TYPE_INSTANCE_OF);
    }

    /**
     * @param string $valueType
     */
    private function assertValidInstanceOf($valueType)
    {
        $instanceOfLength = strlen(self::TYPE_INSTANCE_OF);

        if (strlen($valueType) <= $instanceOfLength) {
            throw new \InvalidArgumentException('Instance of has missing class to check');
        } else {
            $class = $this->parseClass($valueType);

            if (!(class_exists($class) || interface_exists($class))) {
                throw new \InvalidArgumentException(sprintf('Instance of has invalid class (%s)', $class));
            }
        }
    }

    /**
     * @param string $type
     * @return string
     */
    private function parseClass($type)
    {
        return substr($type, strlen(self::TYPE_INSTANCE_OF));
    }

    /**
     * @return string
     */
    public function getKeyType()
    {
        return $this->stripInstanceOfPrefix($this->keyType);
    }

    /**
     * @param string $type
     * @return string
     */
    private function stripInstanceOfPrefix($type)
    {
        return str_replace(self::TYPE_INSTANCE_OF, '', $type);
    }

    /**
     * @return string
     */
    public function getValueType()
    {
        return $this->stripInstanceOfPrefix($this->valueType);
    }

    /**
     * @param <K> $key
     */
    public function assertKeyType($key)
    {
        $this->assertType($key, $this->keyType, 'key');
    }

    /**
     * @param mixed $givenType
     * @param string $type
     * @param string $typeTitle
     */
    private function assertType($givenType, $type, $typeTitle)
    {
        if ($this->isInstanceOfType($type)) {
            $this->assertInstanceOf($typeTitle, $givenType);
        } elseif ($type === self::TYPE_STRING && !is_string($givenType)) {
            $this->invalidTypeError($typeTitle, self::TYPE_STRING, $givenType);
        } elseif ($type === self::TYPE_INT && !is_integer($givenType)) {
            $this->invalidTypeError($typeTitle, self::TYPE_INT, $givenType);
        } elseif ($type === self::TYPE_FLOAT && !is_float($givenType)) {
            $this->invalidTypeError($typeTitle, self::TYPE_FLOAT, $givenType);
        } elseif ($type === self::TYPE_BOOL && !($givenType === true || $givenType === false)) {
            $this->invalidTypeError($typeTitle, self::TYPE_BOOL, $givenType);
        } elseif ($type === self::TYPE_ARRAY && !is_array($givenType)) {
            $this->invalidTypeError($typeTitle, self::TYPE_ARRAY, $givenType);
        } elseif ($type === self::TYPE_OBJECT && !is_object($givenType)) {
            $this->invalidTypeError($typeTitle, self::TYPE_OBJECT, $givenType);
        }
    }

    /**
     * @param string $type
     * @param <V> $value
     */
    private function assertInstanceOf($type, $value)
    {
        $class = $this->parseClass($this->valueType);

        if (!$value instanceof $class) {
            $this->invalidTypeError($type, sprintf('instance of (%s)', $class), $type);
        }
    }

    /**
     * @param string $typeTitle
     * @param string $typeExpected
     * @param mixed $givenType
     */
    private function invalidTypeError($typeTitle, $typeExpected, $givenType)
    {
        throw new \InvalidArgumentException(
            sprintf(
                'Invalid %s type argument "%s"<%s> given - <%s> expected',
                $typeTitle,
                $this->getGivenTypeString($givenType),
                gettype($givenType),
                $typeExpected
            )
        );
    }

    /**
     * @param mixed $givenType
     * @return string
     */
    private function getGivenTypeString($givenType)
    {
        if (is_array($givenType)) {
            return 'Array';
        } elseif (is_object($givenType)) {
            return get_class($givenType);
        } elseif ($givenType === true) {
            return 'true';
        } elseif ($givenType === false) {
            return 'false';
        } else {
            return sprintf('%s', $givenType);
        }
    }

    /**
     * @param <V> $value
     */
    public function assertValueType($value)
    {
        $this->assertType($value, $this->valueType, 'value');
    }
}
