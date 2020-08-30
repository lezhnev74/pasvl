<?php

declare(strict_types=1);

namespace PASVL\Validation\Rules\Library;

use PASVL\Validation\Rules\Problems\RuleFailed;
use PASVL\Validation\Rules\Rule;

class RuleBool extends Rule
{
    public function test(...$args): void
    {
        if (!is_bool($this->value)) throw new RuleFailed("the value is not a boolean");

        // optional exact match
        if (count($args)) {
            $exact = $args[0];
            if ($exact !== $this->value) {
                throw new RuleFailed(sprintf("the boolean does not match the exact value: %s", $this->value));
            }
        }
    }
}