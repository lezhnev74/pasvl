<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 02/01/2018
 */

namespace PASVL\ValidatorLocator;


/**
 * Takes validator string name and locates the corresponding class
 * @package PASVL
 */
class ValidatorLocator
{
    protected $cache = [];

    public function getValidatorClass(string $name): object
    {
        if (!($validator = @$this->cache[$name])) {
            if (!($validator = $this->locate($name))) {
                if (!($validator = $this->locateDefault($name))) {
                    throw new \Exception("Missed validator: " . $name);
                }
            }
            $this->cache[$name] = $validator;
        }

        return $this->cache[$name];
    }

    protected function locateDefault(string $name): ?object
    {

        $fqcn = "\\PASVL\\Validator\\" . ucfirst($name) . "Validator";
        return class_exists($fqcn) ? new $fqcn : null;
    }

    protected function locate(string $name): ?object
    {
        // to be overridden somewhere
        return null;
    }
}