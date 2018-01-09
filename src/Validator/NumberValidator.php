<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 02/01/2018
 */

namespace PASVL\Validator;


class NumberValidator extends Validator
{
    public function __invoke($data): bool
    {
        return is_integer($data) || is_float($data);
    }

    public function between($data, $from, $to): bool
    {
        if ($to < $from) {
            throw new \Exception("top boundary is lower than bottom boundary");
        }

        return $data <= $to && $data >= $from;
    }

    public function in($data, ...$options): bool
    {
        return in_array($data, $options);
    }

    public function gt($data, $compare): bool
    {
        return $data > $compare;
    }

    public function gte($data, $compare): bool
    {
        return $data >= $compare;
    }

    public function lt($data, $compare): bool
    {
        return $data < $compare;
    }

    public function lte($data, $compare): bool
    {
        return $data <= $compare;
    }

    public function negative($data): bool
    {
        return $data < 0;
    }

    public function positive($data): bool
    {
        return $data > 0;
    }

    public function min($data, $min): bool
    {
        return $this->gte($data, $min);
    }

    public function max($data, $max): bool
    {
        return $this->lte($data, $max);
    }
}