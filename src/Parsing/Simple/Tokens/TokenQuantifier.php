<?php

declare(strict_types=1);

namespace PASVL\Parsing\Simple\Tokens;

class TokenQuantifier
{
    /** @var int */
    private $min;
    /** @var int */
    private $max;

    private function __construct(int $min, int $max)
    {
        $this->min = $min;
        $this->max = $max;
    }

    public static function make(int $min, int $max): self
    {
        return new self($min, $max);
    }

    public function min(): int
    {
        return $this->min;
    }

    public function max(): int
    {
        return $this->max;
    }

    public function equals($other): bool
    {
        if (!$other instanceof $this) {
            return false;
        }

        return
            $this->min === $other->min &&
            $this->max === $other->max;
    }
}