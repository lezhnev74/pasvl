<?php

declare(strict_types=1);

namespace PASVL\Validation;

use PASVL\Parsing\Compound\CompoundRuleParser;
use PASVL\Parsing\Compound\Tokens\Operand;
use PASVL\Parsing\Compound\Tokens\TokenSimpleOperand;
use PASVL\Parsing\Problems\UnexpectedCharacter;
use PASVL\Parsing\Simple\Tokens\TokenQuantifier;
use PASVL\Parsing\Simple\Tokens\TokenRule;
use PASVL\Validation\Matcher\TokensMatcher;
use PASVL\Validation\Problems\ArrayFailedValidation;
use PASVL\Validation\Problems\DataKeyMatchedNoPatternKey;
use PASVL\Validation\Problems\DataValueMatchedNoPattern;
use PASVL\Validation\Problems\MatchAttempt;
use PASVL\Validation\Rules\Problems\RuleFailed;
use PASVL\Validation\Rules\RuleLocator;

class ArrayValidator extends Validator
{
    /** @var string[] the current position(level) in the data array */
    private $level = [];
    /** @var Operand[] $tokenizedPatterns */
    private $tokenizedPatterns = [];

    public function __construct(RuleLocator $locator, private array $pattern)
    {
        parent::__construct($locator);
    }

    public function validate($data): void
    {
        if (!is_array($data)) {
            throw new \RuntimeException('Data must be an array');
        }

        $this->matchValue($this->pattern, $data);
    }

    /** Match one value against the pattern */
    private function matchValue($pattern, $value): void
    {
        if (is_array($pattern)) {
            // Compound matching
            if (!is_array($value)) {
                throw ArrayFailedValidation::make(
                    $this->level,
                    sprintf('Pattern describes an array while given data is %s', gettype($value))
                );
            }
            $this->validateArrayLevel($pattern, $value);
        } else {
            // Scalar matching
            $operand = $this->tokenize($pattern);
            $matcher = new TokensMatcher([$operand], $this->locator());
            $result = $matcher->match($value);
            if (!$result->isPassed()) {
                throw $result->reason();
            }
        }
    }

    private function validateArrayLevel(array $patternLevel, array $dataLevel): void
    {
        $matchedKeys = []; // [dataKey1 => [patternKey1,...], ...]
        $attempts = []; // MatchAttempt[] - best-failure candidates for this level
        $patternOrder = array_flip(array_keys($patternLevel)); // patternKey => declaration index
        foreach (array_keys($dataLevel) as $dataKey) {
            // 1. Find pattern keys that correctly describe the given $dataKey
            $keyMatches = [];
            foreach (array_keys($patternLevel) as $patternKey) {
                try {
                    $this->matchValue($patternKey, $dataKey);
                    $keyMatches[] = $patternKey;
                } catch (RuleFailed $e) {
                    $attempts[] = new MatchAttempt(
                        $patternKey,
                        MatchAttempt::STAGE_KEY_MISMATCH,
                        $e,
                        count($this->level),
                        $patternOrder[$patternKey]
                    );
                }
            }
            $matchedKeys[$dataKey] = $keyMatches;
            if (!$matchedKeys[$dataKey]) {
                throw DataKeyMatchedNoPatternKey::fromData(
                    $dataKey,
                    $this->level,
                    $this->bestAttempt($attempts),
                    $dataKey
                );
            }

            // 2. Filter out pattern keys which corresponding pattern values do not match the given $dataValue
            $valueMatches = [];
            foreach ($matchedKeys[$dataKey] as $patternKey) {
                $isArrayValue = is_array($dataLevel[$dataKey]);
                try {
                    if ($isArrayValue) {
                        $this->pushPatternLevel($patternKey);
                    }
                    $this->matchValue($patternLevel[$patternKey], $dataLevel[$dataKey]);
                    if ($isArrayValue) {
                        $this->revertOneLevel();
                    }
                    $valueMatches[] = $patternKey;
                } catch (RuleFailed $e) {
                    // Only reachable for scalar values (matchValue throws RuleFailed there).
                    // Nested-array failures throw ArrayFailedValidation and are intentionally
                    // NOT caught here - they propagate already-enriched.
                    $attempts[] = new MatchAttempt(
                        $patternKey,
                        MatchAttempt::STAGE_VALUE_MISMATCH,
                        $e,
                        count($this->level),
                        $patternOrder[$patternKey]
                    );
                }
            }
            $matchedKeys[$dataKey] = $valueMatches;
            if (!$matchedKeys[$dataKey]) {
                throw DataValueMatchedNoPattern::fromData(
                    $dataLevel[$dataKey],
                    $this->level,
                    $this->bestAttempt($attempts),
                    $dataKey
                );
            }
        }

        // 3. Make combinations of data keys and pattern keys and filter out those that don't match pattern quantifiers
        $qualifiedCombinations = array_filter(
            $this->getCombinations($matchedKeys),
            fn ($combination): bool => array_all(array_count_values($combination), fn (int $count, string $patternKey): bool => $this->quantityMatch($patternKey, $count))
        );

        // 4. So far we checked that all given data matches given patterns, but if data is absent, we need to check the
        // other way around. Test that all given patterns describe data that is present.
        $patternKeyFulfillmentMap = array_fill_keys(array_keys($patternLevel), false);
        foreach (array_keys($patternKeyFulfillmentMap) as $patternKey) {
            // if a patternKey was not selected for data key (within combinations)
            // and it does not have 0-quantity expectations, then this patternKey has unfulfilled expectations
            $patternKeyIsInCombination = false;
            foreach ($qualifiedCombinations as $combination) {
                foreach ($combination as $validPatternKey) {
                    $patternKeyIsInCombination = ($validPatternKey == $patternKey);
                    if ($patternKeyIsInCombination) {
                        break 2;
                    }
                }
            }

            $patternKeyWasPerspective = false;
            foreach ($matchedKeys as $perspectivePatternKeys) {
                if ($patternKeyWasPerspective) {
                    break;
                }
                $patternKeyWasPerspective = in_array($patternKey, $perspectivePatternKeys);
            }

            $patternKeyFulfillmentMap[$patternKey] =
                $patternKeyIsInCombination || (!$patternKeyWasPerspective && $this->quantityMatch($patternKey, 0));
        }
        if (array_sum($patternKeyFulfillmentMap) !== count($patternKeyFulfillmentMap)) {
            $unfulfilled = [];
            foreach ($patternKeyFulfillmentMap as $patternKey => $ok) {
                if (!$ok) {
                    $unfulfilled[] = sprintf(
                        '[%s] (expected %s)',
                        $patternKey,
                        $this->quantifierExpectation((string)$patternKey)
                    );
                }
            }
            throw ArrayFailedValidation::make(
                $this->level,
                sprintf('Unfulfilled pattern keys: %s', implode(', ', $unfulfilled))
            );
        }
    }

