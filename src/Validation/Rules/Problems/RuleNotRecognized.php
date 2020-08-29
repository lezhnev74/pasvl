<?php
declare(strict_types=1);

namespace PASVL\Validation\Rules\Problems;

class RuleNotRecognized extends \RuntimeException
{
    /** @var string */
    public $rule;

    public function __construct(string $rule)
    {
        parent::__construct(sprintf("Rule [%s] not recognized (check the rule locator)", $rule));
        $this->rule = $rule;
    }


}