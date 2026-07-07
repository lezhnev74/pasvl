<?php

declare(strict_types=1);

namespace PASVL\Parsing\Compound\Tokens;

class TokenCompoundOperand implements Operand
{
    private function __construct(private array $tokens)
    {
    }

    public static function make(array $tokens): self
    {
        // normalize
        if (count($tokens) === 1 && $tokens[0] instanceof self) {
            return $tokens[0];
        }

        return new self($tokens);
    }

    public function tokens(): array
    {
        return $this->tokens;
    }

    public function equals($other): bool
    {
        if (!$other instanceof $this) {
            return false;
        }

        if (count($this->tokens) !== count($other->tokens)) {
            return false;
        }
        return array_all($this->tokens, fn ($t, $i) => $t->equals($other->tokens[$i]));
    }
}
