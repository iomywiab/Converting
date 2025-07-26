<?php
/*
 * Copyright (c) 2022-2025 Iomywiab/PN, Hamburg, Germany. All rights reserved
 * File name: AllExceptionsTest.php
 * Project: Converting
 * Modified at: 26/07/2025, 14:48
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
     * @return non-empty-list<non-empty-array<mixed>>
     */
    public static function provideTestData(): array
    {
        $validData = [
            [['item'], DataTypeEnum::BOOLEAN, 'test', 'Error converting value. from=array(1) to=boolean',],
            [true, DataTypeEnum::ARRAY, 'test', 'Error converting value. from=boolean:true to=array',],
            [2.3, DataTypeEnum::ARRAY, 'test', 'Error converting value. from=float:2.3 to=array',],
            [1, DataTypeEnum::ARRAY, 'test', 'Error converting value. from=integer:1 to=array',],
            [new \stdClass(), DataTypeEnum::ARRAY, 'test', 'Error converting value. from=stdClass to=array',],
            ['abc', DataTypeEnum::ARRAY, 'test', 'Error converting value. from="abc" to=array',],
        ];

        $data = [];
        foreach ($validData as $dataset) {
            $data[] = ['value' => $dataset[0], 'enum' => $dataset[1], 'exceptionMessage' => $dataset[2], 'expectedMessage' => $dataset[3]];
        }

        return $data;
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
