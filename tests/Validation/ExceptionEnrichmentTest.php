<?php

declare(strict_types=1);

namespace PASVL\Tests\Validation;

use PASVL\Validation\Problems\ArrayFailedValidation;
use PASVL\Validation\Problems\DataKeyMatchedNoPatternKey;
use PASVL\Validation\Problems\DataValueMatchedNoPattern;
use PASVL\Validation\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

class ExceptionEnrichmentTest extends TestCase
{
    private function validateExpectingFailure(array $pattern, array $data): ArrayFailedValidation
    {
        try {
            ValidatorBuilder::forArray($pattern)->build()->validate($data);
        } catch (ArrayFailedValidation $e) {
            return $e;
        }

        $this->fail('Expected validation to fail but it passed');
    }

    public function testScalarValueNearMissSurfacesReasonAndPath(): void
    {
        $e = $this->validateExpectingFailure(
            ['user' => ['age' => ':number']],
            ['user' => ['age' => '30x']]
        );

        $this->assertInstanceOf(DataValueMatchedNoPattern::class, $e);
        $this->assertSame(['user', 'age'], $e->path);
        $this->assertStringContainsString('the value is not a number', (string)$e->reason);
        $this->assertStringContainsString('user->age', $e->getMessage());
        $this->assertStringContainsString('the value is not a number', $e->getMessage());
    }

    public function testDataKeyNearMissNamesClosestPatternKey(): void
    {
        $e = $this->validateExpectingFailure(
            [':exact("name")' => ':string'],
            ['naem' => 'x']
        );

        $this->assertInstanceOf(DataKeyMatchedNoPatternKey::class, $e);
        $this->assertSame(['naem'], $e->path);
        $this->assertStringContainsString(':exact("name")', $e->getMessage());
    }

    public function testStagePrecedenceValueMismatchBeatsKeyMismatch(): void
    {
        // 'age' key matches the first pattern key but its value fails (:number);
        // the second pattern key never matches 'age' at all. The reported reason
        // must be the value failure, not the key mismatch.
        $e = $this->validateExpectingFailure(
            [':exact("age")' => ':number', ':exact("zzz")' => ':string'],
            ['age' => 'notnum']
        );

        $this->assertInstanceOf(DataValueMatchedNoPattern::class, $e);
        $this->assertStringContainsString('the value is not a number', (string)$e->reason);
        $this->assertSame(['age'], $e->path);
    }

    public function testTerminalUnfulfilledPatternKeyNamedWithQuantifier(): void
    {
        $e = $this->validateExpectingFailure(
            ['name' => ':string', ":exact('last_name')" => ':string'],
            ['name' => 'John']
        );

        $this->assertInstanceOf(ArrayFailedValidation::class, $e);
        $this->assertStringContainsString('last_name', $e->getMessage());
        $this->assertStringContainsString('expected exactly 1', $e->getMessage());
    }

    public function testTerminalUnfulfilledRendersQuantifierRange(): void
    {
        $e = $this->validateExpectingFailure(
            [':string {2,3}' => ':string'],
            ['a' => 'x']
        );

        $this->assertStringContainsString('2..3', $e->getMessage());
    }

    public function testBackwardCompatibleLevelAndFailedDataStillPopulated(): void
    {
        $e = $this->validateExpectingFailure(
            ['user' => ['age' => ':number']],
            ['user' => ['age' => '30x']]
        );

        $this->assertInstanceOf(DataValueMatchedNoPattern::class, $e);
        $this->assertSame(['user'], $e->level);
        $this->assertSame('30x', $e->failedData);
    }

    public function testNestedArrayFailurePropagatesWithDeepPath(): void
    {
        $e = $this->validateExpectingFailure(
            ['book' => ['chapter' => ['title' => ':number']]],
            ['book' => ['chapter' => ['title' => 'Intro']]]
        );

        $this->assertInstanceOf(DataValueMatchedNoPattern::class, $e);
        $this->assertSame(['book', 'chapter', 'title'], $e->path);
        $this->assertStringContainsString('the value is not a number', (string)$e->reason);
    }
}
