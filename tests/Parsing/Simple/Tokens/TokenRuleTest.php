<?php
declare(strict_types=1);

namespace PASVL\Tests\Parsing\Simple\Tokens;

use PASVL\Parsing\Simple\Tokens\TokenRule;
use PHPUnit\Framework\TestCase;

class TokenRuleTest extends TestCase
{
    public function testCreation(): void
    {
        $t = TokenRule::make('string', ['a', 2]);
        $this->assertEquals('string', $t->name());
        $this->assertEquals(['a',2], $t->arguments());
        $this->assertTrue($t->equals(TokenRule::make('string', ['a', 2])));
        $this->assertFalse($t->equals(TokenRule::make('string', ['a', 3])));
        $this->assertFalse($t->equals('q'));
    }
}
