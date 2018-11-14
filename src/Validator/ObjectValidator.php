<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 02/01/2018
 */

namespace PASVL\Validator;


class ObjectValidator extends Validator
{
    /** @var boolean */
    protected $skipValidation;

    /**
     * @param $data
     * @param string $nullable
     * @return bool
     */
    public function __invoke($data, string $nullable = "false"): bool
    {
        $nullable = $this->convertStringToBool($nullable);
        $this->skipValidation = is_null($data) && $nullable;

        return is_object($data) || $this->skipValidation;
    }

    public function instance($data, $fqcn): bool
    {
        if ($this->skipValidation) {
            return true;
        }

        return ($data instanceof $fqcn);
    }

    public function property($data, $property, $value): bool
    {
        if ($this->skipValidation) {
            return true;
        }

        return (property_exists($data, $property) || property_exists($data, '__get'))
               && $data->$property == $value;
    }

    public function method($data, $method, $value): bool
    {
        if ($this->skipValidation) {
            return true;
        }

        return (method_exists($data, $method) || method_exists($data, '__call'))
               && $data->$method() == $value;
    }

}