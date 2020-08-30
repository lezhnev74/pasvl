<?php

declare(strict_types=1);

namespace PASVL\Validation\Matcher;

use PASVL\Parsing\Compound\Tokens\TokenCompoundOperand;
use PASVL\Parsing\Compound\Tokens\TokenOperator;
use PASVL\Parsing\Compound\Tokens\TokenSimpleOperand;
use PASVL\Parsing\Simple\Tokens\TokenNullableRule;
use PASVL\Parsing\Simple\Tokens\TokenQuantifier;
use PASVL\Parsing\Simple\Tokens\TokenRule;
use PASVL\Parsing\Simple\Tokens\TokenSubRule;
use PASVL\Validation\Rules\Problems\RuleFailed;
use PASVL\Validation\Rules\Problems\SubRuleNotRecognized;
use PASVL\Validation\Rules\Rule;
use PASVL\Validation\Rules\RuleLocator;

class TokensMatcher
{
    private const STATE_START  = 0; // reduce all TokenSimpleOperand's to Result
    private const STATE_AND    = 1; // apply all AND operators
    private const STATE_OR     = 2; // apply all OR operators
    private const STATE_FINISH = 3; // calculate the final Result

    /** @var array of tokens to analyze */
    private $tokens;
    private $state = self::STATE_START;
    /** @var RuleLocator */
    private $rulesLocator;

    public function __construct(array $tokens, RuleLocator $rulesLocator)
    {
        $this->tokens = $tokens;
        $this->rulesLocator = $rulesLocator;
    }

    /**
     * Test if the given $input can be described with the given tokens
     */
    public function match($input): Result
    {
        switch ($this->state) {
            case self::STATE_START:
                $this->reduce($input);
                $this->state = self::STATE_AND;
                return $this->match($input);
            case self::STATE_AND:
                $this->evaluateOperators(TokenOperator::OPERATOR_AND);
                $this->state = self::STATE_OR;
                return $this->match($input);
            case self::STATE_OR:
                $this->evaluateOperators(TokenOperator::OPERATOR_OR);
                $this->state = self::STATE_FINISH;
                return $this->match($input);
            case self::STATE_FINISH:
                if (!count($this->tokens)) return Result::passed(); // nothing to do, ex: "" or "*"
                if (count($this->tokens) !== 1) throw new \RuntimeException('Unmatched count of results');
                return $this->tokens[0];
            default:
                throw new \RuntimeException('Unexpected state');
        }
    }

    /**
     * Reduce all TokenSimpleOperand to Result recursively
     */
    private function reduce($input): void
    {
        foreach ($this->tokens as $i => $token) {
            switch (get_class($token)) {
                case TokenSimpleOperand::class:
                    $this->tokens[$i] = $this->matchSimpleToken($token, $input);
                    break;
                case TokenCompoundOperand::class:
                    /** @var TokenCompoundOperand $token */
                    $newMatcher = new self($token->tokens(), $this->rulesLocator);
                    $this->tokens[$i] = $newMatcher->match($input);
                    break;
                case TokenOperator::class:
                case Result::class:
                    // noting to simplify, continue
                    break;
                default:
                    throw new \RuntimeException('Unexpected token');
            }
        }
    }

    private function matchSimpleToken(TokenSimpleOperand $simpleOperand, $input): Result
    {
        // Simple operand can contain different sets of tokens:
        // 1. Rule + subrule + optional quantifier
        // 2. Rule + quantifier
        // 3. Quantifier only
        // We are only interested in matching against rule and subrules (if present)

        if ($simpleOperand->tokens()[0] instanceof TokenQuantifier) return Result::passed(); // no rule given? then do nothing (example: "*")

        // Prepare tokens
        /** @var TokenRule $ruleToken */
        $ruleToken = $simpleOperand->tokens()[0];
        if (is_null($input) && $ruleToken instanceof TokenNullableRule) return Result::passed();

        // Locate the rule
        /** @var TokenSubRule[] $subRuleTokens */
        $subRuleTokens = array_filter($simpleOperand->tokens(), function ($t) { return $t instanceof TokenSubRule; });
        /** @var Rule $rule */
        $rule = $this->rulesLocator->locate($ruleToken->name());

        // Use the rule against the input
        try {
            $rule->putValue($input);
            call_user_func_array([$rule, 'test'], $ruleToken->arguments());

            foreach ($subRuleTokens as $subRuleToken) {
                if (!method_exists($rule, $subRuleToken->name())) {
                    throw new SubRuleNotRecognized($ruleToken->name(), $subRuleToken->name());
                }
                call_user_func_array([$rule, $subRuleToken->name()], $subRuleToken->arguments());
            }
            return Result::passed();
        } catch (RuleFailed $e) {
            return Result::failed($e);
        }
    }

    private function evaluateOperators(int $opType): void
    {
        do {
            $this->tokens = array_values($this->tokens);
            $reiterate = false;
            foreach ($this->tokens as $i => $token) {
                if (!$token instanceof TokenOperator) continue;
                if (!$token->equals(TokenOperator::make($opType))) continue;
                if ($i % 2 === 0) throw new \RuntimeException('Only binary operators supported');

                /** @var Result $lOperand */
                $lOperand = $this->tokens[$i - 1];
                /** @var Result $rOperand */
                $rOperand = $this->tokens[$i + 1];
                unset($this->tokens[$i - 1]);
                unset($this->tokens[$i + 1]);

                if ($token->isAnd()) {
                    $r = $lOperand->isPassed() && $rOperand->isPassed();
                } else {
                    $r = $lOperand->isPassed() || $rOperand->isPassed();
                }
                $this->tokens[$i] = $r ? Result::passed() : Result::failed(new RuleFailed('Compound rule has been evaluated to FAIL'));
                $reiterate = true;
                break;
            }
        } while ($reiterate);
    }
}