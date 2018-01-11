<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 03/01/2018
 */

namespace PASVL\Traverser;

use PASVL\ValidatorLocator\ValidatorLocator;
use PHPUnit\Framework\TestCase;

class TraversingMatcherTest extends TestCase
{
    function dataProviderValid()
    {
        return [
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
     * @throws FailReport
     */
    function test_matches_valid_data($data, $pattern)
    {
        $matcher = new TraversingMatcher(new ValidatorLocator());
        try {
            $matcher->match($pattern, $data);
            $this->addToAssertionCount(1);
        } catch (FailReport $report) {
            echo "\n--- Array does not match a pattern ---\n";
            echo "Reason: " . ($report->isKeyFailed() ? "Invalid key found" : "Invalid value found") . "\n";
            echo "Data keys chain to invalid data: ";
            if ($report->getFailedPatternLevel()) {
                echo implode(" => ", $report->getDataKeyChain());
                echo " => ";
            }
            echo $report->getMismatchDataKey() . "\n";
            if ($report->isValueFailed()) {
                echo "Invalid value: ";
                echo json_encode($report->getMismatchDataValue(), JSON_PRETTY_PRINT) . "\n";
            }
            echo "Mismatched pattern: " . json_encode($report->getMismatchPattern(), JSON_PRETTY_PRINT) . "\n";
            $this->fail();
        }
    }

    function test_validates_simple_array2()
    {

        $data = [
            'a_key' => 'a_value',
            'b_key' => [
                'c_key' => 12,
                'd_key' => ['a', 'b', 'c'],
            ],
        ];
        $pattern = [
            'a_key' => ":string",
            ':string' => [
                ":string" => ":int",
                "d_key" => ":array",
            ],
        ];

        $matcher = new TraversingMatcher(new ValidatorLocator());
        try {
            $matcher->match($pattern, $data);
            $this->addToAssertionCount(1);
        } catch (FailReport $report) {
            echo "\n--- Array does not match a pattern ---\n";
            echo "Reason: " . ($report->isKeyFailed() ? "Invalid key found" : "Invalid value found") . "\n";
            if ($report->getFailedPatternLevel()) {
                echo "Data keys chain to invalid data: ";
                echo implode(" => ", $report->getDataKeyChain());
                echo " => ";
            }
            echo $report->getMismatchDataKey() . "\n";
            if ($report->isValueFailed()) {
                echo "Invalid value: ";
                echo json_encode($report->getMismatchDataValue(), JSON_PRETTY_PRINT) . "\n";
            }
            echo "Mismatched pattern: " . json_encode($report->getMismatchPattern(), JSON_PRETTY_PRINT) . "\n";
        }

    }

    function test_it_handles_empty_array_case()
    {
        $this->expectException(FailReport::class);

        $pattern = ["name" => "Woz"];
        $matcher = new TraversingMatcher(new ValidatorLocator());
        $matcher->match($pattern, []);
    }

    function test_it_matches_empty_array_against_asterisks()
    {
        $matcher = new TraversingMatcher(new ValidatorLocator());
        $matcher->match(["*" => ":any"], []);
        $this->addToAssertionCount(1);
    }
}
