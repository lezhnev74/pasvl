<?php

declare(strict_types=1);

namespace PASVL\Parsing\Compound\Tokens;

class TokenCompoundOperand implements Operand
{
    /** @var array */
    private $tokens;

    private function __construct(array $tokens)
    {
        $this->tokens = $tokens;
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

        foreach ($this->tokens as $i => $t) {
            if (!$t->equals($other->tokens[$i])) {
                return false;
            }
        }

        return true;
    }
}