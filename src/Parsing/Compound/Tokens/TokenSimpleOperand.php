<?php

declare(strict_types=1);

namespace PASVL\Parsing\Compound\Tokens;

class TokenSimpleOperand implements Operand
{
    private function __construct(
        /** @var array of rule tokens */
        private array $tokens
    ) {
    }

    public static function make(array $tokens): self
    {
        return new self($tokens);
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

    public function tokens(): array
    {
        return $this->tokens;
    }
}
