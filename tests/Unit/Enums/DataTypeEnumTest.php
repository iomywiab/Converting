<?php
/*
 * Copyright (c) 2022-2025 Iomywiab/PN, Hamburg, Germany. All rights reserved
 * File name: DataTypeEnumTest.php
 * Project: Converting
 * Modified at: 30/07/2025, 12:50
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
     * @return \Generator<array{type: DataTypeEnum, getTypeType: non-empty-string, serializeMarker: non-empty-string}>
     */
    public static function provideDataForCases(): \Generator
    {
        yield ['type' => DataTypeEnum::ARRAY, 'getTypeType' => 'array', 'serializeMarker' => 'a'];
        yield ['type' => DataTypeEnum::BOOLEAN, 'getTypeType' => 'boolean', 'serializeMarker' => 'b'];
        yield ['type' => DataTypeEnum::FLOAT, 'getTypeType' => 'double', 'serializeMarker' => 'd'];
        yield ['type' => DataTypeEnum::INTEGER, 'getTypeType' => 'integer', 'serializeMarker' => 'i'];
        yield ['type' => DataTypeEnum::NULL, 'getTypeType' => 'NULL', 'serializeMarker' => 'N'];
        yield ['type' => DataTypeEnum::OBJECT, 'getTypeType' => 'object', 'serializeMarker' => 'O'];
        yield ['type' => DataTypeEnum::RESOURCE, 'getTypeType' => 'resource', 'serializeMarker' => 'R'];
        yield ['type' => DataTypeEnum::RESOURCE_CLOSED, 'getTypeType' => 'resource (closed)', 'serializeMarker' => 'R'];
        yield ['type' => DataTypeEnum::STRING, 'getTypeType' => 'string', 'serializeMarker' => 's'];
        yield ['type' => DataTypeEnum::UNKNOWN, 'getTypeType' => 'unknown type', 'serializeMarker' => 'u'];
    }

    /**
     * @return \Generator<array{isValid: bool, value: mixed, expectedEnum: DataTypeEnum}>
     */
    public static function provideDataForFromValue(): \Generator
    {
        yield ['isValid' => true, 'value' => [], 'expectedEnum' => DataTypeEnum::ARRAY];
        yield ['isValid' => true, 'value' => [1], 'expectedEnum' => DataTypeEnum::ARRAY];
        yield ['isValid' => true, 'value' => true, 'expectedEnum' => DataTypeEnum::BOOLEAN];
        yield ['isValid' => true, 'value' => false, 'expectedEnum' => DataTypeEnum::BOOLEAN];
        yield ['isValid' => true, 'value' => -1.2, 'expectedEnum' => DataTypeEnum::FLOAT];
        yield ['isValid' => true, 'value' => 0.0, 'expectedEnum' => DataTypeEnum::FLOAT];
        yield ['isValid' => true, 'value' => 1.2, 'expectedEnum' => DataTypeEnum::FLOAT];
        yield ['isValid' => true, 'value' => -3, 'expectedEnum' => DataTypeEnum::INTEGER];
        yield ['isValid' => true, 'value' => 0, 'expectedEnum' => DataTypeEnum::INTEGER];
        yield ['isValid' => true, 'value' => 3, 'expectedEnum' => DataTypeEnum::INTEGER];
        yield ['isValid' => true, 'value' => null, 'expectedEnum' => DataTypeEnum::NULL];
        yield ['isValid' => true, 'value' => new \stdClass(), 'expectedEnum' => DataTypeEnum::OBJECT];
        yield ['isValid' => true, 'value' => new \DateTime(), 'expectedEnum' => DataTypeEnum::OBJECT];
        yield ['isValid' => true, 'value' => Enum4Testing::ONE, 'expectedEnum' => DataTypeEnum::OBJECT];
        yield ['isValid' => true, 'value' => IntEnum4Testing::ONE, 'expectedEnum' => DataTypeEnum::OBJECT];
        yield ['isValid' => true, 'value' => StringEnum4Testing::ONE, 'expectedEnum' => DataTypeEnum::OBJECT];
        yield ['isValid' => true, 'value' => new Stringable4Testing(), 'expectedEnum' => DataTypeEnum::OBJECT];
        yield ['isValid' => true, 'value' => '', 'expectedEnum' => DataTypeEnum::STRING];
        yield ['isValid' => true, 'value' => 'abc', 'expectedEnum' => DataTypeEnum::STRING];
    }

    /**
     * @return \Generator<non-empty-list<mixed>>
     */
    public static function provideDataForNormalize(): \Generator
    {
        yield [false, 'xxx', DataTypeEnum::STRING];
        yield [true, ['string', 'integer'], [DataTypeEnum::STRING, DataTypeEnum::INTEGER]];
        foreach (DataTypeEnum::cases() as $case) {
            yield [true, $case, $case];
            yield [true, $case->value, $case];
        }
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
    public function testFromData(bool $isValid, mixed $value, DataTypeEnum $expectedEnum): void
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
    public function testFromDataForClosedResource(): void
    {
        $closedResource = \fopen('php://memory', 'rb');
        if (false !== $closedResource) {
            \fclose($closedResource);
        }

        self::assertSame(DataTypeEnum::RESOURCE_CLOSED, DataTypeEnum::fromData($closedResource));
    }

    /**
     */
    public function testFromDataForResource(): void
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

    /**
     * @return void
     */
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
        self::assertNull(DataTypeEnum::RESOURCE_CLOSED->toPhpDocName());
        self::assertNull(DataTypeEnum::UNKNOWN->toPhpDocName());
    }
}
