<?php
declare(strict_types=1);

namespace PASVL\Validation;

use PASVL\Parsing\Compound\CompoundRuleParser;
use PASVL\Parsing\Compound\Tokens\Operand;
use PASVL\Parsing\Compound\Tokens\TokenSimpleOperand;
use PASVL\Parsing\Simple\Tokens\TokenRule;
use PASVL\Validation\Matcher\TokensMatcher;
use PASVL\Validation\Problems\StringValidationFailed;
use PASVL\Validation\Rules\RuleLocator;

class StringValidator extends Validator
{
    /** @var Operand */
    private $tokenizedPattern;
    /** @var string */
    private $pattern;

    public function __construct(RuleLocator $locator, string $pattern)
    {
        parent::__construct($locator);
        $this->pattern = $pattern;

        // Edge case: exact match
        if (!in_array(trim($pattern)[0], [":", "("])) {
            $this->tokenizedPattern = TokenSimpleOperand::make([TokenRule::make('exact', [$pattern])]);
        } else {
            $this->tokenizedPattern = (new CompoundRuleParser())->parse($pattern)[0];
        }
    }


    public function validate($data): void
    {
        $matcher = new TokensMatcher([$this->tokenizedPattern], $this->locator());
        $r = $matcher->match($data);
        if (!$r->isPassed()) {
            throw new StringValidationFailed($r->reason()->getMessage(), $r->reason()->getCode(), $r->reason());
        }
    }
}