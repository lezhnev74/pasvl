<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 03/01/2018
 */

namespace PASVL\Traverser;


use PASVL\Pattern\Pattern;
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
        $this->preparePatterns($patterns);

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
     * Go through the pattern definition and validate/parse all the pattern strings
     * @param array $patterns
     */
    protected function preparePatterns(array $patterns)
    {
        $parseString = function ($pattern) {
            if (!isset($this->patterns[$pattern])) {
                $this->patterns[$pattern] = new Pattern($pattern, null, true);
            }
        };

        // parse pattern string once
        foreach ($patterns as $patternKey => $patternValue) {
            // keys
            $parseString($patternKey);

            // values
            if (is_iterable($patternValue)) {
                $this->preparePatterns($patternValue);
            } else {
                $parseString($patternValue);
            }
        }
    }

    /**
     * Analyze one level of data
     *
     * @param array $patterns
     * @param iterable $data
     * @throws DataMismatchedPattern
     * @throws DataNoMatching
     */
    protected function matchDataToPattern(array $patterns, iterable $data)
    {
        // Optimization: analyze explicit keys first
        uksort($patterns, function ($key1, $key2) {
            return (int)(substr($key1, 0, 1) == ":" && substr($key2, 0, 1) != ":");
        });

        // Edge case - empty array
        // Array will match pattern if it has dismissible keys with quantifiers: *, ?, and {0,..}
        if (!count($data)) {

            foreach ($patterns as $patternKey => $patternValue) {
                if (!$this->patterns[$patternKey]->getQuantifier()->isValidQuantity(0)) {
                    throw new DataNoMatching("", "", DataNoMatching::MISMATCHED_KEY);
                }
            }

        } else {
            // Usual case when data set is not empty
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
                throw new DataNoMatching("", "", DataNoMatching::MISMATCHED_KEY);
            }
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
//            // parse pattern string once
//            if (!isset($this->patterns[$pattern])) {
//                $this->patterns[$pattern] = new Pattern($pattern, null, true);
//            }
            $mainValidator = $this
                ->validatorLocator
                ->getValidatorClass($this->patterns[$pattern]->getMainValidator()->getName());


            $matched = call_user_func_array(
                $mainValidator,
                array_merge([$data], $this->patterns[$pattern]->getMainValidator()->getArguments())
            );

            foreach ($this->patterns[$pattern]->getSubValidators() as $subValidator) {
                $matched = $matched && call_user_func_array(
                        [$mainValidator, $subValidator->getName()],
                        array_merge([$data], $subValidator->getArguments())
                    );

                if (!$matched) {
                    break;
                }
            }

            if ($matched) {
                $matchedPatterns[$pattern_key] = $pattern;
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
        return $result == [[]] ? [] : $result;
    }
}