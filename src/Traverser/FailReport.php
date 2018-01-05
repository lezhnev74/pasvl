<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 03/01/2018
 */

namespace PASVL\Traverser;


class FailReport extends \Exception
{
    /** @var int 0 - key, 1 - value */
    private $failed_type;
    /** @var mixed */
    private $mismatch_data_key;
    /** @var mixed */
    private $mismatch_data_value;
    /** @var mixed */
    private $mismatch_pattern;
    /** @var array */
    private $data_key_chain = [];
    /** @var array */
    private $pattern_key_chain = [];

    /**
     * FailReport constructor.
     * @param int $failed_type
     * @param mixed $mismatch_data_key
     * @param mixed $mismatch_data_value
     * @param mixed $mismatch_pattern
     * @param array $data_key_chain
     * @param array $pattern_key_chain
     */
    public function __construct(
        int $failed_type,
        $mismatch_data_key,
        $mismatch_data_value,
        $mismatch_pattern,
        array $data_key_chain,
        array $pattern_key_chain
    ) {
        $this->failed_type = $failed_type;
        $this->mismatch_data_key = $mismatch_data_key;
        $this->mismatch_data_value = $mismatch_data_value;
        $this->mismatch_pattern = $mismatch_pattern;
        $this->data_key_chain = $data_key_chain;
        $this->pattern_key_chain = $pattern_key_chain;

        parent::__construct();
    }

    /**
     * @return int
     */
    public function isKeyFailed(): bool
    {
        return $this->failed_type == 0;
    }

    /**
     * @return int
     */
    public function isValueFailed(): bool
    {
        return $this->failed_type == 1;
    }

    /**
     * @return mixed
     */
    public function getMismatchDataKey()
    {
        return $this->mismatch_data_key;
    }

    /**
     * @return mixed
     */
    public function getMismatchDataValue()
    {
        return $this->mismatch_data_value;
    }

    /**
     * @return mixed
     */
    public function getMismatchPattern()
    {
        return $this->mismatch_pattern;
    }

    /**
     * @return array
     */
    public function getDataKeyChain(): array
    {
        return $this->data_key_chain;
    }

    /**
     * @return array
     */
    public function getPatternKeyChain(): array
    {
        return $this->pattern_key_chain;
    }

    public function getFailedPatternLevel(): int
    {
        return count($this->getPatternKeyChain());
    }

}