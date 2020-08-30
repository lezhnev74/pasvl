<?php

declare(strict_types=1);

namespace PASVL\Tests\Validation\Matcher;

use PASVL\Parsing\Compound\CompoundRuleParser;
use PASVL\Validation\Matcher\TokensMatcher;
use PASVL\Validation\Rules\Problems\RuleNotRecognized;
use PASVL\Validation\Rules\Problems\SubRuleNotRecognized;
use PASVL\Validation\Rules\RuleLocator;
use PHPUnit\Framework\TestCase;

class TokensMatcherTest extends TestCase
{
    public function data(): array
    {
        return [
            ["", "good", true],
            [":string", "good", true],
            [":string :min(4)", "good", true],
            [":string :min(4)", "bad", false],
            [":string :between(1,2) or :string :between(3,4)", "bad", true],
        ];
    }

    /** @dataProvider data */
    public function testItCanMatchTokensAgainstString(string $pattern, string $data, bool $matched): void
    {
        $ruleLocator = new RuleLocator();

        // 1. Parse the pattern
        $tokens = (new CompoundRuleParser())->parse($pattern);

        // 2. Match tokens against string
        $matcher = new TokensMatcher($tokens, $ruleLocator);

        $this->assertEquals($matched, $matcher->match($data)->isPassed());
    }

    public function testItThrowsExceptionOnUnrecognizedRule(): void
    {
        $this->expectException(RuleNotRecognized::class);
        $ruleLocator = new RuleLocator();
        $tokens = (new CompoundRuleParser())->parse(":unknown");
        (new TokensMatcher($tokens, $ruleLocator))->match("");
    }

    public function testItThrowsExceptionOnUnrecognizedSubRule(): void
    {
        $this->expectException(SubRuleNotRecognized::class);
        $ruleLocator = new RuleLocator();
        $tokens = (new CompoundRuleParser())->parse(":string :unknown");
        (new TokensMatcher($tokens, $ruleLocator))->match("");
    }
}
