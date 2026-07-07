<?php

declare(strict_types=1);

namespace PASVL\Parsing\Simple\Tokens;

class TokenQuantifier
{
    private function __construct(private int $min, private int $max)
    {
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
