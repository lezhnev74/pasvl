<?php

declare(strict_types=1);

namespace PASVL\Validation\Rules;

use PASVL\Validation\Rules\Problems\RuleFailed;

abstract class Rule
{
    /** @var mixed */
    protected $value;

    public function putValue($value): void
    {
        $this->value = $value;
    }

    /** @throws RuleFailed */
    abstract public function test(...$args): void;
}