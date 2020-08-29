<?php
declare(strict_types=1);

namespace PASVL\Tests\Parsing\Simple;

use PASVL\Parsing\Problems\NonEmptyPostfix;
use PASVL\Parsing\Problems\UnexpectedCharacter;
use PASVL\Parsing\Simple\SimpleRuleParser;
use PASVL\Parsing\Simple\Tokens\TokenNullableRule;
use PASVL\Parsing\Simple\Tokens\TokenQuantifier;
use PASVL\Parsing\Simple\Tokens\TokenRule;
use PASVL\Parsing\Simple\Tokens\TokenSubRule;
use PHPUnit\Framework\TestCase;

class SimpleRuleParserTest extends TestCase
{
    public function badTestSet(): array
    {
        return [
            ["exact", NonEmptyPostfix::class],
            [":string :min?(1)", UnexpectedCharacter::class],
            [":min('1'')", UnexpectedCharacter::class],
            [':min("1"")', UnexpectedCharacter::class],
            [":min * abc", NonEmptyPostfix::class],
            [":", UnexpectedCharacter::class],
            [":re(1,2,)", UnexpectedCharacter::class],
            [":re(1,2,,)", UnexpectedCharacter::class],
            [':re("\")', UnexpectedCharacter::class],
            [":re('\')", UnexpectedCharacter::class],
            [":re('", UnexpectedCharacter::class],
            [":re('1'", UnexpectedCharacter::class],
            [":re(1", UnexpectedCharacter::class],
            [":re(a", UnexpectedCharacter::class],
            [":re(", UnexpectedCharacter::class],
            [":re(abc)", UnexpectedCharacter::class],
            [":string **", NonEmptyPostfix::class],
            [":string *{1,2}", NonEmptyPostfix::class],
            [":string ++", NonEmptyPostfix::class],
            [":string +?", NonEmptyPostfix::class],
            [":string&", NonEmptyPostfix::class],
            [":&", UnexpectedCharacter::class],
            [": * *", UnexpectedCharacter::class],
        ];
    }

    public function goodTestSet(): array
    {
        return [
            ["", []],
            [":string", [TokenRule::make("string", [])]],
            [":string?", [TokenNullableRule::make("string", [])]],
            [":string??", [TokenNullableRule::make("string", []), TokenQuantifier::make(0, 1)]],
            [":string ?", [TokenRule::make("string", []), TokenQuantifier::make(0, 1)]],
            [":string('\'')", [TokenRule::make("string", ["'"])]],
            [
                ":string :regexp('\w{1,4}') {1}",
                [TokenRule::make("string", []), TokenSubRule::make("regexp", ['\w{1,4}']), TokenQuantifier::make(1, 1)],
            ],
            [":string :min(501)", [TokenRule::make("string", []), TokenSubRule::make("min", [501])]],
            [":string? :min(1)", [TokenNullableRule::make("string", []), TokenSubRule::make("min", [1])]],
            [":string :min(1.2)", [TokenRule::make("string", []), TokenSubRule::make("min", [1.2])]],
            [":string :eq('a')", [TokenRule::make("string", []), TokenSubRule::make("eq", ['a'])]],
            [':string :eq("a")', [TokenRule::make("string", []), TokenSubRule::make("eq", ['a'])]],
            [":string :min(.999)", [TokenRule::make("string", []), TokenSubRule::make("min", [.999])]],
            [
                ":string :min(1) :max(5)",
                [
                    TokenRule::make("string", []),
                    TokenSubRule::make("min", [1]),
                    TokenSubRule::make("max", [5]),
                ],
            ],
            [
                ":string :between(1,5)",
                [
                    TokenRule::make("string", []),
                    TokenSubRule::make("between", [1, 5]),
                ],
            ],
            [
                ":string *",
                [
                    TokenRule::make("string", []),
                    TokenQuantifier::make(0, PHP_INT_MAX),
                ],
            ],
            [
                ":re('abc') :re('^(a\")+$')",
                [
                    TokenRule::make("re", ['abc']),
                    TokenSubRule::make("re", ['^(a")+$']),
                ],
            ],
            // Quantifiers
            ["{1}", [TokenQuantifier::make(1, 1)]],
            ["{1,}", [TokenQuantifier::make(1, PHP_INT_MAX)]],
            ["{,3}", [TokenQuantifier::make(0, 3)]],
            ["{2,3}", [TokenQuantifier::make(2, 3)]],
            ["+", [TokenQuantifier::make(1, PHP_INT_MAX)]],
            ["!", [TokenQuantifier::make(1, 1)]],
            ["*", [TokenQuantifier::make(0, PHP_INT_MAX)]],
            ["?", [TokenQuantifier::make(0, 1)]],
        ];
    }

    /** @dataProvider goodTestSet */
    public function testParsesInputTextCorrectly($text, array $expectedTokens): void
    {
        $parser = new SimpleRuleParser();
        $actualTokens = $parser->parse((string)$text);

        $this->assertCount(count($expectedTokens), $actualTokens);
        foreach ($expectedTokens as $i => $expectedToken) {
            $this->assertTrue($expectedToken->equals($actualTokens[$i]));
        }
    }

    /** @dataProvider badTestSet */
    public function testThrowsOnInvalidInputText($text, string $expectedExceptionType): void
    {
        $this->expectException($expectedExceptionType);
        $parser = new SimpleRuleParser();
        $parser->parse($text);
    }

    public function testNullableStrategy(): void
    {
        $parser = new SimpleRuleParser();
        $parser->parse(":string??");
        $this->addToAssertionCount(1);

        $parser->parse(":string ?", SimpleRuleParser::STRATEGY_STRICT | SimpleRuleParser::STRATEGY_DISABLE_NULLABLE);

        try {
            $parser->parse(":string?", SimpleRuleParser::STRATEGY_STRICT | SimpleRuleParser::STRATEGY_DISABLE_NULLABLE);
            $this->fail('Must have been thrown');
        } catch (UnexpectedCharacter $e) {
            $this->addToAssertionCount(1);
        }
    }
}
