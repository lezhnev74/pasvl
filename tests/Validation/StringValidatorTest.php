<?php

declare(strict_types=1);

namespace PASVL\Tests\Validation;

use PASVL\Validation\Problems\StringValidationFailed;
use PASVL\Validation\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

class StringValidatorTest extends TestCase
{
    private const EXPECT_PASS = true;
    private const EXPECT_FAIL = false;

    public function data(): array
    {
        return [
            // Unicode
            ['Строка', ':string', self::EXPECT_PASS],
            ['Строка', ':string("Строка")', self::EXPECT_PASS],
            // edge case: the exact matching
            ['exact', 'exact', self::EXPECT_PASS],
            ['not exact', 'exact', self::EXPECT_FAIL],
            [':whatever :exact :value', ":exact(':whatever :exact :value')", self::EXPECT_PASS],
            // pattern based matching
            ['good', ':string', self::EXPECT_PASS],
            ['good', ':string("good")', self::EXPECT_PASS],
            ['good', ':string("not good")', self::EXPECT_FAIL],
            [null, ':string?("not good")', self::EXPECT_PASS],
            [123, ':string', self::EXPECT_FAIL],
            ['long string', ':string :between(0,3)', self::EXPECT_FAIL],
            ['long string', ':string :between(0,3) or :string :between(11,11)', self::EXPECT_PASS],
        ];
    }

    /** @dataProvider data */
    public function testItValidatesStrings($input, string $pattern, bool $isValid): void
    {
        if (!$isValid) $this->expectException(StringValidationFailed::class);

        $v = ValidatorBuilder::forString($pattern)->build();
        $v->validate($input);

        if ($isValid) $this->addToAssertionCount(1);
    }
}
