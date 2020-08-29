<?php
declare(strict_types=1);


namespace PASVL\Validation\Rules\Library;


use PASVL\Validation\Rules\Problems\RuleFailed;
use PASVL\Validation\Rules\Rule;

class RuleNumber extends Rule
{
    public function test(...$args): void
    {
        if (!is_numeric($this->value)) throw new RuleFailed("the value is not a number");

        // optional exact match
        if (count($args)) {
            $exact = $args[0];
            if ($exact !== $this->value) {
                throw new RuleFailed(sprintf("number does not match the exact value: %s", $this->value));
            }
        }
    }

    public function max(int $x): void
    {
        if ($this->value > $x) throw new RuleFailed(sprintf("the number is greater than %d", $x));
    }

    public function int(): void
    {
        if (!is_int($this->value)) throw new RuleFailed("the number must be integer");
    }

    public function float(): void
    {
        if (!is_float($this->value)) throw new RuleFailed("the number must be float");
    }

    public function min(int $x): void
    {
        if ($this->value < $x) throw new RuleFailed(sprintf("the number is less than %d", $x));
    }

    public function between(int $min, int $max): void
    {
        $this->min($min);
        $this->max($max);
    }
}