<?php

namespace alcamo\time;

use PHPUnit\Framework\TestCase;
use alcamo\exception\Unsupported;

class PosixFormatTest extends TestCase
{
    /**
     * @dataProvider basicsProvider
     */
    public function testBasics(
        $dateTime,
        $posixFormat,
        $expectedPhpFormat,
        $expectedText,
        $expectedLength,
        $expectedResult
    ): void {
        $dateTime = new \DateTime($dateTime);

        $posixFormat = new PosixFormat($posixFormat);

        $this->assertSame($expectedPhpFormat, $posixFormat->getPhpFormat());

        $this->assertSame($expectedText, $posixFormat->getText());

        $this->assertSame($expectedLength, $posixFormat->getLength());

        $this->assertSame($expectedResult, $posixFormat->applyTo($dateTime));
    }

    public function basicsProvider(): array
    {
        return [
            [
                '2026-02-25T18:21:42',
                '%d/%m/%Y %H:%M:%S %% %b %y, %V %a %u %w, %I %p',
                'd/m/Y H:i:s % M y, W D N w, h A',
                'dd/mm/YYYY HH:MM:SS % bbb yy, VV aaa u w, II pp',
                47,
                '25/02/2026 18:21:42 % Feb 26, 09 Wed 3 3, 06 PM'
            ],
            [
                '2023-01-01',
                '%B %V, %u %w',
                'F W, N w',
                '* VV, u w',
                null,
                'January 52, 7 0'
            ]
        ];
    }

    public function testException(): void
    {
        $this->expectException(Unsupported::class);

        $this->expectExceptionMessage(
            '"Posix format specifier %j" not supported'
        );

        new PosixFormat('%j');
    }
}
