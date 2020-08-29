<?php
declare(strict_types=1);

namespace PASVL\Tests\Validation;

use PASVL\Validation\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testICanValidateString(): void
    {
        $data = "Hello world!";
        $pattern = ":string";

        $validator = ValidatorBuilder::forString($pattern)->build();
        $validator->validate($data);

        $this->addToAssertionCount(1); // no exception thrown
    }
}
