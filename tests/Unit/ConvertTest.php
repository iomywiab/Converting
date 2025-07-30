<?php
/*
 * Copyright (c) 2022-2025 Iomywiab/PN, Hamburg, Germany. All rights reserved
 * File name: ConvertTest.php
 * Project: Converting
 * Modified at: 30/07/2025, 12:48
 * Modified by: pnehls
 */

declare(strict_types=1);

namespace Iomywiab\Tests\Converting\Unit;

use Iomywiab\Library\Converting\Convert;
use Iomywiab\Library\Converting\Enums\DataTypeEnum;
use Iomywiab\Library\Converting\Exceptions\ConvertException;
use Iomywiab\Library\Converting\Exceptions\ConvertExceptionInterface;
use Iomywiab\Library\Testing\DataTypes\Enum4Testing;
use Iomywiab\Library\Testing\DataTypes\IntEnum4Testing;
use Iomywiab\Library\Testing\DataTypes\Stringable4Testing;
use Iomywiab\Library\Testing\DataTypes\StringEnum4Testing;
use Iomywiab\Library\Testing\DataTypes\ToString4Testing;
use Iomywiab\Library\Testing\Values\DataProvider;
use Iomywiab\Library\Testing\Values\Enums\SubstitutionEnum;
use Iomywiab\Library\Testing\Values\Enums\TagEnum;
use Iomywiab\Library\Testing\Values\Exceptions\TestValueExceptionInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Convert::class)]
#[UsesClass(DataTypeEnum::class)]
#[UsesClass(ConvertException::class)]
class ConvertTest extends TestCase
{
    /**
     * @return \Generator<array-key,mixed>
     * @throws TestValueExceptionInterface
     */
    public static function provideTestDataForBooleans(): \Generator
    {
        $validData = [
            // boolean
            [true, true],
            [false, false],

            // float
            [0.0, false],
            [1.0, true],

            // integer
            [0, false],
            [1, true],

            // null
            [null, false],

            // string
            ['false', false],
            ['true', true],
        ];

        foreach ($validData as $item) {
            yield ['isValid' => true, 'value' => $item[0], 'expectedBool' => $item[1]];
        }

        // @phpstan-ignore shipmonk.checkedExceptionInYieldingMethod
        yield from DataProvider::byTemplate(
            ['isValid' => false, 'value' => SubstitutionEnum::VALUE, 'expectedBool' => false],
            [],
            [TagEnum::BOOLEAN]
        );
    }

    /**
     * @return \Generator<array-key,mixed>
     * @throws TestValueExceptionInterface
     * @throws \Exception
     */
    public static function provideTestDataForDateTimes(): \Generator
    {
        $timezone = new \DateTimeZone('UTC');

        $validData = [
            // Datetime
            [new \DateTime('1970-01-01', $timezone), new \DateTime('1970-01-01', $timezone)],
            [new \DateTime('2025-06-26', $timezone), new \DateTime('2025-06-26', $timezone)],
        ];

        foreach ($validData as $item) {
            yield ['isValid' => true, 'value' => $item[0], 'expectedDatetime' => $item[1]];
        }

        $now = new \DateTime('now', $timezone);
        // @phpstan-ignore shipmonk.checkedExceptionInYieldingMethod
        yield from DataProvider::byTemplate(
            ['isValid' => false, 'value' => SubstitutionEnum::VALUE, 'expectedDatetime' => $now],
            [],
            [TagEnum::DATETIME]
        );
    }

    /**
     * @return \Generator<array-key,mixed>
     * @throws TestValueExceptionInterface
     */
    public static function provideTestDataForFloats(): \Generator
    {
        $validData = [
            // boolean
            [true, 1.0],
            [false, 0.0],

            // float
            [-2.3, -2.3],
            [-1.0, -1.0],
            [0.0, 0.0],
            [1.0, 1.0],
            [-2.3, -2.3],

            // integer
            [-1, -1.0],
            [0, 0.0],
            [1, 1.0],

            // null
            [null, 0.0],

            // string
            ['-1', -1.0],
            ['0', 0.0],
            ['1', 1.0],
        ];

        foreach ($validData as $item) {
            yield ['isValid' => true, 'value' => $item[0], 'expectedFloat' => $item[1]];
        }

        // @phpstan-ignore shipmonk.checkedExceptionInYieldingMethod
        yield from DataProvider::byTemplate(
            ['isValid' => false, 'value' => SubstitutionEnum::VALUE, 'expectedFloat' => 0],
            [],
            [TagEnum::FLOAT, TagEnum::STRING_FLOAT]
        );
    }

