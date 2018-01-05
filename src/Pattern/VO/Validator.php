<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 01/01/2018
 */

namespace PASVL\Pattern\VO;


class Validator
{
    /** @var string */
    protected $name;
    /** @var array */
    protected $arguments = [];

    /**
     * Validator constructor.
     * @param string $name
     * @param array $arguments
     */
    public function __construct(string $name, array $arguments = [])
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function isEqual(self $validator): bool
    {
        return
            $validator->getName() == $this->getName() &&
            $validator->arguments == $this->getArguments();
    }

}