<?php
/*
 * Copyright (c) 2022-2025 Iomywiab/PN, Hamburg, Germany. All rights reserved
 * File name: DataTypeEnumTest.php
 * Project: Converting
 * Modified at: 26/07/2025, 14:54
 * Modified by: pnehls
 */

declare(strict_types=1);

namespace Iomywiab\Tests\Converting\Unit\Enums;

use Iomywiab\Library\Converting\Enums\DataTypeEnum;
use Iomywiab\Library\Testing\DataTypes\Enum4Testing;
use Iomywiab\Library\Testing\DataTypes\IntEnum4Testing;
use Iomywiab\Library\Testing\DataTypes\Stringable4Testing;
use Iomywiab\Library\Testing\DataTypes\StringEnum4Testing;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DataTypeEnum::class)]
class DataTypeEnumTest extends TestCase
{
    /**
     * @return non-empty-list<non-empty-list<mixed>>
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
        if (false !== $openResource) {
            \fclose($openResource);
        }
    }

    /**
     * @return void
     */
    public function testIsScalar(): void
    {
        foreach (DataTypeEnum::cases() as $case) {
            self::assertSame($case === DataTypeEnum::BOOLEAN || $case === DataTypeEnum::FLOAT || $case === DataTypeEnum::INTEGER || $case === DataTypeEnum::STRING, $case->isScalar());
        }
    }

    /**
     * @param bool $isValid
     * @param mixed $value
     * @param array<array-key,DataTypeEnum>|DataTypeEnum $expectedEnum
     * @return void
     * @throws \Throwable
     * @dataProvider provideDataForNormalize
     */
    public function testNormalize(bool $isValid, mixed $value, array|DataTypeEnum $expectedEnum): void
    {
        try {
            self::assertTrue(\is_array($value) || (\is_string($value) && '' !== $value) || $value instanceof DataTypeEnum);
            // @phpstan-ignore argument.type
            $value = DataTypeEnum::normalize($value);
            if (\is_array($value)) {
                foreach ($value as $item) {
                    self::assertInstanceOf(DataTypeEnum::class, $item);
                }
                self::assertEquals($expectedEnum, $value);
            } else {
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

    public function testPhpDocName(): void
    {
        self::assertSame(DataTypeEnum::ARRAY->toPhpDocName(), 'array');
        self::assertSame(DataTypeEnum::BOOLEAN->toPhpDocName(), 'bool');
        self::assertSame(DataTypeEnum::FLOAT->toPhpDocName(), 'float');
        self::assertSame(DataTypeEnum::INTEGER->toPhpDocName(), 'int');
        self::assertSame(DataTypeEnum::NULL->toPhpDocName(), 'null');
        self::assertSame(DataTypeEnum::OBJECT->toPhpDocName(), 'object');
        self::assertSame(DataTypeEnum::RESOURCE->toPhpDocName(), 'resource');
        self::assertSame(DataTypeEnum::STRING->toPhpDocName(), 'string');
        self::assertSame(DataTypeEnum::RESOURCE_CLOSED->toPhpDocName(), null);
        self::assertSame(DataTypeEnum::UNKNOWN->toPhpDocName(), null);

    }
}
