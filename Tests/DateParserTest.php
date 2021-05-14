<?php

namespace ArturDoruch\DateParser\Tests;

use ArturDoruch\DateParser\DateParser;
use PHPUnit\Framework\TestCase;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class DateParserTest extends TestCase
{
    public function getValidDates()
    {
        return [
            // For test with string month
            ['15 marzec, 2018', '15-03-2018'],
            ['Jan 23 2017', '23-01-2017'],
            ['1 Luty 2020', '01-02-2020'],
            ['Февраль 15 2021', '15-02-2021'],
            // For test timestamp
            [1621004003, '14-05-2021'],
            // For test regexp 1
            ['2012.03.04', '04-03-2012'],
            // For test regexp 2
            ['01.10.70', '01-10-1970'],
            ['01.10.00', '01-10-2000'],
            ['14.10.' . date('y'), '14-10-20' . date('y')],
        ];
    }

    /**
     * @dataProvider getValidDates
     */
    public function testParseValidDate($formatted, $expected)
    {
        $dateTime = DateParser::parse($formatted);
        self::assertEquals($expected, $dateTime->format('d-m-Y'));
    }


    public function getRelativeDates()
    {
        return [
            ['-10 days'],
            ['10 days ago'],
            ['yesterday noon'],
            ['yesterday 14:00'],
            ['last sat of April 2021'],
            ['Monday'],
            ['+1 week may 2021'],
        ];
    }

    /**
     * @dataProvider getRelativeDates
     */
    public function testParseRelativeDate($relative)
    {
        $this->expectNotToPerformAssertions();
        DateParser::parse($relative);
    }


    public function getDateTimes()
    {
        return [
            ['15 marzec 2018, 12:00:01', '15-03-2018 12:00:01'],
            ['2019.03.04, 12:00:01', '04-03-2019 12:00:01'],
            ['2019.03.05, 13:30', '05-03-2019 13:30:00'],
        ];
    }

    /**
     * @dataProvider getDateTimes
     */
    public function testParseDateTimes($formatted, $expected)
    {
        $dateTime = DateParser::parse($formatted);
        self::assertEquals($expected, $dateTime->format('d-m-Y H:i:s'));
    }


    public function getInvalidDates()
    {
        return [
            ['18 luty 20302'],
            ['0.00.00'],

            // For test timestamp
            [32503680001],
            [0],

            [''],

            // For test regexp 1
            ['2012.03.40'],
            ['2012.20.01'],
            ['0998.02.01'],
        ];
    }

    /**
     * @dataProvider getInvalidDates
     * @expectedException \InvalidArgumentException
     */
    public function testParseInvalidDate($dateString)
    {
        DateParser::parse($dateString);
    }
}
