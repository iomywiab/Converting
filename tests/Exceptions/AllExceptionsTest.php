<?php
/*
 * Copyright (c) 2022-2025 Iomywiab/PN, Hamburg, Germany. All rights reserved
 * File name: AllExceptionsTest.php
 * Project: Converting
 * Modified at: 22/07/2025, 23:04
 * Modified by: pnehls
 */

declare(strict_types=1);

namespace Iomywiab\Tests\Converting\Exceptions;

use Iomywiab\Library\Converting\Enums\DataTypeEnum;
use Iomywiab\Library\Converting\Exceptions\ConvertException;
use Iomywiab\Library\Converting\Exceptions\MissingConvertImplementationException;
use Iomywiab\Library\Converting\Exceptions\UnknownGetTypeTypeException;
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
                new ConvertException('value', DataTypeEnum::BOOLEAN, new \Exception('test')),
                'Error converting value. from="STRING" to="BOOLEAN" value=[value]',
            ],
            [
                new MissingConvertImplementationException('methodName', new \Exception('test')),
                'Missing implementation. methodName="methodName"',
            ],
            [
                new UnknownGetTypeTypeException('xxx', new \Exception('test')),
                'Unknown type from getType(). value="xxx"',
            ],
        ];
    }
}
