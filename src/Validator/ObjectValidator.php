<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 02/01/2018
 */

namespace PASVL\Validator;


class ObjectValidator extends Validator
{
    public function __invoke($data, $nullable = false): bool
    {
        return is_object($data) ||
            ($nullable && $data == null);;
    }

    public function instance($data, $fqcn): bool
    {
        return $data instanceof $fqcn;
    }

    public function property($data, $property, $value): bool
    {
        return (property_exists($data, $property) || property_exists($data, '__get'))
            && $data->$property == $value;
    }

    public function method($data, $method, $value): bool
    {
        return (method_exists($data, $method) || method_exists($data, '__call'))
            && $data->$method() == $value;
    }

}