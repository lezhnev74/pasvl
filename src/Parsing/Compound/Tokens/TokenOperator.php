<?php

declare(strict_types=1);

namespace PASVL\Parsing\Compound\Tokens;

class TokenOperator
{
    public const OPERATOR_OR  = 0;
    public const OPERATOR_AND = 1;

    private int $operator;

    private function __construct(int $operator)
    {
        if (!in_array($operator, [self::OPERATOR_AND, self::OPERATOR_OR])) {
            throw new \InvalidArgumentException('unexpected operator');
        }

        $this->operator = $operator;
    }

    public static function make(int $o): self
    {
        return new self($o);
    }

    public function isOr(): bool
    {
        return $this->operator === self::OPERATOR_OR;
    }

    public function isAnd(): bool
    {
        return $this->operator === self::OPERATOR_AND;
    }

    public function equals($other): bool
    {
        if (!$other instanceof $this) {
            return false;
        }

        return $this->operator === $other->operator;
    }
}
