<?php
declare(strict_types=1);

namespace PASVL\Tests\Validation;

use PASVL\Validation\Problems\ArrayFailedValidation;
use PASVL\Validation\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

class ArrayValidatorTest extends TestCase
{
    private const EXPECT_PASS = true;
    private const EXPECT_FAIL = false;

    public function data(): array
    {
        return [
            // Simple rules
            [[], [':exact("name")?' => ':string'], self::EXPECT_PASS],
            [['name' => 'John Dutton'], ['name' => ':string'], self::EXPECT_PASS],
            [['name' => 'Kayce Dutton'], ['name' => ':string :min(20)'], self::EXPECT_FAIL],
            [['name' => 'Kayce Dutton'], ['name' => ':string :regexp("Jamie")'], self::EXPECT_FAIL],
            [['name' => 'Jamie', 'lastname' => 'Dutton'], [':string {2}' => ':string'], self::EXPECT_PASS],
            [['name' => 'Beth Dutton'], ['*' => ':string or :number'], self::EXPECT_PASS],
            // Compound rules
            [['name', 12], ['*' => ':string or :number'], self::EXPECT_PASS],
            [['12', []], ['*' => ':string and :number or :array'], self::EXPECT_PASS],
            [['12', []], ['*' => ':string and (:number or :array)'], self::EXPECT_FAIL],
            [['name', 12], ['*' => ':string or (:number and :number :int)'], self::EXPECT_PASS],
            [['name', 12], ['*' => ':string or (:number and :number :float)'], self::EXPECT_FAIL],
            // Simple quantifiers
            [
                ['name' => 'Jamie', 'lastname' => 'Dutton', 'job' => 'attorney'],
                [':string {2}' => ':string'],
                self::EXPECT_FAIL,
            ],
            [['name' => 'Beth Dutton'], [':string {2}' => ':string'], self::EXPECT_FAIL],
            [[1, 2, 3], ['*' => ':number'], self::EXPECT_PASS],
            [[1, 1], [':string' => 1], self::EXPECT_FAIL],
            [[9], ['*' => 9], self::EXPECT_PASS],
            [[3, 4], [':number *' => ':number :int'], self::EXPECT_PASS],
            [['1', '2', '3'], ['*' => ':number'], self::EXPECT_PASS],
            [['1', '1'], [':number??' => '1'], self::EXPECT_FAIL],
            // Complex quantifiers
            [
                [
                    "name" => "",
                    "lastname" => "",
                ],
                [
                    ":string :regexp('\w{0,4}') {1}" => ":any",
                    ":string :regexp('\w{0,}') {1}" => ":any",
                ],
                self::EXPECT_PASS,
            ],
            [
                ["name" => "John"],
                [':string :regexp("\w{1,4}") {1}' => ":any"],
                self::EXPECT_PASS,
            ],
            [
                [
                    "efgh" => "",
                    "ab" => "",
                    "cd" => "",
                    "a123" => "",
                ],
                [
                    ":string :len(4) {2}" => ":any",
                    ":string :len(2) {2}" => ":any",
                ],
                self::EXPECT_PASS,
            ],
            [
                [
                    [
                        'type' => 'book',
                        'title' => 'Geography book',
                        'chapters' => [
                            'eu' => ['title' => 'Europe', 'interesting' => true],
                            'as' => ['title' => 'America', 'interesting' => false],
                        ],
                    ],
                    [
                        'type' => 'book',
                        'title' => 'Foreign languages book',
                        'chapters' => [
                            'de' => ['title' => 'Deutsch'],
                        ],
                    ],
                ],
                [
                    '*' => [
                        'type' => 'book',
                        'title' => ':string :contains("book")',
                        'chapters' => [
                            ':string :len(2) {1,3}' => [
                                'title' => ':string',
                                ':exact("interesting") ?' => ':bool',
                            ],
                        ],
                    ],
                ],
                self::EXPECT_PASS,
            ],
            [
                ['name' => 'John',],
                [
                    "name" => ":string",
                    ":exact('last_name')" => ":string",
                ],
                self::EXPECT_FAIL,
            ],
            [
                [
                    "name" => "John",
                    "surname" => "McDawn",
                ],
                [
                    ":string{1,1}" => ":string",
                    ":string!" => ":string",
                ],
                self::EXPECT_PASS,
            ],
            [
                [
                    "name" => "John",
                    "surname" => "McDawn",
                ],
                [
                    "name" => "John",
                    ":string!" => ":string",
                ],
                self::EXPECT_PASS,
            ],
            [
                [
                    "object" => new \stdClass(),
                    "nullable_object" => null,
                    "nullable_array" => null,
                ],
                [
                    "object" => ":object",
                    "nullable_object" => ":object? :instance('\stdClass')",
                    "nullable_array" => ":array? :count(1)",
                ],
                self::EXPECT_PASS,
            ],
            [
                [
                    "first_name" => "Sam",
                    "last_name" => "Walberg",
                ],
                [
                    ":string :regexp('(first|last)_name') *" => ":string",
                ],
                self::EXPECT_PASS,
            ],
            [
                [
                    0 => "John",
                ],
                [
                    "*" => [
                        "name" => ":string",
                    ],
                ],
                self::EXPECT_FAIL,
            ],
        ];
    }

    /** @dataProvider data */
    public function testItValidatesArrays($input, array $pattern, bool $isValid): void
    {
        if (!$isValid) $this->expectException(ArrayFailedValidation::class);

        $v = ValidatorBuilder::forArray($pattern)->build();
        $v->validate($input);

        if ($isValid) $this->addToAssertionCount(1);
    }
}
