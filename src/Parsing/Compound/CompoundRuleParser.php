<?php

declare(strict_types=1);

namespace PASVL\Parsing\Compound;

use PASVL\Parsing\Compound\Tokens\TokenCompoundOperand;
use PASVL\Parsing\Compound\Tokens\TokenOperator;
use PASVL\Parsing\Compound\Tokens\TokenSimpleOperand;
use PASVL\Parsing\Parser;
use PASVL\Parsing\Problems\Eof;
use PASVL\Parsing\Problems\NonEmptyPostfix;
use PASVL\Parsing\Problems\NotLexeme;
use PASVL\Parsing\Problems\ParsingFailed;
use PASVL\Parsing\Problems\UnexpectedCharacter;
use PASVL\Parsing\Simple\SimpleRuleParser;

/**
 * Parses compound rules like "(<simple>) or (<simple>)", "(<simple>) and ((<simple>) or (<simple>))" and "(<simple>)"
 */
class CompoundRuleParser extends Parser
{
    const STATE_OPERAND  = 2;
    const STATE_OPERATOR = 3;

    public function parse(string $text, int $strategy = self::STRATEGY_STRICT): array
    {
        $tokens = parent::parse($text, $strategy);
        return count($tokens) > 1 ? [TokenCompoundOperand::make($tokens)] : $tokens;
    }

    protected function getNextToken()
    {
        switch ($this->state) {
            case self::STATE_START:
            case self::STATE_OPERAND:
                $token = $this->buildOperandToken();
                $this->state = self::STATE_OPERATOR;
                return $token;
            case self::STATE_OPERATOR:
                try {
                    $token = $this->buildOperatorToken();
                    $this->state = self::STATE_OPERAND;
                    return $token;
                } catch (NotLexeme $e) {
                    // no operator means the operand is unary
                    $this->state = self::STATE_FINISH;
                    return $this->getNextToken();
                }
            case self::STATE_FINISH:
                if (strlen($this->remainder())) {
                    throw new NonEmptyPostfix($this->pos);
                }
                throw new Eof();
            default:
                throw new ParsingFailed("unexpected state reached");
        }
    }

    private function buildOperatorToken(): TokenOperator
    {
        $this->skipSpaces();
        try {
            [, $lexeme] = $this->expectAny(["or", "and"]);
        } catch (UnexpectedCharacter $e) {
            throw new NotLexeme($this->pos);
        }
        if ($lexeme == "or") {
            return TokenOperator::make(TokenOperator::OPERATOR_OR);
        }
        return TokenOperator::make(TokenOperator::OPERATOR_AND);
    }

    private function buildOperandToken()
    {
        $token = null;
        $this->skipSpaces();
        $nextSymbol = $this->select([
            "\(", // compound start
            ":",  // simple rule start
            // quantifiers start:
            '\+',
            '!',
            '\*',
            '\?',
            '{',
        ]);
        switch ($nextSymbol) {
            case ":":
            case "\+":
            case "!":
            case "\*":
            case "\?":
            case "{":
                $p = new SimpleRuleParser();
                $ruleTokens = $p->parse($this->remainder(), SimpleRuleParser::STRATEGY_ALLOW_POSTFIX);
                $token = TokenSimpleOperand::make($ruleTokens);
                $this->move($p->pos);
                break;
            case "\(":
                $this->move();
                $p = new CompoundRuleParser();
                $tokens = $p->parse($this->remainder(), CompoundRuleParser::STRATEGY_ALLOW_POSTFIX);
                $token = count($tokens) > 1 ? TokenCompoundOperand::make($tokens) : $tokens[0];
                $this->move($p->pos);
                $this->skipSpaces();
                $this->expect("\)");
                break;
        }

        return $token;
    }
}