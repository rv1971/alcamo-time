<?php

namespace alcamo\time;

use PHPUnit\Framework\TestCase;
use alcamo\exception\{SyntaxError};

class DurationTest extends TestCase
{
  /**
   * @dataProvider basicsProvider
   */
    public function testBasics(
        $string,
        $expectedString,
        $expectedTotalDays,
        $expectedTotalHours,
        $expectedTotalMinutes,
        $expectedTotalSeconds
    ) {
        $duration = new Duration($string);

        $this->assertSame($expectedString, (string)$duration);
        $this->assertSame($expectedTotalDays, $duration->getTotalDays());
        $this->assertSame($expectedTotalHours, $duration->getTotalHours());
        $this->assertSame($expectedTotalMinutes, $duration->getTotalMinutes());
        $this->assertSame($expectedTotalSeconds, $duration->getTotalSeconds());
    }

    public function basicsProvider()
    {
        return [
            [
                'P1Y2M3DT4H5M6.78912S', 'P1Y2M3DT4H5M6.78912S',
                428, 10276, 616565, 36993906.78912
            ],
            [
                'P100D', 'P100D',
                100, 2400, 144000, 8640000.0
            ],
            [
                'PT12H37S', 'PT12H37S',
                0, 12, 720, 43237.0
            ],
            [
                'P1DT0.000007S', 'P1DT0.000007S',
                1, 24, 1440, 86400.000007
            ],
            [
                'PT1H0.0S', 'PT1H',
                0, 1, 60, 3600.
            ],
            [
                'PT1H2M.0S', 'PT1H2M',
                0, 1, 62, 3720.
            ],
            [
                'PT3M.S', 'PT3M',
                0, 0, 3, 180.
            ],
            [
                'PT.1S', 'PT0.1S',
                0, 0, 0, 0.1
            ]
        ];
    }

    public function testConstructException()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage(
            'Syntax error in "P0.5Y" at offset 2 (".5Y"); not a supported ISO 8601 duration'
        );

        $duration = new Duration('P0.5Y');
    }
}
