<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 02/01/2018
 */

namespace PASVL\ValidatorLocator;

use PASVL\Validator\Validator;


/**
 * Goal is to look for validator classes
 * @package PASVL
 */
class ValidatorLocator
{
    protected $cache = [];

    /**
     * Takes validator string name and locates the corresponding class
     * There is a set of default validators located at "src/Validator" folder
     *
     * @param string $name
     * @return Validator
     * @throws \Exception
     */
    public function getValidatorClass(string $name): Validator
    {
        if (!array_key_exists($name, $this->cache)) {
            if (!($validator = $this->locate($name))) {
                if (!($validator = $this->locateDefault($name))) {
                    throw new \Exception("Missed validator: " . $name);
                }
            }
            $this->cache[$name] = $validator;
        }

        return $this->cache[$name];
    }

    protected function locateDefault(string $name): ?Validator
    {

        $fqcn = "\\PASVL\\Validator\\" . ucfirst($name) . "Validator";
        return class_exists($fqcn) ? new $fqcn : null;
    }

    protected function locate(string $name): ?object
    {
        // to be overridden in your own ValidatorLocator implementation (if any)
        return null;
    }
}
