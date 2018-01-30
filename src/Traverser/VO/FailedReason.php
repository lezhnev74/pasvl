<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 30/01/2018
 */
declare(strict_types=1);


namespace PASVL\Traverser\VO;


class FailedReason
{
    const MISMATCHED_KEY = 1;
    const MISMATCHED_VALUE = 2;
    const MISMATCHED_KEY_QUANTITY = 3; // means data is okay, but count of keys mismatched

    /** @var int */
    private $reason_code;

    /**
     * FailedReason constructor.
     * @param int $reason_code
     */
    public function __construct(int $reason_code)
    {
        if (!in_array($reason_code, [
            self::MISMATCHED_KEY,
            self::MISMATCHED_KEY_QUANTITY,
            self::MISMATCHED_VALUE,
        ])) {
            throw new \InvalidArgumentException("Invalid reason code");
        }
        $this->reason_code = $reason_code;
    }

    static function fromFailedValue(): self
    {
        return new self(self::MISMATCHED_VALUE);
    }

    static function fromFailedKey(): self
    {
        return new self(self::MISMATCHED_KEY);
    }

    static function fromFailedKeyQuantity(): self
    {
        return new self(self::MISMATCHED_KEY_QUANTITY);
    }

    /**
     * @return int
     */
    public function getReasonCode(): int
    {
        return $this->reason_code;
    }

    public function isKeyType(): bool
    {
        return $this->getReasonCode() == self::MISMATCHED_KEY;
    }

    public function isKeyQuantityType(): bool
    {
        return $this->getReasonCode() == self::MISMATCHED_KEY_QUANTITY;
    }

    public function isValueType(): bool
    {
        return $this->getReasonCode() == self::MISMATCHED_VALUE;
    }

}