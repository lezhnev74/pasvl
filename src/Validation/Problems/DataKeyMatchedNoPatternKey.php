<?php

declare(strict_types=1);

namespace PASVL\Validation\Problems;

/**
 * This means that the key in the data array matched no key in the pattern array.
 * Example:
 *  $data = ['name' => 'John'];
 *  $pattern = ['full_name' => ':string']
 */
class DataKeyMatchedNoPatternKey extends ArrayFailedValidation
{
    /** @var mixed data that matched no given pattern */
    public $failedData;

    public static function fromData($data, array $level)
    {
        $i = parent::make($level, sprintf("Data key [%s] matched no pattern key", $data));
        $i->failedData = $data;
        return $i;
    }
}