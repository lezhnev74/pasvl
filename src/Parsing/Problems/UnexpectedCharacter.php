<?php
declare(strict_types=1);


namespace PASVL\Parsing\Problems;


class UnexpectedCharacter extends ParsingFailed
{
    /** @var int */
    public $pos;
    /** @var string */
    public $characters;

    public function __construct(int $pos, string $characters, string $text)
    {
        parent::__construct(sprintf("unexpected character at %d: [%s]. Original text: [%s]", $pos, $characters, $text));
        $this->pos = $pos;
        $this->characters = $characters;
    }
}