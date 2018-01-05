<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 03/01/2018
 */

namespace PASVL\Traverser;


class DataMismatchedPattern extends DataNoMatching
{
    /** @var mixed|null */
    protected $mismatched_pattern;

    public function __construct($mismatched_value, int $mismatched_type, $mismatched_pattern)
    {
        parent::__construct($mismatched_value, $mismatched_type);
        $this->mismatched_pattern = $mismatched_pattern;
    }


    /**
     * @return mixed
     */
    public function getMismatchedPattern()
    {
        return $this->mismatched_pattern;
    }


}