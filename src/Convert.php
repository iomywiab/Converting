<?php
/*
 * Copyright (c) 2022-2025 Iomywiab/PN, Hamburg, Germany. All rights reserved
 * File name: Convert.php
 * Project: Converting
 * Modified at: 26/07/2025, 13:33
 * Modified by: pnehls
 */

declare(strict_types=1);

namespace Iomywiab\Library\Converting;

use Iomywiab\Library\Converting\Enums\DataTypeEnum;
use Iomywiab\Library\Converting\Exceptions\ConvertException;
use Iomywiab\Library\Converting\Exceptions\ConvertExceptionInterface;

/**
 * Class to convert between different types (mostly scalars).
 * Please note: This is not for formatting, just for converting
 * @see Format
 */
class Convert implements ConvertInterface
{
    private const DEFAULT_ARRAY_SEPARATOR = ',';
    public const FALSE_STRING = 'false';
    public const NULL_STRING = 'null';
    public const TRUE_STRING = 'true';

    /**
     * @inheritDoc
     */
    public static function toBool(mixed $value): bool
    {
        $type = DataTypeEnum::fromData($value);

        // @phpstan-ignore return.type
        return match ($type) {
            DataTypeEnum::BOOLEAN => $value,
            DataTypeEnum::FLOAT => (0.0 !== $value),
            DataTypeEnum::INTEGER => (0 !== $value),
            DataTypeEnum::NULL => false,
            DataTypeEnum::STRING => match ($value) {
                self::TRUE_STRING => true,
                self::FALSE_STRING => false,
                default => \is_numeric($value)
                    ? self::toBool((float)$value)
                    : throw new ConvertException($value, DataTypeEnum::BOOLEAN),
            },
            DataTypeEnum::ARRAY,
            DataTypeEnum::OBJECT,
            DataTypeEnum::RESOURCE,
            DataTypeEnum::RESOURCE_CLOSED,
            DataTypeEnum::UNKNOWN => throw new ConvertException($value, DataTypeEnum::BOOLEAN),
        };
    }

    /**
     * @inheritDoc
     */
    public static function toDatetime(mixed $value): \DateTimeInterface
    {
        try {
            $type = DataTypeEnum::fromData($value);

            return match ($type) {
                // @phpstan-ignore argument.type
                DataTypeEnum::INTEGER => (new \DateTime())->setTimestamp($value),
                DataTypeEnum::OBJECT => ($value instanceof \DateTimeInterface) ? $value : throw new ConvertException($value, \DateTimeInterface::class),
                // @phpstan-ignore argument.type
                DataTypeEnum::STRING => new \DateTime($value),
                DataTypeEnum::ARRAY,
                DataTypeEnum::BOOLEAN,
                DataTypeEnum::FLOAT,
                DataTypeEnum::NULL,
                DataTypeEnum::RESOURCE,
                DataTypeEnum::RESOURCE_CLOSED,
                DataTypeEnum::UNKNOWN => throw new ConvertException($value, \DateTimeInterface::class),
            };
        } catch (ConvertExceptionInterface $forward) {
            throw $forward;
        } catch (\Throwable $cause) {
            throw new ConvertException($value, \DateTimeInterface::class, $cause);
        }
    }

    /**
     * @inheritDoc
     */
    public static function toFloat(mixed $value): float
    {
        $type = DataTypeEnum::fromData($value);

        // @phpstan-ignore return.type
        return match ($type) {
            // @phpstan-ignore ternary.condNotBoolean
            DataTypeEnum::BOOLEAN => $value ? 1.0 : 0.0,
            DataTypeEnum::FLOAT => $value,
            // @phpstan-ignore cast.double
            DataTypeEnum::INTEGER => (float)$value,
            DataTypeEnum::STRING => \is_numeric($value) ? (float)$value : throw new ConvertException($value, DataTypeEnum::FLOAT),
            DataTypeEnum::NULL => 0.0,
            DataTypeEnum::ARRAY,
            DataTypeEnum::OBJECT,
            DataTypeEnum::RESOURCE,
            DataTypeEnum::RESOURCE_CLOSED,
            DataTypeEnum::UNKNOWN => throw new ConvertException($value, DataTypeEnum::FLOAT),
        };
    }

