<?php
declare(strict_types=1);
include(__DIR__ . "/../vendor/autoload.php");

use PASVL\Validation\Problems\ArrayFailedValidation;
use PASVL\Validation\Rules\Problems\RuleFailed;
use PASVL\Validation\Rules\Problems\RuleNotRecognized;
use PASVL\Validation\Rules\Rule;
use PASVL\Validation\Rules\RuleLocator;
use PASVL\Validation\ValidatorBuilder;

class RuleAvailable extends Rule
{
    public function test(...$args): void
    {
        if ($this->value !== "laptop") throw new RuleFailed("only laptops are available");;
    }
}

class MyLocator extends RuleLocator
{
    public function locate(string $ruleName): Rule
    {
        try {
            return parent::locate($ruleName);
        } catch (RuleNotRecognized $e) {
            if ($ruleName === "available") return new RuleAvailable(); // this is a new rule
            throw $e;
        }
    }

}

// Validate data:
$pattern = ["*" => ":string and :available"];
$builder = ValidatorBuilder::forArray($pattern)->withLocator(new MyLocator());
$validator = $builder->build();

try {
    $validator->validate(['laptop', 'notebook']);
} catch (ArrayFailedValidation $e) {
    echo "failed: " . $e->getMessage() . "\n";
    echo "note: value 'notebook' did not match 'available' rule";
}