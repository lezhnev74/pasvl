<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 02/01/2018
 */

namespace PASVL\Validator;


class StringValidator extends Validator
{
    public function __invoke($data)
    {
        if (!is_string($data)) {
            throw new InvalidData("Value is not a string");
        }
    }

    public function len($data, int $length)
    {
        if (mb_strlen($data, 'utf-8') != $length) {
            throw new InvalidData("Value has different length");
        }
    }

    public function length($data, int $length)
    {
        $this->len($data, $length);
    }

    public function min($data, int $length)
    {
        if (mb_strlen($data) < $length) {
            throw new InvalidData("Value is too small");
        }
    }

    public function max($data, int $length)
    {
        if (mb_strlen($data) > $length) {
            throw new InvalidData("Value is too long");
        }
    }

    public function contains($data, string $needle)
    {
        if (mb_strpos($data, $needle) === false) {
            throw new InvalidData("String does not contain a needle");
        }
    }

    public function regexp($data, string $pattern)
    {
        if (!preg_match($pattern, $data)) {
            throw new InvalidData("String does not match regular expression pattern");
        }
    }

    public function regex($data, string $pattern)
    {
        $this->regexp($data, $pattern);
    }

    public function starts($data, string $starts)
    {
        if (mb_strpos($data, $starts) !== 0) {
            throw new InvalidData("String does not start with a given value");
        }
    }

    public function ends($data, string $ends)
    {
        if (mb_strrpos($data, $ends) !== (strlen($data) - strlen($ends))) {
            throw new InvalidData("String does not end with a given value");
        }
    }


}