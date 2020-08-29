<?php
declare(strict_types=1);


namespace PASVL\Validation\Rules\Library;


use PASVL\Validation\Rules\Problems\RuleFailed;
use PASVL\Validation\Rules\Rule;

class RuleExact extends Rule
{
    public function test(...$args): void
    {
        if (!count($args)) throw new RuleFailed('Exact rule MUST have a value as an argument');

        $exact = $args[0];
        if ($exact !== $this->value) {
            throw new RuleFailed(sprintf("it does not match the exact value: %s", $this->value));
        }
    }
}