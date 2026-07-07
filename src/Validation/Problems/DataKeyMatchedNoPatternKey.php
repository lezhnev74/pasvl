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

    /**
     * @param int|string       $data
     * @param int|string|null  $dataKey the failing data key, appended to build the path
     */
    public static function fromData($data, array $level, ?MatchAttempt $best = null, $dataKey = null): static
    {
        $path = $level;
        if ($dataKey !== null) {
            $path[] = $dataKey;
        }
        $reason = $best?->reason?->getMessage();
        $suffix = $best instanceof MatchAttempt
            ? sprintf(' (closest pattern key [%s]: %s)', $best->patternKey, $reason)
            : '';

        $i = parent::make($level, sprintf('Data key [%s] matched no pattern key%s', $data, $suffix));
        $i->failedData = $data;
        $i->reason = $reason;
        $i->path = $path;
        return $i;
    }
}
