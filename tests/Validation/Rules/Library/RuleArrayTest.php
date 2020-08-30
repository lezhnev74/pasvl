<?php

declare(strict_types=1);

namespace PASVL\Tests\Validation\Rules\Library;

use PASVL\Validation\Rules\Library\RuleArray;
use PASVL\Validation\Rules\Problems\RuleFailed;
use PHPUnit\Framework\TestCase;

class RuleArrayTest extends TestCase
{
    private const SHOULD_SUCCEED = true;
    private const SHOULD_FAIL    = false;

    public function data(): array
    {
        return [
            ['test', [], [1, 2], self::SHOULD_SUCCEED],
            ['test', [], 'string', self::SHOULD_FAIL],
            ['count', [1], ['a'], self::SHOULD_SUCCEED],
            ['count', [1], [], self::SHOULD_FAIL],
            ['keys', [0, 'a'], [1, 'a' => 2], self::SHOULD_SUCCEED],
            ['keys', [0, 'a'], [1, 2], self::SHOULD_FAIL],
            ['min', [1], [1, 2], self::SHOULD_SUCCEED],
            ['min', [1], [], self::SHOULD_FAIL],
            ['max', [1], [], self::SHOULD_SUCCEED],
            ['max', [1], ['a', 'b'], self::SHOULD_FAIL],
            ['between', [1, 2], ['a', 'b'], self::SHOULD_SUCCEED],
            ['between', [1, 2], ['a', 'b', 'c'], self::SHOULD_FAIL],
        ];
    }

    /** @dataProvider data */
    public function testInput(string $method, array $methodArguments, $value, bool $expectedSuccess)
    {
        $rule = new RuleArray();
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
