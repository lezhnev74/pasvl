<?php

declare(strict_types=1);

namespace PASVL\Tests\Validation\Rules\Library;

use PASVL\Validation\Rules\Library\RuleString;
use PASVL\Validation\Rules\Problems\RuleFailed;
use PHPUnit\Framework\TestCase;

class RuleStringTest extends TestCase
{
    private const SHOULD_SUCCEED = true;
    private const SHOULD_FAIL    = false;

    public function data(): array
    {
        return [
            ['test', [], 'hello', self::SHOULD_SUCCEED],
            ['test', [], 123, self::SHOULD_FAIL],
            ['test', [], [1,2], self::SHOULD_FAIL],
            ['test', ['hello'], 'hello', self::SHOULD_SUCCEED],
            ['test', ['not hello'], 'hello', self::SHOULD_FAIL],
            ['test', ['not hello'], 'hello', self::SHOULD_FAIL],
            ['in', ['hello', 'world'], 'hello', self::SHOULD_SUCCEED],
            ['in', ['hello', 'world'], 'today', self::SHOULD_FAIL],
            ['len', [3], 'abc', self::SHOULD_SUCCEED],
            ['len', [3], 'ab', self::SHOULD_FAIL],
            ['min', [3], 'abc', self::SHOULD_SUCCEED],
            ['min', [3], 'ab', self::SHOULD_FAIL],
            ['max', [3], 'abc', self::SHOULD_SUCCEED],
            ['max', [3], 'abcd', self::SHOULD_FAIL],
            ['between', [1, 3], 'abc', self::SHOULD_SUCCEED],
            ['between', [1, 3], 'abcd', self::SHOULD_FAIL],
            ['regexp', ['^[a]+$'], 'aa', self::SHOULD_SUCCEED],
            ['regexp', ['^[a]+$'], 'ab', self::SHOULD_FAIL],
            ['contains', ['bb'], 'abba', self::SHOULD_SUCCEED],
            ['contains', ['bb'], 'ab', self::SHOULD_FAIL],
            ['starts', ['na'], 'name', self::SHOULD_SUCCEED],
            ['starts', ['na'], 'surname', self::SHOULD_FAIL],
            ['ends', ['e'], 'name', self::SHOULD_SUCCEED],
            ['ends', ['e'], 'job', self::SHOULD_FAIL],
            ['url', [], 'http://example.org', self::SHOULD_SUCCEED],
            ['url', [], '123.com', self::SHOULD_FAIL],
            ['json', [], '{"a":12}', self::SHOULD_SUCCEED],
            ['json', [], "{'a':12}", self::SHOULD_FAIL],
            ['email', [], "abc@example.com", self::SHOULD_SUCCEED],
            ['email', [], "@example.com", self::SHOULD_FAIL],
        ];
    }

    /** @dataProvider data */
    public function testStringRule(string $method, array $methodArguments, $value, bool $expectedSuccess)
    {
        $rule = new RuleString();
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
