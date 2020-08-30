<?php

declare(strict_types=1);

namespace PASVL\Tests\Parsing\Simple\Tokens;

use PASVL\Parsing\Simple\Tokens\TokenQuantifier;
use PHPUnit\Framework\TestCase;

class TokenQuantifierTest extends TestCase
{
    public function testCreation(): void
    {
        $token = TokenQuantifier::make(0, 1);

        $this->assertEquals(0, $token->min());
        $this->assertEquals(1, $token->max());
        $this->assertTrue($token->equals(TokenQuantifier::make(0, 1)));
        $this->assertFalse($token->equals(TokenQuantifier::make(0, 2)));
        $this->assertFalse($token->equals(2));
    }
}
