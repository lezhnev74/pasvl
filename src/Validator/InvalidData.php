<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 02/01/2018
 */

namespace PASVL\Validator;


class InvalidData extends \Exception
{
    private $invalid_data = null;

    public function __construct(string $message = "", $invalid_data = null)
    {
        $this->invalid_data = $invalid_data;
        parent::__construct($message);
    }

    /**
     * @return null
     */
    public function getInvalidData()
    {
        return $this->invalid_data;
    }


}