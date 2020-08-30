<?php

declare(strict_types=1);

namespace PASVL\Validation\Rules\Library;

use PASVL\Validation\Rules\Problems\RuleFailed;
use PASVL\Validation\Rules\Rule;

class RuleString extends Rule
{
    public function test(...$args): void
    {
        if (!is_string($this->value)) {
            throw new RuleFailed("the value is not a string");
        }

        // optional exact match
        if (count($args)) {
            $exact = $args[0];
            if ($exact !== $this->value) {
                throw new RuleFailed(sprintf("string does not match the exact value: %s", $this->value));
            }
        }
    }

    public function contains(string $substring): void
    {
        if (mb_strstr($this->value, $substring) === false) {
            throw new RuleFailed(sprintf("the substring [%s] not found in string [%s]", $substring, $this->value));
        }
    }

    public function len(int $len): void
    {
        if (mb_strlen($this->value) !== $len) {
            throw new RuleFailed(sprintf("string must be %d characters long", $len));
        }
    }

    public function max(int $len): void
    {
        if (mb_strlen($this->value) > $len) {
            throw new RuleFailed(sprintf("string is longer than %d characters", $len));
        }
    }

    public function min(int $len): void
    {
        if (mb_strlen($this->value) < $len) {
            throw new RuleFailed(sprintf("string is shorter than %d characters", $len));
        }
    }

    public function regexp(string $expr): void
    {
        if (!preg_match(sprintf("#%s#", $expr), $this->value)) {
            throw new RuleFailed(sprintf("string does not match regular expression %s", $expr));
        }
    }

    public function url(): void
    {
        if (filter_var($this->value, FILTER_VALIDATE_URL) === false) throw new RuleFailed("string must be url");
    }

    public function between(int $min, int $max): void
    {
        $this->min($min);
        $this->max($max);
    }
}