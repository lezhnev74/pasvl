<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 02/01/2018
 */

namespace PASVL\Validator;


class StringValidator extends Validator
{
    public function __invoke($data): bool
    {
        return is_string($data);
    }

    public function len($data, int $length): bool
    {
        return mb_strlen($data, 'utf-8') == $length;
    }

    public function length($data, int $length): bool
    {
        return $this->len($data, $length);
    }

    public function min($data, int $length): bool
    {
        return mb_strlen($data) >= $length;
    }

    public function max($data, int $length): bool
    {
        return mb_strlen($data) <= $length;
    }

    public function contains($data, string $needle): bool
    {
        return mb_strpos($data, $needle) !== false;
    }

    public function regexp($data, string $pattern): bool
    {
        return preg_match($pattern, $data);
    }

    public function regex($data, string $pattern): bool
    {
        return $this->regexp($data, $pattern);
    }

    public function starts($data, string $starts): bool
    {
        return mb_strpos($data, $starts) === 0;
    }

    public function ends($data, string $ends): bool
    {
        return mb_strrpos($data, $ends) === (strlen($data) - strlen($ends));
    }


}