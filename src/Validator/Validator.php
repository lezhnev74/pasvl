<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 02/01/2018
 */

namespace PASVL\Validator;


abstract class Validator
{

    public function nullable($data, string $strict = "true"): bool
    {
        $strict = $this->convertStringToBool($strict);

        return
            (is_null($data) && $strict)
            || ($data == null && !$strict)
            || !is_null($data);
    }

    public function __call($name, $arguments)
    {
        throw new \Exception("Missed sub-validator with name: " . static::class . "::" . $name);
    }

    /**
     * When we use a pattern ":name(false)" then argument is always passed as a string,
     * this helper function converts a string to a boolean value by simple rules
     *
     * @param string $value
     * @return bool
     */
    protected function convertStringToBool(string $value): bool
    {
        // Anything but "false" converts to true
        switch (strtolower($value)) {
            case "false":
                return false;
            default:
                return true;
        }

    }

}