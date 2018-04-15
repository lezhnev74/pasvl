<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 01/01/2018
 */

namespace PASVL\Tests\Pattern;


use PASVL\Pattern\InvalidPattern;
use PASVL\Pattern\Pattern;
use PASVL\Pattern\VO\Quantifier;
use PASVL\Pattern\VO\Validator;
use PHPUnit\Framework\TestCase;

class PatternTest extends TestCase
{

    function dataProvider()
    {
        return [
            [
                "{3,}",
                new Validator("any"),
                [],
                Quantifier::asInterval(3, PHP_INT_MAX),
            ],
            [
                ":string()",
                new Validator("key", [':string()']),
                [],
                Quantifier::asRequired(),
            ],
            [
                "{2}",
                new Validator("any"),
                [],
                Quantifier::asInterval(2, 2),
            ],
            [
                ":string unexpectedLabel {9,2}",
            ],
            [
                "{0,1000}",
                new Validator("any"),
                [],
                Quantifier::asInterval(0, 1000),
            ],
            [
                ":string {2,4}",
                new Validator("string"),
                [],
                Quantifier::asInterval(2, 4),
            ],
            [
                ":string {-1,10}",
                new Validator("key", [':string {-1,10}']),
                [],
                Quantifier::asRequired(),
            ],
            [
                "lastname {9,2}",
            ],
            [
                "*",
                new Validator("any"),
                [],
                Quantifier::asAny(),
            ],
            [
                ":string",
                new Validator("string"),
                [],
                Quantifier::asRequired(),
            ],
            [
                ":string :len(12)",
                new Validator("string"),
                [new Validator("len", ['12'])],
                Quantifier::asRequired(),
            ],
            [
                ":string :min(1) :max(255)",
                new Validator("string"),
                [new Validator("min", ['1']), new Validator("max", ['255'])],
                Quantifier::asRequired(),
            ],
            [
                ":any *",
                new Validator("any"),
                [],
                Quantifier::asAny(),
            ],
            [
                ":any {1,10}",
                new Validator("any"),
                [],
                Quantifier::asInterval(1, 10),
            ],
            [
                ":string :len(10) :uppercase ?",
                new Validator("string"),
                [new Validator("len", ['10']), new Validator("uppercase")],
                Quantifier::asOptional(),
            ],
            [
                ":float :between(1,2)",
                new Validator("float"),
                [new Validator("between", ['1', '2']),],
                Quantifier::asRequired(),
            ],
            [
                "CUSTOM STRING",
                new Validator("key", ['CUSTOM STRING']),
                [],
                Quantifier::asRequired(),
            ],
            [
                "CUSTOM STRING?",
                new Validator("key", ['CUSTOM STRING']),
                [],
                Quantifier::asOptional(),
            ],
            [
                "CUSTOM STRING*",
            ],
            [
                ":string :regex(/so(me|ar\)/,(((12\))",
                new Validator("string"),
                [new Validator("regex", ['/so(me|ar)/', '(((12)']),],
                Quantifier::asRequired(),
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param $pattern_string
     * @param null $main_validator
     * @param array $subvalidators
     * @param null $quantifier
     */
    function test_pattern($pattern_string, $main_validator = null, $subvalidators = [], $quantifier = null)
    {
        if (!$main_validator) {
            $this->expectException(InvalidPattern::class);
        }

        $pattern = new Pattern($pattern_string);
        $this->assertTrue($pattern->getMainValidator()->isEqual($main_validator));
        $this->assertEquals(count($subvalidators), count($pattern->getSubValidators()));
        foreach ($subvalidators as $i => $subvalidator) {
            $this->assertTrue($pattern->getSubValidators()[$i]->isEqual($subvalidator));
        }
        $this->assertTrue($pattern->getQuantifier()->isEqual($quantifier));
    }

    function test_it_skips_malformatted_pattern()
    {
        $pattern = new Pattern(":string :len(12) {-1,100}");
        $this->assertTrue(
            (new Validator("key", [$pattern->getOriginalPattern()]))->isEqual($pattern->getMainValidator())
        );
        $this->assertCount(0, $pattern->getSubValidators());
        $this->assertTrue((new Quantifier(1, 1))->isEqual($pattern->getQuantifier()));
    }

    function test_it_throws_exception_on_malformatted_pattern()
    {
        $this->expectException(InvalidPattern::class);
        new Pattern(":string :len(12) {-1,100}", null, true);
    }

    function test_sub_validator_can_have_parentheses_as_arguments()
    {
        $pattern = new Pattern(":string :regexp(/[a-z](A|B\)/)", null, true);
        $this->assertTrue((new Validator("regexp", ['/[a-z](A|B)/']))->isEqual($pattern->getSubValidators()[0]));
    }

    /**
     * @ref: https://github.com/lezhnev74/pasvl/issues/4
     */
    function test_it_supports_multiple_subvalidators() {
        $patternStr = ':string :min(1) :max(255)';
        $patternObj = new Pattern($patternStr);
        $this->assertCount(2, $patternObj->getSubValidators());
    }
}
