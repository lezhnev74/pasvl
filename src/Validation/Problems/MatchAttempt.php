<?php

declare(strict_types=1);

namespace PASVL\Validation\Problems;

use PASVL\Validation\Rules\Problems\RuleFailed;

/**
 * A single (dataKey, patternKey) match attempt recorded during array validation.
 * Used to surface the near-miss that got furthest when a data key matches no pattern.
 */
final class MatchAttempt
{
    // Stage ordering: how far the attempt got. Higher = closer to success.
    public const STAGE_KEY_MISMATCH   = 0; // pattern key did not describe the data key
    public const STAGE_VALUE_MISMATCH = 1; // key matched, but the value failed its rule

    public function __construct(
        public readonly int|string $patternKey,
        public readonly int $stage,
        public readonly ?RuleFailed $reason, // the swallowed diagnostic
        public readonly int $depth,          // count($this->level) at failure - tiebreaker
        public readonly int $order,          // declaration index of patternKey - final tiebreaker
    ) {
    }
}
