<?php
declare(strict_types=1);


namespace PASVL\Parsing\Compound\Tokens;


/**
 * Operand can be simple or compound, both have some tokens in it
 */
interface Operand
{
    public function tokens(): array;
}