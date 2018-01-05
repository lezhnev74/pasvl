<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 03/01/2018
 */

namespace PASVL\Traverser;


class DataNoMatching extends \InvalidArgumentException
{
    const MISMATCHED_KEY = 1;
    const MISMATCHED_VALUE = 2;

    /** @var mixed */
    protected $key;
    /** @var mixed */
    protected $value;
    /** @var int */
    protected $mismatched_type;

    /**
     * DataNoMatching constructor.
     * @param mixed $key
     * @param mixed $value
     * @param int $mismatched_type
     */
    public function __construct($key, $value, int $mismatched_type)
    {
        parent::__construct("Invalid data found");
        $this->key = $key;
        $this->value = $value;
        $this->mismatched_type = $mismatched_type;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getMismatchedType(): int
    {
        return $this->mismatched_type;
    }


    public function isKeyType(): bool
    {
        return $this->getMismatchedType() == self::MISMATCHED_KEY;
    }

    public function isValueType(): bool
    {
        return $this->getMismatchedType() == self::MISMATCHED_VALUE;
    }

}