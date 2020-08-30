<?php

declare(strict_types=1);

namespace PASVL\Parsing\Problems;

/**
 * Means that the current text does not match the lexeme
 */
class NotLexeme extends ParsingFailed
{
    /** @var int */
    public $pos;

    public function __construct(int $pos)
    {
        parent::__construct(sprintf("unexpected character at %d", $pos));
        $this->pos = $pos;
    }
}