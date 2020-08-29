<?php
declare(strict_types=1);


namespace PASVL\Parsing\Compound\Tokens;


class TokenSimpleOperand implements Operand
{
    /** @var array of rule tokens */
    private $tokens;

    private function __construct(array $tokens) { $this->tokens = $tokens; }

    public static function make(array $tokens): self
    {
        return new static($tokens);
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

    public function tokens(): array
    {
        return $this->tokens;
    }
}