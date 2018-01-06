<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 02/01/2018
 */

namespace PASVL\Validator;


class FloatValidator extends NumberValidator
{
    public function __invoke($data): bool
    {
        return is_float($data);
    }
}