<?php

namespace alcamo\time;

use alcamo\exception\SyntaxError;

/**
 * @namespace alcamo::time
 *
 * @brief Date/time-related classes
 */

/**
 * @brief %Duration adding features to DateInterval
 *
 * @date Last reviewed 2025-10-11
 */
class Duration extends \DateInterval
{
    /**
     * @brief Average number of days in a month
     *
     * May be changed in derived classes if a different computation is needed
     * (e.g. for commercial applications).
     */
    public const AVG_DAYS_PER_MONTH = 365.2425 / 12;

    /**
     * @param stringable|DateInterval $text
     *
     * Unlike
     * [DateInterval::__construct()](https://www.php.net/manual/en/dateinterval.construct)
     * this constructor also recognizes fractions of a second.
     */
    public function __construct($text)
    {
        if ($text instanceof \DateInterval) {
            $text = $text->format(
                '%rP'
                    . ($text->days > 0 ? '%aD' : '%yY%mM%dD')
                    . 'T%hH%iM%s.%FS'
            );
        } else {
            $text = (string)$text;
        }

        $a = explode('.', $text);

        if (!isset($a[1])) {
            // Literal without dot, understood by DateInterval constructor
            parent::__construct($text);
        } else {
            // After the dot there must be a number and an 'S'.
            if (!preg_match('/^([0-9]*)S$/', $a[1], $matches)) {
                /** @throw alcamo::exception::SyntaxError if not a supported
                 *  ISO 8601 duration */
                throw (new SyntaxError())->setMessageContext(
                    [
                        'inData' => $text,
                        'atOffset' => strlen($a[0]),
                        'extraMessage' => 'not a supported ISO 8601 duration'
                    ]
                );
            }

            /* If there are only zeros before the dot, the part understood by
             * the DateInterval constructor must not contain seconds. */
            $str = rtrim($a[0], '0');

            if (!(int)$str[-1]) {
                parent::__construct($str == 'PT' ? 'P0D' : rtrim($str, 'T'));
            } else {
                parent::__construct("{$a[0]}S");
            }

            $this->f = (float)".{$a[1]}";
        }
    }

    /// Return minimal ISO 8601 representation.
    public function __toString(): string
    {
        $format = '%rP';

        if ($this->days > 0) {
            $format .= '%aD';
        } else {
            if ($this->y) {
                $format .= '%yY';
            }

            if ($this->m) {
                $format .= '%mM';
            }

            if ($this->d) {
                $format .= '%dD';
            }
        }

        $timeFormat = '';

        if ($this->h) {
            $timeFormat .= '%hH';
        }

        if ($this->i) {
            $timeFormat .= '%iM';
        }

        $fraction = rtrim($this->format('%F'), 0);

        if ($this->s || $fraction) {
            $timeFormat .= "%s"
                . ($fraction ? ".$fraction" : '')
                . 'S';
        }

        if ($timeFormat != '') {
            $format .= "T$timeFormat";
        }

        return $this->format($format);
    }

    /// Return the total number of days, ignoring smaller units of time
    public function getTotalDays(): int
    {
        /** @warning If the interval contains months, they are counted with
         *  their average duration. */
        return round($this->getTotalDaysAsFloat());
    }

    /// Return the total number of hours, ignoring smaller units of time
    public function getTotalHours(): int
    {
        /** @warning If the interval contains months, they are counted with
         *  their average duration up to hour precision. */
        return round($this->getTotalHoursAsFloat());
    }

    /// Return the total number of minutes, ignoring smaller units of time
    public function getTotalMinutes(): int
    {
        /** @warning If the interval contains months, they are counted with
         *  their average duration up to minute precision. */
        return round($this->getTotalMinutesAsFloat());
    }

    /// Return the total number of seconds, ignoring smaller units of time
    public function getTotalSeconds(): float
    {
        /** @warning If the interval contains months, they are counted with
         *  their average duration. */
        return $this->getTotalMinutesAsFloat() * 60 + $this->s + $this->f;
    }

    private function getTotalDaysAsFloat(): float
    {
        return $this->days > 0
            ? $this->days
            : ($this->y * 12 + $this->m) * static::AVG_DAYS_PER_MONTH
            + $this->d;
    }

    private function getTotalHoursAsFloat(): float
    {
        return $this->getTotalDaysAsFloat() * 24 + $this->h;
    }

    private function getTotalMinutesAsFloat(): float
    {
        return $this->getTotalHoursAsFloat() * 60 + $this->i;
    }
}
