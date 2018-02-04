<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 03/02/2018
 */
declare(strict_types=1);


namespace PASVL\Tests\Traverser;


use PASVL\Traverser\FailReport;
use PASVL\Traverser\VO\Traverser;
use PASVL\ValidatorLocator\ValidatorLocator;
use PHPUnit\Framework\TestCase;

class TraverserTest extends TestCase
{
    function dataProviderInvalid()
    {
        return [
            [
                ["method" => "createClient"],
                ['method' => ':string', 'other' => ':string'],
            ],
            [
                [
                    "people" => [
                        [
                            "passport" => [
                                "names" => [
                                    "john",
                                    "steven",
                                    "paul",
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'people' => [
                        "*" => [
                            "passport" => [
                                'names' => [
                                    "{1,2}" => ":string",
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                ["a", "b",],
                ['{3,}' => ":any",],
            ],
            [
                ["a", "b",],
                ['{0,1}' => ":any",],
            ],
            [
                ["a"],
                ['{0}' => ":any",],
            ],
            [
                ["a"],
                ['missed' => ":any",],
            ],

            [
                [
                    ["groupName" => "sales"],
                    ["groupName" => "Other"],
                ],
                [
                    '{3,}' => [ // TEST to check if minimum rows of 3 is present above
                        'groupName' => ':string',
                    ],
                ],

            ],
        ];
    }

    /**
     * @dataProvider dataProviderInvalid
     */
    function test_matches_invalid_data($data, $pattern)
    {
        $matcher = new Traverser(new ValidatorLocator());
        $this->assertFalse($matcher->check($pattern, $data));
    }

    function dataProviderValid()
    {
        return [
            [
                [
                    "Other" => [
                        "rw",
                        "move_into",
                        "note",
                        "owner",
                        "priority",
                        "owner",
                    ],
                    "sales" => [
                        "rw",
                    ],
                ],
                [

                    'Other!' => [
                        'rw',
                        'move_into',
                        'note',
                        'owner',
                        'priority',
                        'owner',
                    ],
                    ':string :min(1)' => [
                        'rw',
                    ],

                ],
            ],
            [
                [
                    ["other" => ["a", "b"]],
                    ["sales" => ["a"]],
                ],
                [
                    [':string :min(2)' => ['a', 'b']],
                    [':string :min(1)' => ['a']],
                ],
            ],
            [
                [
                    [
                        "other" => ["a", "b"],
                        "sales" => ["a"],
                    ],
                ],
                [
                    '*' => [
                        ':string :min(2)' => ['a', 'b'],
                        ':string :min(1)' => ['a'],
                    ],
                ],
            ],
            [
                [
                    'a_key' => 'a_value',
                    'b_key' => [
                        'c_key' => 12,
                        'd_key' => ['a', 'b', 'c'],
                    ],
                ],
                [
                    'a_key' => ":string",
                    ':string' => [
                        ":string" => ":int",
                        "d_key" => ":array",
                    ],
                ],
            ],
            [
                [
                    "name" => "",
                    "lastname" => "",
                ],
                [
                    ":string :regex(#\w{0,4}#) {1}" => ":any",
                    ":string :regex(#\w{0,}#) {1}" => ":any",
                ],
            ],
            [
                [
                    "name" => "John",
                ],
                [
                    ":string :regex(#\w{1,4}#) {1}" => ":any",
                ],
            ],
            [
                [
                    "array" => ["a" => 1, "c" => 2, "b" => 3],
                ],
                [
                    "array" => ":array :keys(b,a,c)",
                ],
            ],
            [
                [
                    "result" => "passed",
                ],
                [
                    "result" => ":string :in(passed,failed)",
                ],
            ],
            [
                [
                    "name" => "John",
                    "surname" => "McDawn",
                ],
                [
                    ":string {2}" => ":string",
                ],
            ],
            [
                [
                    "name" => "John",
                    "surname" => "McDawn",
                ],
                [
                    "name" => ":string :ends(hn)",
                    "surname" => ":string :starts(Mc)",
                ],
            ],
            [
                [
                    "ab" => "",
                    "cd" => "",
                    "efgh" => "",
                    "a123" => "",
                ],
                [
                    ":string :len(4) {2}" => ":any",
                    ":string :len(2) {2}" => ":any",
                ],
            ],
            [
                [
                    "ab" => "",
                    "cd" => "",
                    "efgh" => "",
                    "a123" => "",
                ],
                [
                    ":string :len(2) *" => ":any",
                    ":string :len(4) *" => ":any",
                ],
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
                        'title' => ':string :contains(book)',
                        'chapters' => [
                            ':string :length(2) {1,3}' => [
                                'title' => ':string',
                                'interesting?' => ':bool',
                            ],
                        ],
                    ],
                ],
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
            ],
            [
                [
                    "name" => "John",
                    "age" => 30,
                ],
                [
                    "name" => "John",
                    "age" => ":int",
                ],
            ],

        ];
    }

    /**
     * @dataProvider dataProviderValid
     */
    function test_matches_valid_data($data, $pattern)
    {
        $matcher = new Traverser(new ValidatorLocator());
        $this->assertTrue($matcher->check($pattern, $data));
    }

    function test_it_handles_empty_array_case()
    {
        $this->expectException(FailReport::class);

        $pattern = ["name" => "Woz"];
        $matcher = new Traverser(new ValidatorLocator());
        $matcher->match($pattern, []);
    }

    function test_it_matches_empty_array_against_asterisks()
    {
        $matcher = new Traverser(new ValidatorLocator());
        $matcher->match(["*" => ":any"], []);
        $this->addToAssertionCount(1);
    }
}