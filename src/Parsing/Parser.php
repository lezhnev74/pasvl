<?php

declare(strict_types=1);

namespace PASVL\Parsing;

use PASVL\Parsing\Problems\Eof;
use PASVL\Parsing\Problems\NonEmptyPostfix;
use PASVL\Parsing\Problems\ParsingFailed;
use PASVL\Parsing\Problems\UnexpectedCharacter;

abstract class Parser
{
    public const STRATEGY_ALLOW_POSTFIX    = 0; // parse while text is valid
    public const STRATEGY_STRICT           = 1; // parse all, if invalid symbol found - throw
    public const STRATEGY_DISABLE_NULLABLE = 2; // allow rules to be nullable

    public const STATE_START  = 0;
    public const STATE_FINISH = 1;

    public const PATTERN_SPACES = '\s+';

    /** @var string */
    protected $text;
    /** @var int */
    protected $pos;
    /** @var int */
    protected $state;
    /** @var int */
    protected $strategy;

    /** @return array of tokens */
    public function parse(string $text, int $strategy = self::STRATEGY_STRICT): array
    {
        if ($text === '') {
            return [];
        }

        $this->text = $text;
        $this->pos = 0;
        $this->state = self::STATE_START;
        $this->strategy = $strategy;

        $tokens = [];
        try {
            // Loops until getNextToken() throws Eof (caught below).
            while (true) { // @phpstan-ignore while.alwaysTrue
                $tokens[] = $this->getNextToken();
            }
        } catch (Eof) {
        } catch (NonEmptyPostfix $e) {
            // Sometimes we want to parse until possible, and return what was parsed
            if (($this->strategy & self::STRATEGY_STRICT) !== 0) {
                throw $e;
            }
        }

        return $tokens;
    }

    /**
     * Returns the next token instance or null of no more tokens available
     *
     * @throws UnexpectedCharacter
     * @throws NonEmptyPostfix
     * @throws ParsingFailed
     * @throws Eof when no more tokens available
     */
    abstract protected function getNextToken();

    /**
     * Matches the prefix of the input text to the $pattern and if matched:
     *  - returns the matched prefix of the text
     *  - advances the position to the end of the matched prefix
     *
     * @param string[] $patterns
     * @throws UnexpectedCharacter
     * @returns array [<string>$matchedPattern, <string|array>$matchedPrefix], if $pattern has groups in it, $lexeme
     *  will be an array of captured groups: pattern: "a(b)", lexeme: [0=>ab, 1=>b]
     */
    protected function expectAny(array $patterns): array
    {
        $matchedPattern = null;
        $match = [];
        foreach ($patterns as $p) {
            if (preg_match("#^$p#u", $this->remainder(), $match)) {
                $matchedPattern = $p;
                break;
            }
        }

        if ($matchedPattern === null) {
            $this->fail();
        }

        $this->move(mb_strlen($match[0]));

        $lexeme = $match[0];
        if (count($match) > 1) {
            $lexeme = $match;
        }

        return [$matchedPattern, $lexeme];
    }

    /**
     * Matches the prefix of the input text to the $pattern and if matched:
     *  - returns the matched prefix of the text
     *  - advances the position to the end of the matched prefix
     *
     * @throws UnexpectedCharacter
     * @returns string of matched prefix (empty string means nothing to match)
     */
    protected function expect(string $pattern): string
    {
        if (!preg_match("#^$pattern#u", $this->remainder(), $match)) {
            $this->fail();
        }

        $this->move(mb_strlen($match[0]));
        return $match[0];
    }

    /**
     * return the first matching pattern against the current position,
     * it DOES NOT move the cursor
     * @param string[] $patterns
     */
    protected function select(array $patterns): string
    {
        foreach ($patterns as $p) {
            if (preg_match("#^$p#u", $this->remainder(), $match)) {
                return $p;
            }
        }

        $this->fail();
    }

    protected function cur(): string
    {
        if (!isset($this->text[$this->pos])) {
            throw new Eof();
        }
        return $this->text[$this->pos];
    }

    /** Read next $n symbols, if less symbols are available, then return everything from the cur pos to the end of text */
    protected function nextSymbols(int $n): string
    {
        return substr($this->remainder(), 0, $n);
    }

    protected function move(int $chars = 1): void
    {
        $this->pos += $chars;
    }

    protected function skipSpaces(): void
    {
        try {
            $this->expect(self::PATTERN_SPACES);
        } catch (UnexpectedCharacter) {
            // no spaces found? good :)
        }
    }

    /**
     * @return string literal
     */
    protected function readStringLiteralUntil(string $stopSymbol): string
    {
        $argumentValue = '';

        try {
            while ($this->cur() !== $stopSymbol) {
                if ($this->nextSymbols(2) === '\\' . $stopSymbol) {
                    // escape sequence found
                    $this->move(); // \
                    $argumentValue .= $stopSymbol;
                } else {
                    $argumentValue .= $this->cur();
                }
                $this->move();
            }
        } catch (Eof) {
            // literal should not end until stop symbol found
            $this->fail();
        }
        $this->move();

        return $argumentValue;
    }

    protected function remainder(): string
    {
        return substr($this->text, $this->pos);
    }

    /**
     * @return never
     * @throws UnexpectedCharacter
     */
    protected function fail(string $message = ''): void
    {
        throw new UnexpectedCharacter($this->pos, $this->remainder(), $this->text);
    }
}
