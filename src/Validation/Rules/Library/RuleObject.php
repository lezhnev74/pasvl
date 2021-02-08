<?php

declare(strict_types=1);

namespace PASVL\Validation\Rules\Library;

use PASVL\Validation\Rules\Problems\RuleFailed;
use PASVL\Validation\Rules\Rule;

class RuleObject extends Rule
{
    public function test(...$args): void
    {
        if (!is_object($this->value)) throw new RuleFailed("the value is not an object");
    }

    public function instance(...$args): void
    {
        if (!count($args)) throw new RuleFailed("Sub-rule [%s] expects one argument", __METHOD__);
        $fqcn = $args[0];
        if (!$this->value instanceof $fqcn) {
            throw new RuleFailed(
                sprintf("Object of type [%s] is not an instance of [%s]", gettype($this->value), $fqcn)
            );
        }
    }

    public function propertyExists(string $propertyName): void
    {
        if (!property_exists($this->value, $propertyName)) {
            throw new RuleFailed(sprintf("object must have property %s", $propertyName));
        }
    }

    public function methodExists(string $propertyName): void
    {
        if (!method_exists($this->value, $propertyName)) {
            throw new RuleFailed(sprintf("object must have property %s", $propertyName));
        }
    }
}