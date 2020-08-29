<?php
declare(strict_types=1);

namespace PASVL\Validation\Problems;

class ArrayFailedValidation extends \RuntimeException
{
    /** @var array */
    public $level;

    public static function make(array $level, string $message = "")
    {
        $i = new static(sprintf("%s %s", $message, self::getLevelPostfix($level)));
        $i->level = $level;
        return $i;
    }

    protected static function getLevelPostfix(array $level): string
    {
        if (!$level) return "at root level";
        return sprintf("at level [%s]", implode("->", $level));
    }

    protected static function serialize($data)
    {
        if (is_array($data)) return var_export($data, true);
        return $data;
    }
}