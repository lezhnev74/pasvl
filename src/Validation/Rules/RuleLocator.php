<?php

declare(strict_types=1);

namespace PASVL\Validation\Rules;

use PASVL\Validation\Rules\Problems\RuleNotRecognized;

class RuleLocator
{
    private $libraryPath = __DIR__ . DIRECTORY_SEPARATOR . "Library";

    public function locate(string $ruleName): Rule
    {
        $fqcn = sprintf("\PASVL\Validation\Rules\Library\Rule%s", ucfirst($ruleName));
        if (!class_exists($fqcn)) throw new RuleNotRecognized($ruleName);
        return $this->makeRule($fqcn);
    }

    private function makeRule(string $fqcn): Rule
    {
        return new $fqcn();
    }
}