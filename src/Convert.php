<?php
/*
 * Copyright (c) 2022-2025 Iomywiab/PN, Hamburg, Germany. All rights reserved
 * File name: Convert.php
 * Project: Converting
 * Modified at: 22/07/2025, 23:39
 * Modified by: pnehls
 */

declare(strict_types=1);

namespace Iomywiab\Library\Converting;

use Iomywiab\Library\Converting\Enums\DataTypeEnum;
use Iomywiab\Library\Converting\Exceptions\ConvertException;
use Iomywiab\Library\Converting\Exceptions\MissingConvertImplementationException;

/**
 * Class to convert between different types (mostly scalars).
 * Please note: This is not for formatting
 * @see Format
 */
class Convert
{
    private const DEFAULT_ARRAY_SEPARATOR = ',';

    /**
     * @throws MissingConvertImplementationException
     * @noinspection PhpUnusedParameterInspection
     */
    public static function toBool(mixed $value): float
    {
        throw new MissingConvertImplementationException(__METHOD__);
    }

    /**
     * @throws MissingConvertImplementationException
     * @noinspection PhpUnusedParameterInspection
     */
    public static function toDatetime(mixed $value): float
    {
        throw new MissingConvertImplementationException(__METHOD__);
    }

    /**
     * @throws MissingConvertImplementationException
     * @noinspection PhpUnusedParameterInspection
     */
    public static function toFloat(mixed $value): float
    {
        throw new MissingConvertImplementationException(__METHOD__);
    }

    /**
     * @param array<array-key,mixed> $array
     * @param null|non-empty-string $arraySeparator
     * @throws ConvertException
     */
    private static function arrayToString(array $array, null|string $arraySeparator = null): string
    {
        $string = '';
        $separator = '';
        foreach ($array as $item) {
            $string .= $separator.self::toString($item, $arraySeparator);
            $separator = $arraySeparator ?? self::DEFAULT_ARRAY_SEPARATOR;
        }

        return $string;
    }

    /**
     * @param mixed $value
     * @param non-empty-string|null $arraySeparator
     * @return string
     * @throws ConvertException
     */
    public static function toString(mixed $value, null|string $arraySeparator = null): string
    {
        try {
            $type = DataTypeEnum::fromData($value);

            return match ($type) {
                DataTypeEnum::ARRAY => /** @var array<array-key,mixed> $value */ self::arrayToString($value, $arraySeparator),
                DataTypeEnum::BOOLEAN => /** @var bool $value */ (bool)$value ? 'true' : 'false',
                DataTypeEnum::FLOAT,
                DataTypeEnum::INTEGER,
                DataTypeEnum::STRING => /** @var float|int|string $value */ (string)$value,
                DataTypeEnum::NULL => 'NULL',
                DataTypeEnum::OBJECT => /** @var object $value */ self::objectToString($value),
                DataTypeEnum::RESOURCE,
                DataTypeEnum::RESOURCE_CLOSED,
                DataTypeEnum::UNKNOWN => throw new ConvertException($value, DataTypeEnum::STRING),
            };
        } catch (\Throwable $cause) {
            throw new ConvertException($value, DataTypeEnum::STRING, $cause);
        }
    }

    /**
     * @param object $object
     * @return string
     * @throws \JsonException
     */
    private static function objectToString(object $object): string
    {
        return match (true) {
            $object instanceof \DateTimeInterface => $object->format(\DateTimeInterface::ATOM),
            $object instanceof \UnitEnum => $object->name,
            $object instanceof \Stringable => $object->__toString(),
            \method_exists($object, 'toString') => $object->toString(),
            $object instanceof \JsonSerializable => \json_encode($object, \JSON_THROW_ON_ERROR),
            $object instanceof \Serializable => \serialize($object),
            default => 'object'
        };
    }

    /**
     * @throws ConvertException
     */
    private static function objectToInt(object $value): int
    {
        return match (true) {
            $value instanceof \DateTime => $value->getTimestamp(),
            $value instanceof \BackedEnum && \is_int($value->value) => $value->value, // $value instanceof \IntBackedEnum -> false PHP 8.1
            \method_exists($value, 'toInt') => $value->toInt(),
            default => throw new ConvertException($value, DataTypeEnum::INTEGER)
        };
    }

    /**
     * @throws ConvertException
     */
    public static function toInt(mixed $value): int
    {
        try {
            $type = DataTypeEnum::fromData($value);

            return match ($type) {
                DataTypeEnum::BOOLEAN => /** @var bool $value */ ((bool)$value) ? 1 : 0,
                DataTypeEnum::FLOAT => /** @var float $value */ match (true) {
                    (\floor($value) === $value) => (int)$value,
                    default => throw new ConvertException($value, DataTypeEnum::INTEGER),
                },
                DataTypeEnum::INTEGER => /** @var int $value */ $value,
                DataTypeEnum::STRING => /** @var string $value */ match (true) {
                    (\is_numeric($value) && ($value === (string)(int)$value)) => (int)$value,
                    default => throw new ConvertException($value, DataTypeEnum::INTEGER)
                },
                DataTypeEnum::NULL => 0,
                DataTypeEnum::OBJECT => /** @var object $value */ self::objectToInt($value),
                DataTypeEnum::ARRAY,
                DataTypeEnum::RESOURCE,
                DataTypeEnum::RESOURCE_CLOSED => throw new ConvertException($value, DataTypeEnum::STRING),

                default => throw new MissingConvertImplementationException(__METHOD__)
            };
        } catch (ConvertException $cause) {
            throw $cause;
        } catch (\Throwable $cause) {
            throw new ConvertException($value, DataTypeEnum::STRING, $cause);
        }
    }
}
