<?php

declare(strict_types=1);

namespace PASVL\Validation\Problems;

/**
 * This means that the data item matched no pattern.
 * Example:
 *  $data = ['name' => 'John'];
 *  $pattern = ['name' => ':integer']
 */
class DataValueMatchedNoPattern extends ArrayFailedValidation
{
    /** @var mixed data that matched no given pattern */
    public $failedData;

    public static function fromData($data, array $level)
    {
        $i = parent::make(
            $level,
            sprintf("Data value [%s] matched no pattern", static::serialize($data))
        );
        $i->failedData = $data;
        return $i;
    }
}