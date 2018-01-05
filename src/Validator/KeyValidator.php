<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 02/01/2018
 */

namespace PASVL\Validator;


class KeyValidator extends Validator
{
    public function __invoke($data, $exact_value)
    {

        if ($data !== $exact_value) {
            throw new InvalidData("Value does not match");
        }
    }

}