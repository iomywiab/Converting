<?php
/*
 * Copyright (c) 2022-2025 Iomywiab/PN, Hamburg, Germany. All rights reserved
 * File name: AllExceptionsTest.php
 * Project: Converting
 * Modified at: 30/07/2025, 12:44
 * Modified by: pnehls
 */

declare(strict_types=1);

namespace Iomywiab\Tests\Converting\Unit\Exceptions;

use Iomywiab\Library\Converting\Enums\DataTypeEnum;
use Iomywiab\Library\Converting\Exceptions\ConvertException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConvertException::class)]
#[UsesClass(DataTypeEnum::class)]
class AllExceptionsTest extends TestCase
{
    /**
     * @return \Generator<non-empty-array<mixed>>
     */
    public static function provideTestData(): \Generator
    {
        yield ['value' => ['item'], 'enum' => DataTypeEnum::BOOLEAN, 'exceptionMessage' => 'test', 'expectedMessage' => 'Error converting value. from=array(1) to=boolean',];
        yield ['value' => true, 'enum' => DataTypeEnum::ARRAY, 'exceptionMessage' => 'test', 'expectedMessage' => 'Error converting value. from=boolean:true to=array',];
        yield ['value' => 2.3, 'enum' => DataTypeEnum::ARRAY, 'exceptionMessage' => 'test', 'expectedMessage' => 'Error converting value. from=float:2.3 to=array',];
        yield ['value' => 1, 'enum' => DataTypeEnum::ARRAY, 'exceptionMessage' => 'test', 'expectedMessage' => 'Error converting value. from=integer:1 to=array',];
        yield ['value' => new \stdClass(), 'enum' => DataTypeEnum::ARRAY, 'exceptionMessage' => 'test', 'expectedMessage' => 'Error converting value. from=stdClass to=array',];
        yield ['value' => 'abc', 'enum' => DataTypeEnum::ARRAY, 'exceptionMessage' => 'test', 'expectedMessage' => 'Error converting value. from="abc" to=array',];
    }

    /**
     * @dataProvider provideTestData
     */
    public function testExceptions(mixed $value, DataTypeEnum $enum, string $exceptionMessage, string $expectedMessage): void
    {
        $exception = new ConvertException($value, $enum, new \Exception($exceptionMessage));
        self::assertSame($expectedMessage, $exception->getMessage());
    }
}
