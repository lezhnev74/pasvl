<?php

declare(strict_types=1);

namespace PASVL\Parsing\Simple;

use PASVL\Parsing\Parser;
use PASVL\Parsing\Problems\Eof;
use PASVL\Parsing\Problems\NonEmptyPostfix;
use PASVL\Parsing\Problems\NotLexeme;
use PASVL\Parsing\Problems\ParsingFailed;
use PASVL\Parsing\Problems\UnexpectedCharacter;
use PASVL\Parsing\Simple\Tokens\TokenNullableRule;
use PASVL\Parsing\Simple\Tokens\TokenQuantifier;
use PASVL\Parsing\Simple\Tokens\TokenRule;
use PASVL\Parsing\Simple\Tokens\TokenSubRule;

/**
 * Parse a text string like ":string :max(3) :min(2)" and produce a number of tokens
 */
class SimpleRuleParser extends Parser
{
    const PATTERN_NUMBER     = '-?\d*(?:\.\d*)?';
    const PATTERN_IDENTIFIER = '[a-zA-Z0-9_\x80-\xff][a-zA-Z0-9_\x80-\xff]*';

    const STATE_RULE       = 0;
    const STATE_SUB_RULE   = 1;
    const STATE_QUANTIFIER = 2;
    const STATE_FINISH     = 3;

    const TOKEN_RULE    = true;
    const TOKEN_SUBRULE = false;

    /**
     * Read the next token from the input string
     */
    protected function getNextToken()
    {
        switch ($this->state) {
            case self::STATE_RULE:
                try {
                    $token = $this->buildRuleToken(self::TOKEN_RULE);
                    $this->state = self::STATE_SUB_RULE;
                    return $token;
                } catch (NotLexeme | Eof $e) {
                    $this->state = self::STATE_SUB_RULE;
                    return $this->getNextToken();
                }
            case self::STATE_SUB_RULE:
                try {
                    return $this->buildRuleToken(self::TOKEN_SUBRULE);
                } catch (NotLexeme | Eof $e) {
                    $this->state = self::STATE_QUANTIFIER;
                    return $this->getNextToken();
                }
            case self::STATE_QUANTIFIER:
                try {
                    $token = $this->buildQuantifierToken();
                    $this->state = self::STATE_FINISH;
                    return $token;
                } catch (NotLexeme | Eof $e) {
                    $this->state = self::STATE_FINISH;
                    return $this->getNextToken();
                }
            case self::STATE_FINISH:
                if (isset($this->text[$this->pos])) {
                    throw new NonEmptyPostfix($this->pos);
                }
                throw new Eof();
            default:
                throw new ParsingFailed("unexpected state reached");
        }
    }

    /**
     * TokenRule and TokenSubRule are parsed identically
     * @return TokenRule
     */
    private function buildRuleToken(bool $buildRule = true)
    {
        $tokenType = $buildRule ? TokenRule::class : TokenSubRule::class;

        // 1. read Identifier lexeme
        $this->skipSpaces();
        try {
            $this->expect(":");
        } catch (UnexpectedCharacter $e) {
            throw new NotLexeme($this->pos);
        }

        $idLexeme = $this->expect(self::PATTERN_IDENTIFIER);
        try {
            $this->expect('\?');
            $tokenType = TokenNullableRule::class;
        } catch (UnexpectedCharacter $e) {
            //
        } finally {
            if (($tokenType === TokenNullableRule::class)) {
                if (!$buildRule) $this->fail();
                if ($this->strategy & self::STRATEGY_DISABLE_NULLABLE) $this->fail();
            }
        }

        $token = call_user_func([$tokenType, 'make'], $idLexeme, []);

        // 2. read optional Arguments lexeme
        try {
            if ($this->cur() !== "(") return $token;
        } catch (Eof $e) {
            return $token;
        }

        $this->expect("\(");
        $this->skipSpaces();

        $expectArgument = true;

        while (
            [$matchedPattern, $lexeme] = $this->expectAny([
            "\)",
            ",",
            '"',
            "'",
            self::PATTERN_NUMBER,
            ])
        ) {
            switch ($matchedPattern) {
                case "\)":
                    if ($expectArgument) $this->fail();
                    return $token;
                case ",":
                    if ($expectArgument) $this->fail();
                    $this->skipSpaces();
                    $expectArgument = true;
                    break;
                case '"':
                case "'":
                    if (!$expectArgument) $this->fail();
                    $lexeme = $this->readStringLiteralUntil($matchedPattern);
                    $token = call_user_func(
                        [$tokenType, 'make'],
                        $token->name(),
                        array_merge($token->arguments(), [$lexeme])
                    );
                    $expectArgument = false;
                    break;
                case self::PATTERN_NUMBER:
                    if (!$expectArgument) $this->fail();
                    //convert the number to proper type
                    $number = (float)$lexeme;
                    if ($number == intval($number)) $number = intval($number);

                    $token = call_user_func(
                        [$tokenType, 'make'],
                        $token->name(),
                        array_merge($token->arguments(), [$number])
                    );
                    $expectArgument = false;
                    break;
                default:
                    $this->fail();
            }
        }
    }

    private function buildQuantifierToken(): TokenQuantifier
    {
        try {
            [$matchedPattern, $lexeme] = $this->expectAny([
                '\+',
                '!',
                '\*',
                '\?',
                '{(\d+)}',
                '{,(\d+)}',
                '{(\d+),}',
                '{(\d+),(\d+)}',
            ]);
            switch ($matchedPattern) {
                case '\+':
                    $min = 1;
                    $max = PHP_INT_MAX;
                    break;
                case '!':
                    $min = 1;
                    $max = 1;
                    break;
                case '\*':
                    $min = 0;
                    $max = PHP_INT_MAX;
                    break;
                case '\?':
                    $min = 0;
                    $max = 1;
                    break;
                case '{(\d+)}':
                    $min = $max = (int)$lexeme[1];
                    break;
                case '{,(\d+)}':
                    $min = 0;
                    $max = (int)$lexeme[1];
                    break;
                case '{(\d+),}':
                    $min = (int)$lexeme[1];
                    $max = PHP_INT_MAX;
                    break;
                case '{(\d+),(\d+)}':
                    $min = (int)$lexeme[1];
                    $max = (int)$lexeme[2];
                    break;
                default:
                    $this->fail();
            }
            return TokenQuantifier::make($min, $max);
        } catch (UnexpectedCharacter $e) {
            throw new NotLexeme($this->pos);
        }
    }
}