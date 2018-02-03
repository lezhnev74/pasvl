<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 02/01/2018
 */

namespace PASVL\Validator;


class StringValidator extends Validator
{
    public function __invoke($data, $nullable = false): bool
    {
        return is_string($data) ||
            ($nullable && $data == null);;
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

    /**
     * This regexp methods deserve a special comment.
     * Since there can be commas in pattern definition like this: ":regex(#[a-z]{1,2}#)"
     * So the pattern analyzer will split it into an array of  ["#[a-z]{1","2}#"].
     * That's why code merges it back again
     */
    public function regexp($data, ...$pattern): bool
    {
        $merged_pattern = implode(",", $pattern);
        return preg_match($merged_pattern, $data);
    }

    public function regex($data, ...$pattern): bool
    {
        return call_user_func_array([$this, "regexp"], array_merge([$data], $pattern));
    }


    public function starts($data, string $starts): bool
    {
        return mb_strpos($data, $starts) === 0;
    }

    public function ends($data, string $ends): bool
    {
        return mb_strrpos($data, $ends) === (strlen($data) - strlen($ends));
    }

    public function in($data, ...$options): bool
    {
        return in_array($data, $options);
    }

    public function url($data): bool
    {
        return filter_var($data, FILTER_VALIDATE_URL);
    }

    public function ip($data): bool
    {
        return filter_var($data, FILTER_VALIDATE_IP);
    }

    public function email($data): bool
    {
        return filter_var($data, FILTER_VALIDATE_EMAIL);
    }

    public function json($data): bool
    {
        return @json_decode($data) !== null;
    }

    public function date($data): bool
    {
        return strtotime($data) !== false;
    }
}