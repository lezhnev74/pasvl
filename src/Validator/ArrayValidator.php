<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 02/01/2018
 */

namespace PASVL\Validator;


class ArrayValidator extends Validator
{
    /** @var boolean */
    protected $skipValidation;

    public function __invoke($data, string $nullable = "false"): bool
    {
        $nullable = $this->convertStringToBool($nullable);
        $this->skipValidation = is_null($data) && $nullable;

        return $this->skipValidation || is_iterable($data);
    }

    public function count($data, $count): bool
    {
        if ($this->skipValidation) {
            return true;
        }

        return count($data) == $count;
    }

    public function keys($data, ...$keys): bool
    {
        if ($this->skipValidation) {
            return true;
        }

        foreach ($data as $key => $value) {
            if (!in_array($key, $keys)) {
                return false;
            }
        }

        return true;
    }

}