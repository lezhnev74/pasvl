<?php

declare(strict_types=1);

namespace PASVL\Tests\Parsing\Compound;

use PASVL\Parsing\Compound\CompoundRuleParser;
use PASVL\Parsing\Compound\Tokens\TokenCompoundOperand;
use PASVL\Parsing\Compound\Tokens\TokenOperator;
use PASVL\Parsing\Compound\Tokens\TokenSimpleOperand;
use PASVL\Parsing\Problems\NonEmptyPostfix;
use PASVL\Parsing\Problems\UnexpectedCharacter;
use PASVL\Parsing\Simple\Tokens\TokenQuantifier;
use PASVL\Parsing\Simple\Tokens\TokenRule;
use PASVL\Parsing\Simple\Tokens\TokenSubRule;
use PHPUnit\Framework\TestCase;

class CompoundRuleParserTest extends TestCase
{
    public function badTestSet(): array
    {
        return [
            ["()", UnexpectedCharacter::class],
            ["(())", UnexpectedCharacter::class],
            ["((()))()", UnexpectedCharacter::class],
            ["(:string))", NonEmptyPostfix::class],
            ["((:string)", UnexpectedCharacter::class],
            ["((string)", UnexpectedCharacter::class],
            ["not (:string)", UnexpectedCharacter::class],
            ["(:string) while (:string)", NonEmptyPostfix::class],
            ["(()", UnexpectedCharacter::class],
        ];
    }

    public function goodTestSet(): array
    {
        return [
            ["", []],
            ["*", [TokenSimpleOperand::make([TokenQuantifier::make(0, PHP_INT_MAX)])]],
            [
                ":string",
                [TokenSimpleOperand::make([TokenRule::make('string', [])]),],
            ],
            [
                ":string :reg('abc')",
                [TokenSimpleOperand::make([TokenRule::make('string', []), TokenSubRule::make('reg', ['abc'])]),],
            ],
            [
                ":stringA or :stringB",
                [
                    TokenCompoundOperand::make([
                        TokenSimpleOperand::make([TokenRule::make('stringA', [])]),
                        TokenOperator::make(TokenOperator::OPERATOR_OR),
                        TokenSimpleOperand::make([TokenRule::make('stringB', [])]),
                    ]),

                ],
            ],
            [
                "(:stringA) or (:stringB)",
                [
                    TokenCompoundOperand::make([
                        TokenSimpleOperand::make([TokenRule::make('stringA', [])]),
                        TokenOperator::make(TokenOperator::OPERATOR_OR),
                        TokenSimpleOperand::make([TokenRule::make('stringB', [])]),
                    ]),

                ],
            ],
            [
                "((:stringA) or (:stringB))",
                [
                    TokenCompoundOperand::make([
                        TokenSimpleOperand::make([TokenRule::make('stringA', [])]),
                        TokenOperator::make(TokenOperator::OPERATOR_OR),
                        TokenSimpleOperand::make([TokenRule::make('stringB', [])]),
                    ]),

                ],
            ],
            [
                ":stringA and (:stringB or :stringC)",
                [
                    TokenCompoundOperand::make([
                        TokenSimpleOperand::make([TokenRule::make('stringA', [])]),
                        TokenOperator::make(TokenOperator::OPERATOR_AND),
                        TokenCompoundOperand::make([
                            TokenSimpleOperand::make([TokenRule::make('stringB', [])]),
                            TokenOperator::make(TokenOperator::OPERATOR_OR),
                            TokenSimpleOperand::make([TokenRule::make('stringC', [])]),
                        ]),
                    ]),

                ],
            ],
            [
                ":stringA :min(1) or :stringB :max(2)",
                [
                    TokenCompoundOperand::make([
                        TokenSimpleOperand::make([TokenRule::make('stringA', []), TokenSubRule::make('min', [1])]),
                        TokenOperator::make(TokenOperator::OPERATOR_OR),
                        TokenSimpleOperand::make([TokenRule::make('stringB', []), TokenSubRule::make('max', [2])]),
                    ]),

                ],
            ],
            [
                ":stringA or :stringB and :stringC",
                [
                    TokenCompoundOperand::make([
                        TokenSimpleOperand::make([TokenRule::make('stringA', [])]),
                        TokenOperator::make(TokenOperator::OPERATOR_OR),
                        TokenSimpleOperand::make([TokenRule::make('stringB', [])]),
                        TokenOperator::make(TokenOperator::OPERATOR_AND),
                        TokenSimpleOperand::make([TokenRule::make('stringC', [])]),
                    ]),

                ],
            ],
            [
                "(:string)",
                [
                    TokenSimpleOperand::make([TokenRule::make('string', [])]),
                ],
            ],
            [
                "(((:string :max(1))))",
                [
                    TokenSimpleOperand::make([
                        TokenRule::make('string', []),
                        TokenSubRule::make('max', [1]),
                    ]),
                ],
            ],
            [
                "(:stringA) and (:stringB)",
                [
                    TokenCompoundOperand::make([
                        TokenSimpleOperand::make([TokenRule::make('stringA', [])]),
                        TokenOperator::make(TokenOperator::OPERATOR_AND),
                        TokenSimpleOperand::make([TokenRule::make('stringB', [])]),
                    ]),
                ],
            ],
            [
                "(:string) or (:string)",
                [
                    TokenCompoundOperand::make([
                        TokenSimpleOperand::make([TokenRule::make('string', [])]),
                        TokenOperator::make(TokenOperator::OPERATOR_OR),
                        TokenSimpleOperand::make([TokenRule::make('string', [])]),
                    ]),
                ],
            ],
            [
                "(((:string) or (:string)))",
                [
                    TokenCompoundOperand::make([
                        TokenSimpleOperand::make([TokenRule::make('string', [])]),
                        TokenOperator::make(TokenOperator::OPERATOR_OR),
                        TokenSimpleOperand::make([TokenRule::make('string', [])]),
                    ]),
                ],
            ],
            [
                "(:stringA) or ((:stringB :min(1)) and (:stringC :max('2a')))",
                [
                    TokenCompoundOperand::make([
                        TokenSimpleOperand::make([TokenRule::make('stringA', [])]),
                        TokenOperator::make(TokenOperator::OPERATOR_OR),
                        TokenCompoundOperand::make([
                            TokenSimpleOperand::make([
                                TokenRule::make('stringB', []),
                                TokenSubRule::make('min', [1]),
                            ]),
                            TokenOperator::make(TokenOperator::OPERATOR_AND),
                            TokenSimpleOperand::make([
                                TokenRule::make('stringC', []),
                                TokenSubRule::make('max', ['2a']),
                            ]),
                        ]),
                    ]),
                ],
            ],
        ];
    }

    /** @dataProvider goodTestSet */
    public function testParsesInputTextCorrectly(string $text, array $expectedTokens): void
    {
        $parser = new CompoundRuleParser();
        $actualTokens = $parser->parse($text);

        $this->assertCount(count($expectedTokens), $actualTokens);
        foreach ($expectedTokens as $i => $expectedToken) {
            $this->assertTrue($expectedToken->equals($actualTokens[$i]));
        }
    }

    /** @dataProvider badTestSet */
    public function testThrowsOnInvalidInputText(string $text, string $expectedExceptionType): void
    {
        $this->expectException($expectedExceptionType);
        $parser = new CompoundRuleParser();
        $parser->parse($text);
    }
}
