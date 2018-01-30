<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 03/01/2018
 */

namespace PASVL\Traverser;


use PASVL\Traverser\VO\FailedReason;

class DataNoMatching extends \InvalidArgumentException
{
    /** @var mixed */
    protected $key;
    /** @var mixed */
    protected $value;
    /** @var FailedReason */
    protected $reason;

    /**
     * DataNoMatching constructor.
     * @param mixed $key
     * @param mixed $value
     * @param int $reason
     */
    public function __construct($key, $value, FailedReason $reason)
    {
        parent::__construct("Invalid data found");
        $this->key    = $key;
        $this->value  = $value;
        $this->reason = $reason;
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
     * @return FailedReason
     */
    public function getReason(): FailedReason
    {
        return $this->reason;
    }


}