<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 03/01/2018
 */

namespace PASVL\Traverser;


use PASVL\Traverser\VO\FailedReason;

class FailReport extends \Exception
{
    /** @var FailedReason */
    private $reason;
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
     * @param int $reason
     * @param mixed $mismatch_data_key
     * @param mixed $mismatch_data_value
     * @param mixed $mismatch_pattern
     * @param array $data_key_chain
     * @param array $pattern_key_chain
     */
    public function __construct(
        FailedReason $reason,
        $mismatch_data_key,
        $mismatch_data_value,
        $mismatch_pattern,
        array $data_key_chain,
        array $pattern_key_chain
    ) {
        $this->reason              = $reason;
        $this->mismatch_data_key   = $mismatch_data_key;
        $this->mismatch_data_value = $mismatch_data_value;
        $this->mismatch_pattern    = $mismatch_pattern;
        $this->data_key_chain      = $data_key_chain;
        $this->pattern_key_chain   = $pattern_key_chain;

        parent::__construct();
    }

    /**
     * @return FailedReason
     */
    public function getReason(): FailedReason
    {
        return $this->reason;
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