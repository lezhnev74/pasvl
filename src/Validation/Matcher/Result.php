<?php
declare(strict_types=1);

namespace PASVL\Validation\Matcher;

use PASVL\Validation\Rules\Problems\RuleFailed;

/**
 * After evaluation of the operand and applying operators,
 * this represents the final result of the interpretation
 */
class Result
{
    /** @var boolean */
    private $value;
    /** @var RuleFailed */
    private $causeException;

    public function __construct(bool $value, ?RuleFailed $causeException)
    {
        $this->value = $value;
        $this->causeException = $causeException;
    }


    public static function passed(): self { return new self(true, null); }

    public static function failed(RuleFailed $reason): self { return new self(false, $reason); }

    public function isPassed(): bool { return $this->value === true; }

    public function reason(): RuleFailed { return $this->causeException; }
}