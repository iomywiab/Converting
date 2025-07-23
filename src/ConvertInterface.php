<?php
/*
 * Copyright (c) 2022-2025 Iomywiab/PN, Hamburg, Germany. All rights reserved
 * File name: ConvertInterface.php
 * Project: Converting
 * Modified at: 23/07/2025, 19:46
 * Modified by: pnehls
 */

declare(strict_types=1);

namespace Iomywiab\Library\Converting;

use Iomywiab\Library\Converting\Exceptions\ConvertExceptionInterface;

/**
 * Class to convert between different types (mostly scalars).
 * Please note: This is not for formatting, just for converting
 * @see Format
 */
interface ConvertInterface
{
    /**
     * @param mixed $value
     * @return bool
     * @throws ConvertExceptionInterface
     */
    public static function toBool(mixed $value): bool;

    /**
     * @param mixed $value
     * @return \DateTimeInterface
     * @throws ConvertExceptionInterface
     */
    public static function toDatetime(mixed $value): \DateTimeInterface;

    /**
     * @param mixed $value
     * @return float
     * @throws ConvertExceptionInterface
     */
    public static function toFloat(mixed $value): float;

    /**
     * @param mixed $value
     * @return int
     * @throws ConvertExceptionInterface
     */
    public static function toInt(mixed $value): int;

    /**
     * @param mixed $value
     * @param non-empty-string|null $arraySeparator
     * @return string
     * @throws ConvertExceptionInterface
     */
    public static function toString(mixed $value, null|string $arraySeparator = null): string;
}
