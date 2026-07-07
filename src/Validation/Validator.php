<?php

declare(strict_types=1);

namespace PASVL\Validation;

use PASVL\Validation\Rules\RuleLocator;

abstract class Validator
{
    public function __construct(private \PASVL\Validation\Rules\RuleLocator $locator)
    {
    }

    /** @throws \InvalidArgumentException */
    abstract public function validate($data): void;

    protected function locator(): RuleLocator
    {
        return $this->locator;
    }
}
