<?php

declare(strict_types=1);

namespace PASVL\Validation\Problems;

class ArrayFailedValidation extends \RuntimeException
{
    /** @var array */
    public $level;

    /** @var string|null human-readable rule failure, e.g. "the value is not a number" */
    public $reason = null;

    /** @var array|null full path incl. the failing scalar key, e.g. ['user', 'age'] */
    public $path = null;

    public static function make(array $level, string $message = ''): static
    {
        // Subclasses don't override the RuntimeException constructor, so new static() is safe.
        $i = new static(sprintf('%s %s', $message, self::getLevelPostfix($level))); // @phpstan-ignore new.static
        $i->level = $level;
        return $i;
    }

    /**
     * Build an instance with a verbatim message (no level postfix appended).
     * Used by enriched factories that already embed a precise path in the message.
     */
    protected static function makeWithMessage(array $level, string $message): static
    {
        $i = new static($message); // @phpstan-ignore new.static
        $i->level = $level;
        return $i;
    }

    protected static function getLevelPostfix(array $level): string
    {
        if ($level === []) {
            return 'at root level';
        }
        return sprintf('at level [%s]', implode('->', $level));
    }

    protected static function pathPostfix(array $path): string
    {
        if ($path === []) {
            return 'at root level';
        }
        return sprintf('at [%s]', implode('->', $path));
    }

    protected static function serialize($data)
    {
        if (is_array($data)) {
            return var_export($data, true);
        }
        return $data;
    }
}