    private function bestAttempt(array $attempts): ?MatchAttempt
    {
        if ($attempts === []) {
            return null; // zero-attempt edge (empty pattern level)
        }
        usort($attempts, fn (MatchAttempt $a, MatchAttempt $b): int =>
            ($b->stage <=> $a->stage)     // VALUE_MISMATCH beats KEY_MISMATCH
            ?: ($b->depth <=> $a->depth)  // deeper failure wins
            ?: ($a->order <=> $b->order)); // earliest-declared pattern key
        return $attempts[0];
    }

    private function quantifierExpectation(string $patternKey): string
    {
        $operand = $this->tokenize($patternKey);
        foreach ($operand->tokens() as $token) {
            if ($token instanceof TokenQuantifier) {
                return $token->min() === $token->max()
                    ? sprintf('exactly %d', $token->min())
                    : sprintf('%d..%d', $token->min(), $token->max());
            }
        }
        return 'exactly 1';
    }

    private function tokenize($pattern): Operand
    {
        if (isset($this->tokenizedPatterns[$pattern])) {
            return $this->tokenizedPatterns[$pattern];
        }

        try {
            $operands = (new CompoundRuleParser())->parse((string)$pattern);
            if ($operands === []) {
                $operand = $operand = TokenSimpleOperand::make([TokenRule::make('exact', [''])]);
            } else {
                $operand = $operands[0];
            }
        } catch (UnexpectedCharacter $e) {
            if ($e->pos === 0) {
                // edge case, if unexpected character found in the first position, then treat this an the exact value
                // otherwise, user should use the pattern ":exact(':whatever :exact :value')"
                $operand = TokenSimpleOperand::make([TokenRule::make('exact', [$pattern])]);
                // extra edge case: "exact?", which is optional exact token
                if (is_string($pattern) && $pattern[strlen($pattern) - 1] === '?') {
                    $operand = TokenSimpleOperand::make([
                        TokenRule::make('exact', [mb_substr($pattern, 0, -1)]),
                        TokenQuantifier::make(0, 1),
                    ]);
                }
            } else {
                throw $e;
            }
        }

        $this->tokenizedPatterns[$pattern] = $operand;
        return $operand;
    }

    private function pushPatternLevel($patternKey): void
    {
        if (is_null($patternKey)) {
            if (!$this->level) {
                // initial case, root level
                // do nothing;
                return;
            }
            throw ArrayFailedValidation::make($this->level, 'Unable to go to the next level');
        }

        $this->level[] = $patternKey;
    }

    private function revertOneLevel(): void
    {
        if (!count($this->level)) {
            throw ArrayFailedValidation::make($this->level, 'Out of levels');
        }
        array_pop($this->level);
    }

    /**
     * Get all combinations of multiple arrays (preserves keys)
     *
     * @link https://gist.github.com/cecilemuller/4688876
     *
     * @param array $source [dataKey => [dataPatternKey,...]]
     */
    private function getCombinations(array $source): array
    {
        $result = [[]];
        foreach ($source as $property => $property_values) {
            $tmp = [];
            foreach ($result as $result_item) {
                foreach ($property_values as $property_value) {
                    $tmp[] = array_merge($result_item, [$property => $property_value]);
                }
            }
            $result = $tmp;
        }
        return $result == [[]] ? [] : $result;
    }

    private function quantityMatch(string $pattern, int $count): bool
    {
        $operand = $this->tokenize($pattern);
        foreach ($operand->tokens() as $token) {
            if (!$token instanceof TokenQuantifier) {
                continue;
            }
            return $count >= $token->min() && $count <= $token->max();
        }
        return $count === 1; // expected exactly one by default
    }
}
