<?php

declare(strict_types=1);

namespace PASVL\Validation;

use PASVL\Validation\Rules\RuleLocator;

abstract class Validator
{
    /** @var RuleLocator */
    private $locator;

    public function __construct(RuleLocator $locator) { $this->locator = $locator; }

    /** @throws \InvalidArgumentException */
    abstract public function validate($data): void;

    protected function locator(): RuleLocator { return $this->locator; }
}