<?php
declare(strict_types=1);

namespace PASVL\Validation\Rules\Problems;

class SubRuleNotRecognized extends \RuntimeException
{
    /** @var string */
    public $rule;

    public function __construct(string $rule, string $subRule)
    {
        parent::__construct(
            sprintf("SubRule [%s] of rule [%s] not recognized (probably not implemented)", $subRule, $rule)
        );
        $this->rule = $rule;
    }


}