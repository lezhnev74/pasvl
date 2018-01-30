<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 01/01/2018
 */

namespace PASVL\Pattern;


use PASVL\Pattern\VO\Quantifier;
use PASVL\Pattern\VO\Validator;

class Pattern
{
    /** @var string */
    protected $original_pattern;
    /** @var bool */
    protected $throw_on_invalid_pattern;
    /**
     * The point is to pass any data that can be used inside validators to recognize specific conditions of validation.
     * For example, "production" or "local".
     * @var mixed
     */
    protected $context;

    /** @var array */
    protected $validators = [];
    /** @var Quantifier */
    protected $quantifier;

    /**
     * Pattern constructor.
     * @param string|int $original_pattern
     * @param mixed $context
     * @param bool $throw_on_invalid_pattern
     */
    public function __construct($original_pattern, $context = null, $throw_on_invalid_pattern = false)
    {
        $this->original_pattern         = is_int($original_pattern) ? $original_pattern : trim($original_pattern);
        $this->context                  = $context;
        $this->throw_on_invalid_pattern = $throw_on_invalid_pattern;

        $this->parse($original_pattern);
    }

    /**
     * @return string
     */
    public function getOriginalPattern(): string
    {
        return $this->original_pattern;
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    public function getMainValidator(): ?Validator
    {
        return count($this->validators) ? $this->validators[0] : null;
    }

    public function getSubValidators(): array
    {
        return array_slice($this->validators, 1);
    }

    /**
     * @return Quantifier
     */
    public function getQuantifier(): Quantifier
    {
        return $this->quantifier;
    }


    /**
     * Analyze given pattern and populate internal values
     *
     * @param string $user_pattern
     * @throws InvalidPattern
     */
    protected function parse(string $user_pattern)
    {
        $label_regexp_pattern        = "[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*";
        $quantifier_regexp_pattern   = "(?'quantifier'(?'single_quantifier'[\*|\?|!])|(?'interval_quantifier'{(?'quantifier_min_boundary'\d+)(?'quantifier_max_boundary',\d*)?}))";
        $validator_arguments_pattern = "(\((?'arguments'.+,?)+\))?";
        $full_validator              = "(?'validator':(?'name'$label_regexp_pattern)$validator_arguments_pattern)";
        $full_regexp_pattern         = "#$full_validator*?$quantifier_regexp_pattern?#";

        preg_match_all($full_regexp_pattern, $user_pattern, $matches, PREG_SET_ORDER, 0);

        $this->fillQuantifierFromRegexpMatches($matches);
        if ($this->isUserPatternHasValidLabels($user_pattern, $matches)) {
            $this->fillValidatorsFromRegexpMatches($matches);
        } else {
            $this->fillDefaultValidatorFromRegexpMatches($matches);
        }

        // Final checks

        // Explicit key name cannot be with multiple quantifier, unless the name is empty
        if ($this->quantifier->isMultiple() &&
            $this->getMainValidator()->getName() == "key" &&
            $this->getMainValidator()->getArguments() != ['']
        ) {
            throw new InvalidPattern("Direct name cannot be used along with * quantifier");
        }

    }

    /**
     * If string starts with a ":" - that means validator labels are used.
     * Make sure pattern is valid, otherwise throw an exception or just return boolean result
     *
     * @param string $user_pattern
     * @param array $matches
     * @return bool
     * @throws InvalidPattern
     */
    protected function isUserPatternHasValidLabels(string $user_pattern, array $matches): bool
    {
        $starts_with_colon = strpos($user_pattern, ":") === 0;

        if (!$starts_with_colon) {
            return false;
        }

        foreach ($matches as $match) {
            $user_pattern = str_replace($match[0], "", $user_pattern);
        }
        $remaining_labels = trim($user_pattern);

        if (
            $starts_with_colon &&
            strlen($remaining_labels) &&
            $this->throw_on_invalid_pattern
        ) {
            throw new InvalidPattern("Pattern has invalid labels: " . $remaining_labels);
        }

        return !strlen($remaining_labels);
    }

    /**
     * Populate internal value with Validator objects from pattern parsing matches
     *
     * @param array $matches
     */
    protected function fillValidatorsFromRegexpMatches(array $matches): void
    {
        $this->validators = array_values(
            array_map(function ($match) {
                return new Validator(
                    $match['name'],
                    array_filter(explode(",", $match['arguments'] ?? ""), 'strlen')
                );
            }, array_filter($matches, function ($match) {
                return isset($match['validator']) && strlen($match['validator']);
            }))
        );
    }

    protected function fillDefaultValidatorFromRegexpMatches(array $matches)
    {
        foreach ($matches as $match) {
            if (isset($match['quantifier']) && strlen($match['quantifier'])) {
                $pattern_without_quantifier = str_replace($match['quantifier'], "", $this->original_pattern);

                if (!strlen(trim($pattern_without_quantifier))) {
                    $this->validators[] = new Validator("any"); // "*" or "?" or "!" means any value with quantifier
                } else {
                    $this->validators[] = new Validator("key", [$pattern_without_quantifier]);
                }
                return;
            }
        }

        // default explicit key
        $this->validators[] = new Validator("key", [$this->original_pattern]);
    }

    /**
     * Fill internal value with parsed Quantifier object
     *
     * @param array $matches
     */
    function fillQuantifierFromRegexpMatches(array $matches): void
    {
        $quantifier_options = [1, 1];
        foreach ($matches as $match) {
            if (isset($match['quantifier']) && strlen($match['quantifier'])) {
                if (isset($match['single_quantifier']) && strlen($match['single_quantifier'])) {
                    switch ($match['single_quantifier']) {
                        case "*":
                            $quantifier_options = [0, PHP_INT_MAX];
                            break;
                        case "?":
                            $quantifier_options = [0, 1];
                            break;
                        case "!":
                        default:
                            $quantifier_options = [1, 1];
                            break;
                    }
                } else {
                    if (isset($match['quantifier_max_boundary'])) {

                        $max_boundary = ltrim($match['quantifier_max_boundary'], ",");
                        if ($max_boundary == "") {
                            $max_boundary = PHP_INT_MAX;
                        } else {
                            $max_boundary = (int)$max_boundary;
                        }

                        $quantifier_options = [
                            (int)$match['quantifier_min_boundary'],
                            $max_boundary,
                        ];
                    } else {
                        $quantifier_options = [
                            (int)$match['quantifier_min_boundary'],
                            (int)$match['quantifier_min_boundary'],
                        ];
                    }
                }
                break;
            }
        }
        try {
            $this->quantifier = new Quantifier($quantifier_options[0], $quantifier_options[1]);
        } catch (\InvalidArgumentException $e) {
            throw new InvalidPattern($e->getMessage(), 0, $e);
        }
    }

}