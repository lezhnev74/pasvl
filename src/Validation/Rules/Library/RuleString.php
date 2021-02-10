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

    public function json(): void
    {
        // must have ext-json enabled
        json_decode($this->value);
        if (json_last_error()) {
            throw new RuleFailed(sprintf("the string is not valid JSON: [%s]", $this->value));
        }
    }

    public function email(): void
    {
        if (filter_var($this->value, FILTER_VALIDATE_EMAIL) === false) {
            throw new RuleFailed(sprintf("the string is not valid email: [%s]", $this->value));
        }
    }

    public function uuid(): void
    {
        if (!preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $this->value)) {
            throw new RuleFailed(sprintf("the string is not valid uuid: [%s]", $this->value));
        }
    }

    public function contains(string $substring): void
    {
        if (mb_strstr($this->value, $substring) === false) {
            throw new RuleFailed(sprintf("the substring [%s] not found in string [%s]", $substring, $this->value));
        }
    }

    public function starts(string $substring): void
    {
        if (mb_substr($this->value, 0, strlen($substring)) !== $substring) {
            throw new RuleFailed(sprintf("string [%s] does not start with [%s]", $this->value, $substring));
        }
    }

    public function in(...$args): void
    {
        if (!in_array($this->value, $args)) {
            throw new RuleFailed(sprintf("string [%s] must be one of [%s]", $this->value, implode(',', $args)));
        }
    }

    public function ends(string $substring): void
    {
        if (mb_substr($this->value, -strlen($substring)) !== $substring) {
            throw new RuleFailed(sprintf("string [%s] does not end with [%s]", $this->value, $substring));
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
        error_clear_last();
        if (@preg_match($expr, '') === false) {
            $lastError = error_get_last() ? error_get_last()['message'] : '';
            throw new RuleFailed(sprintf("regexp is not a valid regular expression %s: %s", $expr, $lastError));
        }

        if (!preg_match($expr, $this->value)) {
            throw new RuleFailed(sprintf("string does not match regular expression %s", $expr));
        }
    }

    public function url(): void
    {
        if (filter_var($this->value, FILTER_VALIDATE_URL) === false) {
            throw new RuleFailed("string must be url");
        }
    }

    public function between(int $min, int $max): void
    {
        $this->min($min);
        $this->max($max);
    }
}
