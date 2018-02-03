<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 03/02/2018
 */
declare(strict_types=1);


namespace PASVL\Traverser\VO;

use PASVL\ValidatorLocator\ValidatorLocator;

/**
 * This service has all parsed patterns and can answer yes or no if given data matched given pattern
 * Internally it parses validator and performs calls
 * @package PASVL\Traverser\VO
 */
class PatternValidator
{
    /** @var ParsedPatterns */
    private $parsedPatterns;
    /** @var ValidatorLocator */
    private $validatorLocator;

    /**
     * Traverser constructor.
     * @param ValidatorLocator $validatorLocator
     */
    public function __construct(ValidatorLocator $validatorLocator)
    {
        $this->parsedPatterns   = new ParsedPatterns();
        $this->validatorLocator = $validatorLocator;
    }

    /**
     * Detect if given data matches given pattern string
     *
     * @param $data
     * @param mixed $pattern
     * @return bool
     * @throws \Exception
     */
    public function match($data, $pattern): bool
    {
        $this->parsedPatterns->put($pattern);

        $mainValidator      = $this->parsedPatterns->get($pattern)->getMainValidator();
        $mainValidatorClass = $this->validatorLocator->getValidatorClass($mainValidator->getName());

        $matched = call_user_func_array(
            $mainValidatorClass,
            array_merge([$data], $mainValidator->getArguments())
        );

        foreach ($this->parsedPatterns->get($pattern)->getSubValidators() as $subValidator) {
            $matched = $matched && call_user_func_array(
                    [$mainValidatorClass, $subValidator->getName()],
                    array_merge([$data], $subValidator->getArguments())
                );

            if (!$matched) {
                break;
            }
        }

        return $matched;
    }

    /**
     * Detects if given count matches allowed boundaries of the pattern
     *
     * @param mixed $pattern
     * @param int $count
     * @return bool
     */
    public function quantityMatch($pattern, int $count): bool
    {
        return $this->parsedPatterns->get($pattern)->getQuantifier()->isValidQuantity($count);
    }
}