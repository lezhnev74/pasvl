<?php

declare(strict_types=1);

namespace PASVL\Tests\Parsing\Compound\Tokens;

use PASVL\Parsing\Compound\Tokens\TokenOperator;
use PHPUnit\Framework\TestCase;

class TokenOperatorTest extends TestCase
{
    public function testCreation(): void
    {
        $op = TokenOperator::make(TokenOperator::OPERATOR_OR);
        $this->assertTrue($op->isOr());
        $this->assertFalse($op->isAnd());
        $this->assertTrue($op->equals(TokenOperator::make(TokenOperator::OPERATOR_OR)));

        $op = TokenOperator::make(TokenOperator::OPERATOR_AND);
        $this->assertFalse($op->isOr());
        $this->assertTrue($op->isAnd());
        $this->assertTrue($op->equals(TokenOperator::make(TokenOperator::OPERATOR_AND)));

        try {
            $op = TokenOperator::make(999);
        } catch (\InvalidArgumentException $e) {
            $this->addToAssertionCount(1);
        }
    }
}
