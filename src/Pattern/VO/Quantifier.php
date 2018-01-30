<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 01/01/2018
 */

namespace PASVL\Pattern\VO;


class Quantifier
{
    /** @var int */
    protected $from;
    /** @var int */
    protected $to;

    /**
     * Quantifier constructor.
     * @param int $from
     * @param int $to
     */
    public function __construct(int $from, int $to)
    {
        if ($from < 0) {
            throw new \InvalidArgumentException("lower boundary cannot be negative");
        }
        if ($from > $to) {
            throw new \InvalidArgumentException("lower boundary cannot be greater than upper boundary");
        }

        $this->from = $from;
        $this->to   = $to;
    }

    /**
     * @return int
     */
    public function getFrom(): int
    {
        return $this->from;
    }

    /**
     * @return int
     */
    public function getTo(): int
    {
        return $this->to;
    }

    public function isOptional(): bool
    {
        return
            $this->from == 0 &&
            $this->to == 1;
    }

    public function isRequiredSingle(): bool
    {
        return
            $this->to == 1 &&
            $this->from == 1;
    }

    public function isMultiple(): bool
    {
        return
            $this->to > 1 &&
            ($this->to - $this->from) > 1;
    }

    public function isAny(): bool
    {
        return
            $this->from == 0 &&
            $this->to == PHP_INT_MAX;
    }

    public function isEqual(Quantifier $other_quantifier): bool
    {
        return
            $other_quantifier->getFrom() == $this->getFrom() &&
            $other_quantifier->getTo() == $this->getTo();
    }

    public function isValidQuantity(int $quantity): bool
    {
        return
            $this->from <= $quantity &&
            $this->to >= $quantity;
    }


    static function asAny(): self
    {
        return new static(0, PHP_INT_MAX);
    }

    static function asOptional(): self
    {
        return new static(0, 1);
    }

    static function asRequired(): self
    {
        return new static(1, 1);
    }

    static function asInterval(int $min, int $max): self
    {
        return new static ($min, $max);
    }

    public function __toString(): string
    {
        if (self::isRequiredSingle()) {
            return "!";
        }

        if (self::isOptional()) {
            return "?";
        }

        if (self::isAny()) {
            return "*";
        }


        return "\{$this->from,$this->to\}";

    }


}