    /**
     * @return \Generator<array-key,mixed>
     * @throws TestValueExceptionInterface
     * @throws \Exception
     */
    public static function provideTestDataForIntegers(): \Generator
    {
        $timezone = new \DateTimeZone('UTC');

        $validData = [
            // boolean
            [true, 1],
            [false, 0],

            // Datetime
            [new \DateTime('1970-01-01', $timezone), 0],
            [new \DateTime('2025-06-26', $timezone), 1750896000],

            // Enum
            [IntEnum4Testing::ONE, 1],

            // float
            [-1.0, -1],
            [0.0, 0],
            [1.0, 1],

            // integer
            [-1, -1],
            [0, 0],
            [1, 1],

            // null
            [null, 0],

            // string
            ['-1', -1],
            ['0', 0],
            ['1', 1],
        ];

        foreach ($validData as $item) {
            yield ['isValid' => true, 'value' => $item[0], 'expectedInt' => $item[1]];
        }

        // @phpstan-ignore shipmonk.checkedExceptionInYieldingMethod
        yield from DataProvider::byTemplate(
            ['isValid' => false, 'value' => SubstitutionEnum::VALUE, 'expectedInt' => 0],
            [],
            [TagEnum::INTEGER, TagEnum::ENUM_INT, TagEnum::STRING_INTEGER, TagEnum::STRING_FLOAT]
        );
    }

    /**
     * @return \Generator<array-key,mixed>
     * @throws TestValueExceptionInterface
     * @throws \Exception
     */
    public static function provideTestDataForString(): \Generator
    {
        $timezone = new \DateTimeZone('UTC');

        $validData = [
            // array
            [[], ''],
            [[1], '1'],
            [[1, 'a'], '1,a'],

            // boolean
            [true, Convert::TRUE_STRING],
            [false, Convert::FALSE_STRING],

            // object
            [new \DateTime('1970-01-01', $timezone), '1970-01-01T00:00:00+00:00'],
            [new \DateTime('2025-06-26', $timezone), '2025-06-26T00:00:00+00:00'],
            [Enum4Testing::ONE, 'ONE'],
            [IntEnum4Testing::ONE, 'ONE'],
            [StringEnum4Testing::ONE, 'ONE'],
            [new Stringable4Testing(), 'stringable'],
            [new ToString4Testing(), 'string'],

            // float
            [-1.0, '-1'],
            [0.0, '0'],
            [1.0, '1'],
            [2.3, '2.3'],

            // integer
            [-1, '-1'],
            [0, '0'],
            [1, '1'],

            // null
            [null, Convert::NULL_STRING],

            // string
            ['abc', 'abc'],
            ['-1', '-1'],
            ['0', '0'],
            ['1', '1'],
        ];

        foreach ($validData as $item) {
            yield ['isValid' => true, 'value' => $item[0], 'expectedString' => $item[1]];
        }

        // @phpstan-ignore shipmonk.checkedExceptionInYieldingMethod
        yield from DataProvider::byTemplate(
            ['isValid' => false, 'value' => SubstitutionEnum::VALUE, 'expectedString' => ''],
            [],
            [TagEnum::STRING]
        );
    }

    /**
     * @param bool $isValid
     * @param mixed $value
     * @param bool $expectedBool
     * @return void
     * @throws ConvertExceptionInterface
     * @dataProvider provideTestDataForBooleans
     */
    public function testToBool(bool $isValid, mixed $value, bool $expectedBool): void
    {
        try {
            $val = \is_scalar($value) ? (string)$value : \gettype($value);
            $hint = 'isValue='.($isValid ? 'true' : 'false').', value='.$val.', expectedBool='.($expectedBool ? 'true' : 'false');
            self::assertSame($expectedBool, Convert::toBool($value), $hint);
            self::assertTrue($isValid, $hint);
        } catch (\Throwable $cause) {
            if (!$isValid) {
                $this->expectException($cause::class);
            }

            throw $cause;
        }
    }

    /**
     * @param bool $isValid
     * @param mixed $value
     * @param \DateTimeInterface $expectedDatetime
     * @return void
     * @throws ConvertExceptionInterface
     * @dataProvider provideTestDataForDateTimes
     */
    public function testToDate(bool $isValid, mixed $value, \DateTimeInterface $expectedDatetime): void
    {
        try {
            self::assertEquals($expectedDatetime, Convert::toDatetime($value));
            self::assertTrue($isValid);
        } catch (\Throwable $cause) {
            if (!$isValid) {
                $this->expectException($cause::class);
            }

            throw $cause;
        }
    }

    /**
     * @param bool $isValid
     * @param mixed $value
     * @param float $expectedFloat
     * @return void
     * @throws ConvertExceptionInterface
     * @dataProvider provideTestDataForFloats
     */
    public function testToFloat(bool $isValid, mixed $value, float $expectedFloat): void
    {
        try {
            self::assertSame($expectedFloat, Convert::toFloat($value));
            self::assertTrue($isValid);
        } catch (\Throwable $cause) {
            if (!$isValid) {
                $this->expectException($cause::class);
            }

            throw $cause;
        }
    }

    /**
     * @param bool $isValid
     * @param mixed $value
     * @param int $expectedInt
     * @throws ConvertExceptionInterface
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
     * @param bool $isValid
     * @param mixed $value
     * @param string $expectedString
     * @throws ConvertExceptionInterface
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
