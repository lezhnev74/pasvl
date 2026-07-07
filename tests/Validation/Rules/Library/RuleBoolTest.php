<?php

declare(strict_types=1);

namespace PASVL\Tests\Validation\Rules\Library;

use PASVL\Validation\Rules\Library\RuleBool;
use PASVL\Validation\Rules\Problems\RuleFailed;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RuleBoolTest extends TestCase
{
    private const SHOULD_SUCCEED = true;
    private const SHOULD_FAIL    = false;

    public static function data(): array
    {
        return [
            ['test', [], true, self::SHOULD_SUCCEED],
            ['test', [], 'string', self::SHOULD_FAIL],
            ['test', [true], true, self::SHOULD_SUCCEED],
            ['test', [true], false, self::SHOULD_FAIL],
        ];
    }

    #[DataProvider('data')]
    public function testInput(string $method, array $methodArguments, bool|string $value, bool $expectedSuccess): void
    {
        $rule = new RuleBool();
        $rule->putValue($value);

        try {
            call_user_func_array([$rule, $method], $methodArguments);
            if (!$expectedSuccess) {
                $this->fail('Expected to fail');
            }
        } catch (RuleFailed) {
            if ($expectedSuccess) {
                $this->fail('Expected to succeed');
            }
        }

        $this->addToAssertionCount(1);
    }
}
