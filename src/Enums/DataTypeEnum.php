<?php
/*
 * Copyright (c) 2022-2025 Iomywiab/PN, Hamburg, Germany. All rights reserved
 * File name: DataTypeEnum.php
 * Project: Converting
 * Modified at: 22/07/2025, 23:08
 * Modified by: pnehls
 */

declare(strict_types=1);

namespace Iomywiab\Library\Converting\Enums;

use Iomywiab\Library\Converting\Exceptions\UnknownGetTypeTypeException;

enum DataTypeEnum: string
{
    // all strings are compatible to \gettype()
    case ARRAY = 'array';
    case BOOLEAN = 'boolean';
    case FLOAT = 'double';
    case INTEGER = 'integer';
    case NULL = 'NULL';
    case OBJECT = 'object';
    case RESOURCE = 'resource';
    case RESOURCE_CLOSED = 'resource (closed)';
    case STRING = 'string';
    case UNKNOWN = 'unknown type';

    /** @var non-empty-array<non-empty-string,self> GETTYPE_TO_SELF */
    private const GETTYPE_TO_SELF = [
        'array'             => self::ARRAY,
        'boolean'           => self::BOOLEAN,
        'double'            => self::FLOAT,
        'integer'           => self::INTEGER,
        'NULL'              => self::NULL,
        'object'            => self::OBJECT,
        'resource'          => self::RESOURCE,
        'resource (closed)' => self::RESOURCE_CLOSED,
        'string'            => self::STRING,
        'unknown type'      => self::UNKNOWN,
    ];
    private const SERIALIZE_TO_SELF = [
        'a' => self::ARRAY,
        'b' => self::BOOLEAN,
        'd' => self::FLOAT,
        'i' => self::INTEGER,
        'N' => self::NULL,
        'O' => self::OBJECT,
        'R' => self::RESOURCE,
        's' => self::STRING,
    ];

    /**
     * @throws UnknownGetTypeTypeException
     */
    public static function fromData(mixed $value): self
    {
        $type = \gettype($value);

        return self::fromGetType($type);
    }

    /**
     * @param non-empty-string $getTypeType
     * @throws UnknownGetTypeTypeException
     */
    public static function fromGetType(string $getTypeType): self
    {
        if (isset(self::GETTYPE_TO_SELF[$getTypeType])) {
            return self::GETTYPE_TO_SELF[$getTypeType];
        }

        throw new UnknownGetTypeTypeException($getTypeType);
    }

    /**
     * @param non-empty-string $serializeMarker
     * @throws UnknownGetTypeTypeException
     */
    public static function fromSerialize(string $serializeMarker): self
    {
        if (isset(self::SERIALIZE_TO_SELF[$serializeMarker])) {
            return self::SERIALIZE_TO_SELF[$serializeMarker];
        }

        throw new UnknownGetTypeTypeException($serializeMarker);
    }

    /**
     * @param array<array-key,DataTypeEnum>|DataTypeEnum|non-empty-string $type
     * @return array<array-key,self>|self
     */
    public static function normalize(array|self|string $type): array|self
    {
        if (\is_string($type)) {
            return self::from($type);
        }

        if (\is_array($type)) {
            foreach ($type as &$item) {
                $item = self::normalize($item);
            }
        }

        return $type;
    }

    /**
     * @return non-empty-string
     */
    public function toGetTypeType(): string
    {
        return $this->value;
    }

    /**
     * @return non-empty-string
     * @throws \Exception
     */
    public function toSerializeMarker(): string
    {
        return match ($this) {
            self::ARRAY => 'a',
            self::BOOLEAN => 'b',
            self::FLOAT => 'd',
            self::INTEGER => 'i',
            self::NULL => 'N',
            self::OBJECT => 'O',
            self::RESOURCE, self::RESOURCE_CLOSED => 'R',
            self::STRING => 's',
            self::UNKNOWN => throw new \Exception('An unknown type cannot be serialized, therefore no serialize marker is defined'),
        };
    }
}
