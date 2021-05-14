<?php

namespace ArturDoruch\DateParser;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class DateParser
{
    /**
     * @var int The maximum timestamp allowed to parse.
     */
    const MAX_TIMESTAMP = 4102444800; // 2100-01-01 00:00:00

    /**
     * @var string
     */
    private static $processingDate;

    /**
     * Parses a formatted date and time or a timestamp.
     *
     * @param string|int $date A formatted date and time or a timestamp.
     *
     * @return \DateTime
     * @throws \InvalidArgumentException When given date is invalid.
     */
    public static function parse($date): \DateTime
    {
        if ($date instanceof \DateTime) {
            return $date;
        }

        self::$processingDate = $date;

        if (is_int($date)) {
            if ($date > 0 && $date < self::MAX_TIMESTAMP) {
                return new \DateTime('@'.$date);
            }

            throw new \InvalidArgumentException(sprintf('Invalid date timestamp "%s".', $date));
        }

        $parts = date_parse($date = self::normalizeMonth($date));

        if (!$parts['error_count'] && !$parts['warning_count'] && $parts['day']) {
            return new \DateTime(sprintf('%d-%d-%d %d:%d:%d', $parts['day'], $parts['month'], $parts['year'], $parts['hour'], $parts['minute'], $parts['second']));
        }

        try {
            if (preg_match('/^(?!0.+)(\d{4}).(?!00)([01]\d).(?!00)([0123]\d)(,? \d{2}(:\d{2}){1,2})?$/', $date, $matches)) {
                // Format: YYYY.MM.DD[, H:i[:s]]
                return self::createDate($matches[3], $matches[2], $matches[1] , $matches[4] ?? '');
            } elseif (preg_match('/^(?!00)([0123]\d).(?!00)([01]\d).(\d{2})$/', $date, $matches) ) {
                // Format: DD.MM.YY
                // WARNING: This parsing make leads to incorrect result.
                return self::createDate($matches[1], $matches[2], '20'.$matches[3]);
            } elseif (preg_match('/^([-+]?\d+ ?)?[a-z]{3,}( [a-z\d:+ ]+)?$/i', self::$processingDate)) {
                // Relative datetime format https://www.php.net/manual/en/datetime.formats.relative.php
                return new \DateTime(self::$processingDate);
            }
        } catch (\Exception $e) {
        }

        throw new \InvalidArgumentException(sprintf('Invalid date "%s".', self::$processingDate));
    }


    private static function createDate($day, $month, $year, $time = ''): \DateTime
    {
        if (@checkdate($month, $day, $year)) {
            return new \DateTime($day . '-' . $month . '-' . $year . ' ' . $time);
        }

        throw new \InvalidArgumentException('Invalid date arguments.');
    }


    private static function normalizeMonth(string $date): string
    {
        return preg_replace_callback('/([\p{Ll}]{3})[\p{Ll}]*/u', static function ($matches) {
            return self::$anyEnglishMonthMap[$matches[1]] ?? $matches[1];
        }, str_replace([',', '  '], ' ', mb_strtolower($date)));
    }


    private static $anyEnglishMonthMap = [
        'maa' => 'mar',
        'mei' => 'may',
        // Norway
        'des' => 'dec',
        // German
        'mär' => 'mar',
        'mai' => 'may',
        'okt' => 'oct',
        'dez' => 'dec',
        // Polish
        'sty' => 'jan',
        'lut' => 'feb',
        'kwi' => 'apr',
        'maj' => 'may',
        'cze' => 'jun',
        'lip' => 'jul',
        'sie' => 'aug',
        'wrz' => 'sep',
        'paź' => 'oct',
        'lis' => 'nov',
        'gru' => 'dec',
        // Russian
        'янв' => 'jan',
        'фев' => 'feb',
        'мар' => 'mar',
        'апр' => 'apr',
        'май' => 'may',
        'июн' => 'jun',
        'июл' => 'jul',
        'авг' => 'aug',
        'сен' => 'sep',
        'окт' => 'oct',
        'ноя' => 'nov',
        'дек' => 'dec',
    ];
}
 