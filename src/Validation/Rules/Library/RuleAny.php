<?php
declare(strict_types=1);


namespace PASVL\Validation\Rules\Library;


use PASVL\Validation\Rules\Rule;

// Matches any value
class RuleAny extends Rule
{
    public function test(...$args): void { return; }
}