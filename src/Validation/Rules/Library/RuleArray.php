<?php

declare(strict_types=1);

namespace PASVL\Validation\Rules\Library;

use PASVL\Validation\Rules\Problems\RuleFailed;
use PASVL\Validation\Rules\Rule;

class RuleArray extends Rule
{
    public function test(...$args): void
    {
        if (!is_array($this->value)) throw new RuleFailed("the value is not an array");
    }

    public function count(...$args): void
    {
        if (!count($args)) throw new RuleFailed("Subrule [%s] expects one argument", __METHOD__);
        $count = $args[0];
        if (count($this->value) !== $count) {
            throw new RuleFailed(
                sprintf("given array must have length [%s] but it has [%s]", $count, count($this->value))
            );
        }
    }
}