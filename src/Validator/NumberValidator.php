<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 02/01/2018
 */

namespace PASVL\Validator;


class NumberValidator extends Validator
{
    /** @var boolean */
    protected $skipValidation;

    public function __invoke($data, string $nullable = "false"): bool
    {
        $nullable = $this->convertStringToBool($nullable);
        $this->skipValidation = is_null($data) && $nullable;

        return $this->skipValidation || is_integer($data) || is_float($data);
    }

    public function between($data, $from, $to): bool
    {
        if ($this->skipValidation) {
            return true;
        }

        if ($to < $from) {
            throw new \Exception("top boundary is lower than bottom boundary");
        }

        return $data <= $to && $data >= $from;
    }

    public function in($data, ...$options): bool
    {
        if ($this->skipValidation) {
            return true;
        }

        return in_array($data, $options);
    }

    public function gt($data, $compare): bool
    {
        if ($this->skipValidation) {
            return true;
        }

        return $data > $compare;
    }

    public function gte($data, $compare): bool
    {
        if ($this->skipValidation) {
            return true;
        }

        return $data >= $compare;
    }

    public function lt($data, $compare): bool
    {
        if ($this->skipValidation) {
            return true;
        }

        return $data < $compare;
    }

    public function lte($data, $compare): bool
    {
        if ($this->skipValidation) {
            return true;
        }

        return $data <= $compare;
    }

    public function negative($data): bool
    {
        if ($this->skipValidation) {
            return true;
        }

        return $data < 0;
    }

    public function positive($data): bool
    {
        if ($this->skipValidation) {
            return true;
        }

        return $data > 0;
    }

    public function min($data, $min): bool
    {
        if ($this->skipValidation) {
            return true;
        }

        return $this->gte($data, $min);
    }

    public function max($data, $max): bool
    {
        if ($this->skipValidation) {
            return true;
        }

        return $this->lte($data, $max);
    }
}