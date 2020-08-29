<?php
declare(strict_types=1);

namespace PASVL\Tests\Parsing\Compound\Tokens;

use PASVL\Parsing\Compound\Tokens\TokenSimpleOperand;
use PASVL\Parsing\Simple\Tokens\TokenRule;
use PHPUnit\Framework\TestCase;

class TokenSimpleOperandTest extends TestCase
{
    public function testCreation(): void
    {
        $op = TokenSimpleOperand::make([TokenRule::make('string', [])]);
        $this->assertTrue($op->equals(TokenSimpleOperand::make([TokenRule::make('string', [])])));
        $this->assertFalse($op->equals(9));
        $this->assertEquals('string', $op->tokens()[0]->name());
    }

}
