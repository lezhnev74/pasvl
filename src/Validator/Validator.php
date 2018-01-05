<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 02/01/2018
 */

namespace PASVL\Validator;


abstract class Validator
{

    public function __call($name, $arguments)
    {
        throw new \Exception("Missed sub-validator with name: " . static::class . "::" . $name);
    }

}