<?php
declare(strict_types=1);

namespace PASVL\Tests\Validation\Rules;

use PASVL\Validation\Rules\Library\RuleString;
use PASVL\Validation\Rules\RuleLocator;
use PHPUnit\Framework\TestCase;

class RuleLocatorTest extends TestCase
{
    public function testICanBuildDefaultRule(): void
    {
        $rule = "string";
        $locator = new RuleLocator();
        $instance = $locator->locate($rule);
        $this->assertInstanceOf(RuleString::class, $instance);
    }
}
