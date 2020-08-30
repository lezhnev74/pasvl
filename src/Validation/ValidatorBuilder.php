<?php

declare(strict_types=1);

namespace PASVL\Validation;

use PASVL\Validation\Rules\RuleLocator;

class ValidatorBuilder
{
    private const MODE_STRING = 0;
    private const MODE_ARRAY  = 1;

    /** @var int */
    private $mode;
    /** @var string|array */
    private $pattern;
    private $ruleLocator;

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
        switch ($this->mode) {
            case self::MODE_STRING:
                return new StringValidator($this->ruleLocator, $this->pattern);
            case self::MODE_ARRAY:
                return new ArrayValidator($this->ruleLocator, $this->pattern);
            default:
                throw new \RuntimeException('Unsupported mode');
        }
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