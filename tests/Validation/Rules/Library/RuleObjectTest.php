<?php

declare(strict_types=1);

namespace PASVL\Tests\Validation\Rules\Library;

use PASVL\Validation\Rules\Library\RuleObject;
use PASVL\Validation\Rules\Problems\RuleFailed;
use PHPUnit\Framework\TestCase;

class RuleObjectTest extends TestCase
{
    private const SHOULD_SUCCEED = true;
    private const SHOULD_FAIL    = false;

    public function data(): array
    {
        return [
            ['test', [], new \stdClass(), self::SHOULD_SUCCEED],
            ['test', [], 'string', self::SHOULD_FAIL],
            ['instance', [\Exception::class], new \Exception(), self::SHOULD_SUCCEED],
            ['instance', [\Exception::class], new \stdClass(), self::SHOULD_FAIL],
            [
                'propertyExists',
                ['x'],
                new class {
                    public $x;
                },
                self::SHOULD_SUCCEED,
            ],
            [
                'propertyExists',
                ['x'],
                new class {
                    public $y;
                },
                self::SHOULD_FAIL,
            ],
            [
                'methodExists',
                ['x'],
                new class {
                    public function x() { }
                },
                self::SHOULD_SUCCEED,
            ],
            [
                'methodExists',
                ['x'],
                new class {
                },
                self::SHOULD_FAIL,
            ],
        ];
    }

    /** @dataProvider data */
    public function testInput(string $method, array $methodArguments, $value, bool $expectedSuccess)
    {
        $rule = new RuleObject();
        $rule->putValue($value);

        try {
            call_user_func_array([$rule, $method], $methodArguments);
            if (!$expectedSuccess) $this->fail('Expected to fail');
        } catch (RuleFailed $e) {
            if ($expectedSuccess) $this->fail('Expected to succeed');
        }

        $this->addToAssertionCount(1);
    }
}
