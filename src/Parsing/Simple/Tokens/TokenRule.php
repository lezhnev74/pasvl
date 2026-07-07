<?php

declare(strict_types=1);

namespace PASVL\Parsing\Simple\Tokens;

class TokenRule
{
    final private function __construct(protected string $name, protected array $arguments)
    {
    }

    public static function make(string $name, array $arguments): self
    {
        return new static($name, $arguments);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function arguments(): array
    {
        return $this->arguments;
    }

    public function equals($other): bool
    {
        if (!$other instanceof $this) {
            return false;
        }

        return
            $this->name === $other->name &&
            $this->arguments === $other->arguments;
    }
}
