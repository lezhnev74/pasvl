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
        if (count($args) !== 1) throw new RuleFailed("Sub-rule [%s] expects one argument", __METHOD__);
        $count = $args[0];
        if (count($this->value) !== $count) {
            throw new RuleFailed(
                sprintf("given array must have length [%s] but it has [%s]", $count, count($this->value))
            );
        }
    }

    public function keys(...$args): void
    {
        foreach ($args as $expectedKey) {
            if (!array_key_exists($expectedKey, $this->value)) {
                throw new RuleFailed(sprintf("array must have keys [%s]", implode(",", $args)));
            }
        }
    }

    public function min(int $min): void
    {
        if (count($this->value) < $min) {
            throw new RuleFailed(sprintf("array must have at least %d items", $min));
        }
    }

    public function max(int $max): void
    {
        if (count($this->value) > $max) {
            throw new RuleFailed(sprintf("array must have no more than %d items", $max));
        }
    }

    public function between(int $min, int $max): void
    {
        $this->min($min);
        $this->max($max);
    }
}