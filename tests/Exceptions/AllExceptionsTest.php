<?php
/*
 * Copyright (c) 2022-2025 Iomywiab/PN, Hamburg, Germany. All rights reserved
 * File name: AllExceptionsTest.php
 * Project: Converting
 * Modified at: 23/07/2025, 19:56
 * Modified by: pnehls
 */

declare(strict_types=1);

namespace Iomywiab\Tests\Converting\Exceptions;

use Iomywiab\Library\Converting\Enums\DataTypeEnum;
use Iomywiab\Library\Converting\Exceptions\ConvertException;
use PHPUnit\Framework\TestCase;

class AllExceptionsTest extends TestCase
{
    /**
     * Please note: Data providers do not work for objects in phpunit process isolation
     */
    public function testExceptions(): void
    {
        $testData = self::provideTestData();
        foreach ($testData as $testRecord) {
            $exception = $testRecord[0];
            $expectedText = $testRecord[1];
            self::assertSame($expectedText, $exception->getMessage());
        }
    }

    /**
     * @return non-empty-list<non-empty-list<non-empty-string|\Throwable>>
     */
    public static function provideTestData(): array
    {
        return [
            [
                new ConvertException(['item'], DataTypeEnum::BOOLEAN, new \Exception('test')),
                'Error converting value. from=array(1) to=boolean',
            ],
            [
                new ConvertException(true, DataTypeEnum::ARRAY, new \Exception('test')),
                'Error converting value. from=boolean:true to=array',
            ],
            [
                new ConvertException(2.3, DataTypeEnum::ARRAY, new \Exception('test')),
                'Error converting value. from=float:2.3 to=array',
            ],
            [
                new ConvertException(1, DataTypeEnum::ARRAY, new \Exception('test')),
                'Error converting value. from=integer:1 to=array',
            ],
            [
                new ConvertException(new \stdClass(), DataTypeEnum::ARRAY, new \Exception('test')),
                'Error converting value. from=stdClass to=array',
            ],
            [
                new ConvertException('abc', DataTypeEnum::ARRAY, new \Exception('test')),
                'Error converting value. from="abc" to=array',
            ],
        ];
    }
}
