<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 03/02/2018
 */
declare(strict_types=1);


namespace PASVL\Traverser\VO;

use PASVL\Traverser\DataNoMatching;
use PASVL\Traverser\FailReport;
use PASVL\ValidatorLocator\ValidatorLocator;


/**
 * Represents a level of data to validate against a pattern
 * @package PASVL\Traverser\VO
 */
class Traverser
{

    /** @var PatternValidator */
    private $validator;

    /**
     * Traverser constructor.
     * @param ValidatorLocator $locator
     */
    public function __construct(ValidatorLocator $locator)
    {
        $this->validator = new PatternValidator($locator);
    }

    /**
     * Validate data
     *
     * @throws FailReport
     * @param array $patterns
     * @param iterable $data
     */
    public function match(array $patterns, iterable $data)
    {
        try {
            $this->matchLevel($data, $patterns);
        } catch (DataNoMatching $e) {

            $failed_pattern    = $patterns;
            $pattern_key_chain = [];
            $data_key_chain    = [];

            throw new FailReport(
                $e->getReason(),
                $e->getKey(),
                $e->getValue(),
                $failed_pattern,
                $data_key_chain,
                $pattern_key_chain
            );
        }
    }

    /**
     * Return bool, throw no exception
     *
     * @param array $patterns
     * @param iterable $data
     * @return bool
     */
    public function check(array $patterns, iterable $data): bool
    {
        try {
            $this->match($patterns, $data);
            return true;
        } catch (FailReport $e) {
            return false;
        }
    }

    /**
     * @param iterable $data
     * @param array $patterns
     * @return void
     * @throws \Exception
     */
    protected function matchLevel(iterable $data, array $patterns)
    {

        // Optimization: analyze explicit keys first
        uksort($patterns, function ($key1, $key2) {
            return (int)(
                substr((string)$key1, 0, 1) == ":" &&
                substr((string)$key2, 0, 1) != ":"
            );
        });

        // Collects all matching patterns for each dataKey: [ dataKey => patternKey[] ]
        $dataKeyToPatternKeysMatch = [];
        foreach ($data as $dataKey => $dataValue) {
            // 1. Find perspective pattern keys
            $perspectivePatternKeys = $this->findMatchedPatterns($dataKey, array_keys($patterns));
            if (!count($perspectivePatternKeys)) {
                throw new DataNoMatching($dataKey, $dataValue, FailedReason::fromFailedKey());
            }


            // 2. Filter perspective pattern keys by validating data value against pattern's corresponding value
            $perspectivePatternKeys = $this->filterPerspectiveKeysByMatchingValues(
                $perspectivePatternKeys,
                $patterns,
                $dataValue
            );


            if (!count($perspectivePatternKeys)) {
                throw new DataNoMatching(
                    $dataKey,
                    $dataValue,
                    FailedReason::fromFailedValue()
                );
            }
            $dataKeyToPatternKeysMatch[$dataKey] = $perspectivePatternKeys;
        }

        // 3. Make combinations of data keys and pattern keys and filter those that match pattern's quantifiers
        // Ok we have found patterns that match both keys and values, but now we need to validate it against quantity expectations
        $combinations = array_filter($this->getCombinations($dataKeyToPatternKeysMatch),
            function ($combination) {
                // validate this combination against patterns' quantifiers
                foreach (array_count_values($combination) as $patternKey => $count) {
                    if (!$this->validator->quantityMatch($patternKey, $count)) {
                        return false;
                    }
                }

                return true;
            });

        // 4. For each matched combination make sure that all pattern keys fulfilled expectations
        // all pattern keys must have fulfilled expectations
        // otherwise this level of data does not match given pattern
        $patternKeyFulfillmentMap = array_fill_keys(array_keys($patterns), false);
        foreach ($patternKeyFulfillmentMap as $patternKey => $fulfillmentValue) {
            // if a patternKey was not selected for data key (within combinations)
            // and it does not have 0-quantity expectations, then this patternKey has unfulfilled expectations
            $patternKeyIsInCombination = false;
            foreach ($combinations as $combination) {
                foreach ($combination as $dataKey => $validPatternKey) {
                    $patternKeyIsInCombination = $validPatternKey == $patternKey;
                    if ($patternKeyIsInCombination) {
                        break 2;
                    };
                }
            }

            $patternKeyWasPerspective = false;
            foreach ($dataKeyToPatternKeysMatch as $dataKey => $perspectivePatternKeys) {
                $patternKeyWasPerspective = $patternKeyWasPerspective || in_array($patternKey, $perspectivePatternKeys);
            }

            $patternKeyFulfillmentMap[$patternKey] =
                $patternKeyIsInCombination ||
                (
                    !$patternKeyWasPerspective && $this->validator->quantityMatch($patternKey, 0)
                );
        }

        if (array_sum($patternKeyFulfillmentMap) != count($patternKeyFulfillmentMap)) {
            // fulfillment was not met
            throw new DataNoMatching("", "", FailedReason::fromFailedKeyQuantity());
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
            if ($this->validator->match($data, $pattern)) {
                $matchedPatterns[$pattern_key] = $pattern;
            }
        }

        return $matchedPatterns;
    }

    /**
     * Return perspective pattern keys which corresponding pattern values match data value
     *
     * @param $perspectivePatternKeys
     * @param $patterns
     * @param $dataValue
     */
    function filterPerspectiveKeysByMatchingValues(
        $perspectivePatternKeys,
        $patterns,
        $dataValue
    ): array {

        return array_filter($perspectivePatternKeys, function ($patternKey) use ($dataValue, $patterns) {

            $patternMatched = false;
            $patternValue   = $patterns[$patternKey]; // this is just a link, should not be too heavy on memory

            if (is_array($patternValue)) {

                // pattern is an array, value must be array as well
                if (!is_iterable($dataValue)) {

                    // value does not match the pattern (array expected)
                    // this is not the right patternValue-dataValue pair

                } else {

                    // go down and analyze next array's level
                    try {
                        $this->matchLevel($dataValue, $patternValue);
                        $patternMatched = true;
                    } catch (DataNoMatching $e) {
                        // This means that this pattern cannot be matched against given value,
                        // this exception should not bubble up, it just means that pattern did not match
                    }

                }

            } else {

                // the pattern is not an array and value is also not an array, validate one against another
                $matched_patterns = $this->findMatchedPatterns($dataValue, [$patterns[$patternKey]]);

                // value matched pattern
                $patternMatched = (bool)count($matched_patterns);

            }

            return $patternMatched;
        });

    }

    /**
     * Get all combinations of multiple arrays (preserves keys)
     *
     * @link https://gist.github.com/cecilemuller/4688876
     *
     * @param array $source [dataKey => [dataPatternKey,...]]
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