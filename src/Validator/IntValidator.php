<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 02/01/2018
 */

namespace PASVL\Validator;


class IntValidator extends Validator
{
    public function __invoke($data)
    {
        if (!is_integer($data)) {
            throw new InvalidData("Value is not an integer");
        }
    }

}