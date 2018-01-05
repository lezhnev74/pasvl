<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 02/01/2018
 */

namespace PASVL\Validator;


class ArrayValidator extends Validator
{
    public function __invoke($data)
    {
        if (!is_iterable($data)) {
            throw new InvalidData("Value is not an array/iterable");
        }
    }

}