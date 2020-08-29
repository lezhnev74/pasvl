<?php
declare(strict_types=1);

namespace PASVL\Tests\Parsing\Compound\Tokens;

use PASVL\Parsing\Compound\Tokens\TokenCompoundOperand;
use PASVL\Parsing\Compound\Tokens\TokenOperator;
use PASVL\Parsing\Compound\Tokens\TokenSimpleOperand;
use PASVL\Parsing\Simple\Tokens\TokenRule;
use PHPUnit\Framework\TestCase;

class TokenCompoundOperandTest extends TestCase
{
    public function testCreation(): void
    {
        $op = TokenCompoundOperand::make([
            TokenSimpleOperand::make([TokenRule::make('stringA', [])]),
            TokenOperator::make(TokenOperator::OPERATOR_AND),
            TokenSimpleOperand::make([TokenRule::make('stringB', [])]),
        ]);

        $this->assertCount(3, $op->tokens());
        $this->assertTrue($op->tokens()[0]->equals(TokenSimpleOperand::make([TokenRule::make('stringA', [])])));
        $this->assertTrue($op->tokens()[1]->equals(TokenOperator::make(TokenOperator::OPERATOR_AND)));
        $this->assertTrue($op->tokens()[2]->equals(TokenSimpleOperand::make([TokenRule::make('stringB', [])])));

        // other tokens
        $this->assertFalse($op->equals(
            TokenCompoundOperand::make([
                TokenSimpleOperand::make([TokenRule::make('stringC', [])]),
                TokenSimpleOperand::make([TokenRule::make('stringD', [])]),
            ])
        ));
        // another order
        $this->assertFalse($op->equals(
            TokenCompoundOperand::make([
                TokenSimpleOperand::make([TokenRule::make('stringA', [])]),
                TokenSimpleOperand::make([TokenRule::make('stringB', [])]),
                TokenOperator::make(TokenOperator::OPERATOR_AND),
            ])
        ));
        // another type
        $this->assertFalse($op->equals(10));

        // normalization
        $op = TokenCompoundOperand::make([
            TokenCompoundOperand::make([
                TokenSimpleOperand::make([TokenRule::make('stringA', [])]),
            ]),
        ]);
        $this->assertTrue($op->equals(TokenCompoundOperand::make([
            TokenSimpleOperand::make([TokenRule::make('stringA', [])]),
        ])));
    }
}