    /**
     * @param array<array-key,mixed> $array
     * @param null|non-empty-string $arraySeparator
     * @throws ConvertExceptionInterface
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
     * @inheritDoc
     */
    public static function toString(mixed $value, null|string $arraySeparator = null): string
    {
        try {
            $type = DataTypeEnum::fromData($value);

            return match ($type) {
                // @phpstan-ignore argument.type
                DataTypeEnum::ARRAY => self::arrayToString($value, $arraySeparator),
                // @phpstan-ignore ternary.condNotBoolean
                DataTypeEnum::BOOLEAN => $value ? self::TRUE_STRING : self::FALSE_STRING,
                DataTypeEnum::FLOAT,
                DataTypeEnum::INTEGER,
                    // @phpstan-ignore cast.string
                DataTypeEnum::STRING => /** @var float|int|string $value */ (string)$value,
                DataTypeEnum::NULL => self::NULL_STRING,
                // @phpstan-ignore argument.type
                DataTypeEnum::OBJECT => /** @var object $value */ self::objectToString($value),
                DataTypeEnum::RESOURCE,
                DataTypeEnum::RESOURCE_CLOSED,
                DataTypeEnum::UNKNOWN => throw new ConvertException($value, DataTypeEnum::STRING),
            };
        } catch (ConvertExceptionInterface $forward) {
            throw $forward;
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
        // @phpstan-ignore return.type
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
     * @throws ConvertExceptionInterface
     */
    private static function objectToInt(object $value): int
    {
        // @phpstan-ignore return.type
        return match (true) {
            $value instanceof \DateTime => $value->getTimestamp(),
            $value instanceof \BackedEnum && \is_int($value->value) => $value->value, // $value instanceof \IntBackedEnum -> false PHP 8.1
            \method_exists($value, 'toInt') => $value->toInt(),
            default => throw new ConvertException($value, DataTypeEnum::INTEGER)
        };
    }

    /**
     * @inheritDoc
     */
    public static function toInt(mixed $value): int
    {
        try {
            $type = DataTypeEnum::fromData($value);

            // @phpstan-ignore return.type
            return match ($type) {
                // @phpstan-ignore ternary.condNotBoolean
                DataTypeEnum::BOOLEAN => /** @var bool $value */ $value ? 1 : 0,
                DataTypeEnum::FLOAT => /** @var float $value */ match (true) {
                    // @phpstan-ignore argument.type
                    (\floor($value) === $value) => (int)$value,
                    default => throw new ConvertException($value, DataTypeEnum::INTEGER),
                },
                DataTypeEnum::INTEGER => /** @var int $value */ $value,
                DataTypeEnum::STRING => /** @var string $value */ match (true) {
                    (\is_numeric($value) && ($value === (string)(int)$value)) => (int)$value,
                    default => throw new ConvertException($value, DataTypeEnum::INTEGER)
                },
                DataTypeEnum::NULL => 0,
                // @phpstan-ignore argument.type
                DataTypeEnum::OBJECT => /** @var object $value */ self::objectToInt($value),
                DataTypeEnum::ARRAY,
                DataTypeEnum::RESOURCE,
                DataTypeEnum::RESOURCE_CLOSED,
                DataTypeEnum::UNKNOWN => throw new ConvertException($value, DataTypeEnum::STRING),
            };
        } catch (ConvertExceptionInterface $forward) {
            throw $forward;
        } catch (\Throwable $cause) {
            throw new ConvertException($value, DataTypeEnum::STRING, $cause);
        }
    }
}
