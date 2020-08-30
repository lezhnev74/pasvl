<?php

declare(strict_types=1);

namespace PASVL\Tests\Validation\Rules\Library;

use PASVL\Validation\Rules\Library\RuleNumber;
use PASVL\Validation\Rules\Problems\RuleFailed;
use PHPUnit\Framework\TestCase;

class RuleNumberTest extends TestCase
{
    private const SHOULD_SUCCEED = true;
    private const SHOULD_FAIL    = false;

    public function data(): array
    {
        return [
            ['test', [], .0, self::SHOULD_SUCCEED],
            ['test', [], 'string', self::SHOULD_FAIL],
            ['test', [12], 12, self::SHOULD_SUCCEED],
            ['test', [12], 13, self::SHOULD_FAIL],
            ['int', [], 1, self::SHOULD_SUCCEED],
            ['int', [], 1.0, self::SHOULD_FAIL],
            ['float', [], .0, self::SHOULD_SUCCEED],
            ['float', [], 1, self::SHOULD_SUCCEED],
            ['between', [1, 2], 1.5, self::SHOULD_SUCCEED],
            ['between', [1, 2], 3, self::SHOULD_FAIL],
            ['min', [0.98], .99, self::SHOULD_SUCCEED],
            ['min', [1.0], .99, self::SHOULD_FAIL],
            ['max', [0.98], .97, self::SHOULD_SUCCEED],
            ['max', [1.0], 1.1, self::SHOULD_FAIL],
            ['positive', [], .1, self::SHOULD_SUCCEED],
            ['positive', [], 0, self::SHOULD_SUCCEED],
            ['positive', [], -.1, self::SHOULD_FAIL],
            ['negative', [], .1, self::SHOULD_FAIL],
            ['negative', [], 0, self::SHOULD_FAIL],
            ['negative', [], -.1, self::SHOULD_SUCCEED],
            ['in', [1, 2], 1, self::SHOULD_SUCCEED],
            ['in', [1, 2], 1., self::SHOULD_SUCCEED],
            ['in', [1, 2], 3, self::SHOULD_FAIL],
            ['inStrict', [1, 2], 1., self::SHOULD_FAIL],
        ];
    }

    /** @dataProvider data */
    public function testInput(string $method, array $methodArguments, $value, bool $expectedSuccess)
    {
        $rule = new RuleNumber();
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
