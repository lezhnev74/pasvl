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

    /** @param int|string|null $dataKey the failing data key, appended to build the path */
    public static function fromData($data, array $level, ?MatchAttempt $best = null, $dataKey = null): static
    {
        $path = $level;
        if ($dataKey !== null) {
            $path[] = $dataKey;
        }
        $reason = $best?->reason?->getMessage();

        $i = $best !== null
            ? parent::makeWithMessage(
                $level,
                sprintf('%s: %s (got %s)', self::pathPostfix($path), $reason, static::serialize($data))
            )
            : parent::make($level, sprintf('Data value [%s] matched no pattern', static::serialize($data)));
        $i->failedData = $data;
        $i->reason = $reason;
        $i->path = $path;
        return $i;
    }
}
