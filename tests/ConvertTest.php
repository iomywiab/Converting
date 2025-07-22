<?php
/*
 * Copyright (c) 2022-2025 Iomywiab/PN, Hamburg, Germany. All rights reserved
 * File name: ConvertTest.php
 * Project: Converting
 * Modified at: 22/07/2025, 23:04
 * Modified by: pnehls
 */

declare(strict_types=1);

namespace Iomywiab\Tests\Converting;

use Iomywiab\Library\Converting\Convert;
use Iomywiab\Library\Converting\Exceptions\ConvertException;
use Iomywiab\Library\Converting\Exceptions\MissingConvertImplementationException;
use Iomywiab\Library\Testing\DataTypes\Enum4Testing;
use Iomywiab\Library\Testing\DataTypes\IntEnum4Testing;
use Iomywiab\Library\Testing\DataTypes\Stringable4Testing;
use Iomywiab\Library\Testing\DataTypes\StringEnum4Testing;
use Iomywiab\Library\Testing\DataTypes\ToString4Testing;
use PHPUnit\Framework\TestCase;

class ConvertTest extends TestCase
{
    /**
     * @return non-empty-list<non-empty-list<mixed>>
     * @throws \Exception
     */
    public static function provideTestDataForIntegers(): array
    {
        $timezone = new \DateTimeZone('UTC');

        // TODO use generated TestValues for better coverage
        return [
            // array
            [false, [], 0],

            // boolean
            [true, true, 1],
            [true, false, 0],

            // object
            [true, new \DateTime('1970-01-01', $timezone), 0],
            [true, new \DateTime('2025-06-26', $timezone), 1750896000],
            [false, Enum4Testing::ONE, 1],
            [true, IntEnum4Testing::ONE, 1],
            [false, new Stringable4Testing(), 0],
            [false, new ToString4Testing(), 0],

            // float
            [true, 1.0, 1],
            [false, 2.3, 0],

            // integer
            [true, -1, -1],
            [true, 1, 1],

            // null
            [true, null, 0],

            // string
            [true, '-1', -1],
            [true, '0', 0],
            [true, '1', 1],
        ];
    }

    /**
     * @return non-empty-list<non-empty-list<mixed>>
     * @throws \Exception
     */
    public static function provideTestDataForString(): array
    {
        $timezone = new \DateTimeZone('UTC');

        // TODO use generated TestValues for better coverage
        return [
            // array
            [true, [], ''],
            [true, [1], '1'],
            [true, [1, 'a'], '1,a'],

            // boolean
            [true, true, 'true'],
            [true, false, 'false'],

            // object
            [true, new \DateTime('1970-01-01', $timezone), '1970-01-01T00:00:00+00:00'],
            [true, new \DateTime('2025-06-26', $timezone), '2025-06-26T00:00:00+00:00'],
            [true, Enum4Testing::ONE, 'ONE'],
            [true, IntEnum4Testing::ONE, '1'],
            [true, StringEnum4Testing::ONE, 'One'],
            [true, new Stringable4Testing(), 'stringable'],
            [true, new ToString4Testing(), 'string'],

            // float
            [true, -1.0, '-1'],
            [true, 0.0, '0'],
            [true, 1.0, '1'],
            [true, 2.3, '2.3'],

            // integer
            [true, -1, '-1'],
            [true, 0, '0'],
            [true, 1, '1'],

            // null
            [true, null, 'NULL'],

            // string
            [true, 'abc', 'abc'],
            [true, '-1', '-1'],
            [true, '0', '0'],
            [true, '1', '1'],
        ];
    }

    /**
     * @throws MissingConvertImplementationException
     */
    public function testToBool(): void
    {
        $this->expectException(MissingConvertImplementationException::class);

        Convert::toBool('invalid');
    }

    /**
     * @throws MissingConvertImplementationException
     */
    public function testToDate(): void
    {
        $this->expectException(MissingConvertImplementationException::class);

        Convert::toDate('invalid');
    }

    /**
     * @throws MissingConvertImplementationException
     */
    public function testToFloat(): void
    {
        $this->expectException(MissingConvertImplementationException::class);

        Convert::toFloat('invalid');
    }

    /**
     * @throws ConvertException
     * @dataProvider provideTestDataForIntegers
     */
    public function testToInt(bool $isValid, mixed $value, int $expectedInt): void
    {
        try {
            self::assertSame($expectedInt, Convert::toInt($value));
            self::assertTrue($isValid);
        } catch (\Throwable $cause) {
            if (!$isValid) {
                $this->expectException($cause::class);
            }

            throw $cause;
        }
    }

    /**
     * @throws ConvertException
     * @dataProvider provideTestDataForString
     */
    public function testToString(bool $isValid, mixed $value, string $expectedString): void
    {
        try {
            self::assertSame($expectedString, Convert::toString($value));
            self::assertTrue($isValid);
        } catch (\Throwable $cause) {
            if (!$isValid) {
                $this->expectException($cause::class);
            }

            throw $cause;
        }
    }
}
