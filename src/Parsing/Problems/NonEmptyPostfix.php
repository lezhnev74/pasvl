<?php

declare(strict_types=1);

namespace PASVL\Parsing\Problems;

class NonEmptyPostfix extends ParsingFailed
{
    /** @var int */
    public $pos;

    public function __construct(int $pos)
    {
        parent::__construct(sprintf("finished parsing while there are more characters left at %d", $pos));
        $this->pos = $pos;
    }
}