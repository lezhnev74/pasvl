<?php

declare(strict_types=1);

namespace PASVL\Validation;

use PASVL\Validation\Rules\RuleLocator;

class ValidatorBuilder
{
    private const MODE_STRING = 0;
    private const MODE_ARRAY  = 1;

    private ?int $mode = null;
    private string|array|null $pattern = null;
    private RuleLocator $ruleLocator;

    private function __construct()
    {
        $this->ruleLocator = new RuleLocator();
    }

    public function withLocator(RuleLocator $locator): self
    {
        $this->ruleLocator = $locator;
        return $this;
    }

    public function build(): Validator
    {
        return match ($this->mode) {
            self::MODE_STRING => new StringValidator($this->ruleLocator, $this->pattern),
            self::MODE_ARRAY => new ArrayValidator($this->ruleLocator, $this->pattern),
            default => throw new \RuntimeException('Unsupported mode'),
        };
    }

    public static function forString(string $pattern): self
    {
        $i = new self();
        $i->pattern = $pattern;
        $i->mode = self::MODE_STRING;
        return $i;
    }

    public static function forArray(array $pattern): self
    {
        $i = new self();
        $i->pattern = $pattern;
        $i->mode = self::MODE_ARRAY;
        return $i;
    }
}
