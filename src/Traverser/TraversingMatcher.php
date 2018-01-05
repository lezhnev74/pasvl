<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 03/01/2018
 */

namespace PASVL\Traverser;


use PASVL\Pattern\Pattern;
use PASVL\Validator\InvalidData;
use PASVL\ValidatorLocator\ValidatorLocator;

class TraversingMatcher
{
    /** @var \SplQueue */
    private $keysQueue;
    /** @var Pattern[] */
    private $patterns = [];
    /** @var ValidatorLocator */
    private $validatorLocator;

    /**
     * TraversingMatcher constructor.
     * @param ValidatorLocator $validatorLocator
     */
    public function __construct(ValidatorLocator $validatorLocator)
    {
        $this->validatorLocator = $validatorLocator;
        $this->keysQueue = new \SplQueue();
    }

    /**
     * Recursively validates data-structure against a pattern and throws a report if data mismatch a pattern
     *
     * @param array $patterns
     * @param iterable $data
     * @throws FailReport
     */
    function match(array $patterns, iterable $data): void
    {
        try {
            $this->matchDataToPattern($patterns, $data);
        } catch (DataNoMatching $e) {

            $failed_pattern = $patterns;
            $pattern_key_chain = [];
            $data_key_chain = [];
            foreach ($this->keysQueue as $queuePosition) {
                $pattern_key_chain[] = $queuePosition[0];
                $failed_pattern = $failed_pattern[$queuePosition[0]];

                $data_key_chain[] = $queuePosition[1];
            }

            throw new FailReport(
                (int)$e->isValueType(),
                $e->getKey(),
                $e->getValue(),
                $failed_pattern,
                $data_key_chain,
                $pattern_key_chain
            );

        }
    }

    /**
     * Analyze one level of data
     *
     * @param array $patterns
     * @param iterable $data
     * @throws DataMismatchedPattern
     */
    protected function matchDataToPattern(array $patterns, iterable $data)
    {
        // Optimization: analyze explicit keys first
        uksort($patterns, function ($key1, $key2) {
            return (int)(substr($key1, 0, 1) == ":" && substr($key2, 0, 1) != ":");
        });

        // Collects all matching patterns for each dataKey: [ dataKey => patternKey[] ]
        $dataToPatternMatches = [];

        foreach ($data as $dataKey => $dataValue) {

            // 1. Find matching patterns for dataKey
            $perspectivePatternKeys = $this->findMatchedPatterns($dataKey, array_keys($patterns));


            if (!count($perspectivePatternKeys)) {
                throw new DataNoMatching(
                    $dataKey,
                    $dataValue,
                    DataMismatchedPattern::MISMATCHED_KEY
                );
            }

            // 2. Validate dataValue against promising patterns and discard those which does not fit
            $perspectivePatternKeys = array_filter($perspectivePatternKeys,
                function ($patternKey) use ($dataKey, $dataValue, $patterns) {
                    $patternMatched = false;
                    if (is_array($patterns[$patternKey])) {
                        // pattern is an array, value must be array as well
                        if (!is_iterable($dataValue)) {
                            // value does not match the pattern (array expected)
                            // this is not the right pattern pair
                        } else {
                            // go down and analyze next array level
                            $this->keysQueue->push([$patternKey, $dataKey]);
                            $this->matchDataToPattern($patterns[$patternKey], $dataValue);
                            $this->keysQueue->pop();

                            $patternMatched = true;
                        }
                    } else {
                        // the pattern is not an array and value is also not an array, validate one against another
                        $matched_patterns = $this->findMatchedPatterns($dataValue, [$patterns[$patternKey]]);

                        // value matched pattern
                        $patternMatched = (bool)count($matched_patterns);
                    }

                    return $patternMatched;
                }
            );

            $dataToPatternMatches[$dataKey] = $perspectivePatternKeys;
        }

        $combinations = array_filter($this->getCombinations($dataToPatternMatches), function ($combination) {
            // validate this combination against patterns' quantifiers
            foreach (array_count_values($combination) as $patternKey => $count) {
                if (!$this->patterns[$patternKey]->getQuantifier()->isValidQuantity($count)) {
                    return false;
                }
            }

            return true;
        });

        if (!count($combinations)) {
            // no matching patterns found for this data kay->value pair
            throw new DataNoMatching(
                $dataKey,
                $dataValue,
                DataNoMatching::MISMATCHED_VALUE
            );
        }
    }

    /**
     * Validate a given value against multiple patterns
     *
     * @param scalar $data
     * @param array $patterns
     * @return array
     * @throws \Exception
     */
    protected function findMatchedPatterns($data, array $patterns): array
    {
        $matchedPatterns = [];
        foreach ($patterns as $pattern_key => $pattern) {
            // parse pattern string once
            if (!isset($this->patterns[$pattern])) {
                $this->patterns[$pattern] = new Pattern($pattern, null, true);
            }
            $mainValidator = $this
                ->validatorLocator
                ->getValidatorClass($this->patterns[$pattern]->getMainValidator()->getName());

            try {
                call_user_func_array(
                    $mainValidator,
                    array_merge([$data], $this->patterns[$pattern]->getMainValidator()->getArguments())
                );

                foreach ($this->patterns[$pattern]->getSubValidators() as $subValidator) {
                    call_user_func_array(
                        [$mainValidator, $subValidator->getName()],
                        array_merge([$data], $subValidator->getArguments())
                    );
                }

                $matchedPatterns[$pattern_key] = $pattern;

            } catch (InvalidData $e) {
                // data did not match current pattern
                // continue to the next one
            }
        }

        return $matchedPatterns;
    }


    /**
     * Get all combinations of multiple arrays (preserves keys)
     * @link https://gist.github.com/cecilemuller/4688876
     *
     * @param array $source
     * @return array
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
        return $result;
    }
}