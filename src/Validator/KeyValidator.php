<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 02/01/2018
 */

namespace PASVL\Validator;


class KeyValidator extends Validator
{
    /**
     * @param $data
     * @param string|int $exact_value comes from the array's key which can be both int or string
     * @return bool
     */
    public function __invoke($data, $exact_value): bool
    {
        return $data === $exact_value;
    }

}