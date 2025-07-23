<?php
/*
 * Copyright (c) 2022-2025 Iomywiab/PN, Hamburg, Germany. All rights reserved
 * File name: DataTypeEnumTest.php
 * Project: Converting
 * Modified at: 23/07/2025, 19:51
 * Modified by: pnehls
 */

declare(strict_types=1);

namespace Iomywiab\Tests\Converting\Enums;

use Iomywiab\Library\Converting\Enums\DataTypeEnum;
use Iomywiab\Library\Testing\DataTypes\Enum4Testing;
use Iomywiab\Library\Testing\DataTypes\IntEnum4Testing;
use Iomywiab\Library\Testing\DataTypes\Stringable4Testing;
use Iomywiab\Library\Testing\DataTypes\StringEnum4Testing;
use PHPUnit\Framework\TestCase;

class DataTypeEnumTest extends TestCase
{
    /**
     * @return array[]
     */
    public static function provideDataForCases(): array
    {
        return [
            [DataTypeEnum::ARRAY, 'array', 'a'],
            [DataTypeEnum::BOOLEAN, 'boolean', 'b'],
            [DataTypeEnum::FLOAT, 'double', 'd'],
            [DataTypeEnum::INTEGER, 'integer', 'i'],
            [DataTypeEnum::NULL, 'NULL', 'N'],
            [DataTypeEnum::OBJECT, 'object', 'O'],
            [DataTypeEnum::RESOURCE, 'resource', 'R'],
            [DataTypeEnum::RESOURCE_CLOSED, 'resource (closed)', 'R'],
            [DataTypeEnum::STRING, 'string', 's'],
            [DataTypeEnum::UNKNOWN, 'unknown type', 'u'],
        ];
    }

    /**
     * @return non-empty-list<non-empty-list<mixed>>
     */
    public static function provideDataForFromValue(): array
    {
        return [
            [true, [], DataTypeEnum::ARRAY],
            [true, [1], DataTypeEnum::ARRAY],
            [true, true, DataTypeEnum::BOOLEAN],
            [true, false, DataTypeEnum::BOOLEAN],
            [true, -1.2, DataTypeEnum::FLOAT],
            [true, 0.0, DataTypeEnum::FLOAT],
            [true, 1.2, DataTypeEnum::FLOAT],
            [true, -3, DataTypeEnum::INTEGER],
            [true, 0, DataTypeEnum::INTEGER],
            [true, 3, DataTypeEnum::INTEGER],
            [true, null, DataTypeEnum::NULL],
            [true, new \stdClass(), DataTypeEnum::OBJECT],
            [true, new \DateTime(), DataTypeEnum::OBJECT],
            [true, Enum4Testing::ONE, DataTypeEnum::OBJECT],
            [true, IntEnum4Testing::ONE, DataTypeEnum::OBJECT],
            [true, StringEnum4Testing::ONE, DataTypeEnum::OBJECT],
            [true, new Stringable4Testing(), DataTypeEnum::OBJECT],
            [true, '', DataTypeEnum::STRING],
            [true, 'abc', DataTypeEnum::STRING],
        ];
    }

    /**
     * @return non-empty-list<non-empty-list<mixed>>
     */
    public static function provideDataForNormalize(): array
    {
        $data = [];
        $data[] = [false, 'xxx', DataTypeEnum::STRING];
        $data[] = [true, ['string', 'integer'], [DataTypeEnum::STRING, DataTypeEnum::INTEGER]];
        foreach (DataTypeEnum::cases() as $case) {
            $data[] = [true, $case, $case];
            $data[] = [true, $case->value, $case];
        }

        return $data;
    }

    /**
     * @throws \Exception
     * @dataProvider provideDataForCases
     */
    public function testCases(DataTypeEnum $type, string $getTypeType, string $serializeMarker): void
    {
        self::assertSame($getTypeType, $type->value);
        self::assertSame($getTypeType, $type->toGetTypeType());

        if (DataTypeEnum::UNKNOWN === $type || DataTypeEnum::RESOURCE_CLOSED === $type) {
            return;
        }

        self::assertSame($serializeMarker, $type->toSerializeMarker());
        self::assertSame($type, DataTypeEnum::fromGetType($getTypeType));
        self::assertSame($type, DataTypeEnum::fromSerialize($serializeMarker));
    }

    /**
     * @dataProvider provideDataForFromValue
     * @throws \Throwable
     */
    public function testFromValue(bool $isValid, mixed $value, DataTypeEnum $expectedEnum): void
    {
        try {
            self::assertSame($expectedEnum, DataTypeEnum::fromData($value));
            self::assertTrue($isValid);
        } catch (\Throwable $cause) {
            if (!$isValid) {
                $this->expectException($cause::class);
            }

            throw $cause;
        }
    }

    /**
     */
    public function testFromValueForClosedResource(): void
    {
        $closedResource = \fopen('php://memory', 'rb');
        if (false !== $closedResource) {
            \fclose($closedResource);
        }

        self::assertSame(DataTypeEnum::RESOURCE_CLOSED, DataTypeEnum::fromData($closedResource));
    }

    /**
     */
    public function testFromValueForResource(): void
    {
        $openResource = \fopen('php://memory', 'rb');
        self::assertSame(DataTypeEnum::RESOURCE, DataTypeEnum::fromData($openResource));
        \fclose($openResource);
    }

    /**
     * @throws \Throwable
     * @dataProvider provideDataForNormalize
     */
    public function testNormalize(bool $isValid, mixed $value, array|DataTypeEnum $expectedEnum): void
    {
        try {
            $value = DataTypeEnum::normalize($value);
            if (\is_array($value)) {
                foreach ($value as $item) {
                    self::assertInstanceOf(DataTypeEnum::class, $item);
                }
                self::assertEquals($expectedEnum, $value);
            } else {
                self::assertInstanceOf(DataTypeEnum::class, $value);
                self::assertSame($expectedEnum, $value);
            }
            self::assertTrue($isValid);
        } catch (\Throwable $cause) {
            if (!$isValid) {
                $this->expectException($cause::class);
            }

            throw $cause;
        }
    }
}
