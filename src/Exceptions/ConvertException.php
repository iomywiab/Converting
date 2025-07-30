<?php
/*
 * Copyright (c) 2022-2025 Iomywiab/PN, Hamburg, Germany. All rights reserved
 * File name: ConvertException.php
 * Project: Converting
 * Modified at: 30/07/2025, 18:21
 * Modified by: pnehls
 */

declare(strict_types=1);

namespace Iomywiab\Library\Converting\Exceptions;

use Iomywiab\Library\Converting\Enums\DataTypeEnum;

class ConvertException extends \Exception implements ConvertExceptionInterface
{
    /**
     * @param mixed $value
     * @param DataTypeEnum|class-string $toType
     * @param \Throwable|null $previous
     */
    public function __construct(mixed $value, DataTypeEnum|string $toType, null|\Throwable $previous = null)
    {
        $valueType = DataTypeEnum::fromData($value);
        $from = match ($valueType) {
            // @phpstan-ignore argument.type
            DataTypeEnum::ARRAY => 'array('.\count($value).')',
            // @phpstan-ignore ternary.condNotBoolean
            DataTypeEnum::BOOLEAN => 'boolean:'.($value ? 'true' : 'false'),
            // @phpstan-ignore binaryOp.invalid
            DataTypeEnum::FLOAT => 'float:'.$value,
            // @phpstan-ignore binaryOp.invalid
            DataTypeEnum::INTEGER => 'integer:'.$value,
            DataTypeEnum::NULL => 'null',
            // @phpstan-ignore argument.type
            DataTypeEnum::RESOURCE => 'resource:'.\get_resource_type($value),
            DataTypeEnum::RESOURCE_CLOSED,
            DataTypeEnum::UNKNOWN => $valueType->value,
            // @phpstan-ignore argument.type
            DataTypeEnum::OBJECT => $this->getClassName($value),
            // @phpstan-ignore binaryOp.invalid
            DataTypeEnum::STRING => '"'.$value.'"',
        };

        $to = \is_string($toType) ? $toType : $toType->value;
        $message = 'Error converting value. from='.$from.' to='.$to;

        parent::__construct($message, 0, $previous);
    }

    /**
     * @param object $object
     * @return non-empty-string
     */
    private function getClassName(object $object): string
    {
        // we are trying to avoid reflection classes
        //return (new \ReflectionClass($object))->getShortName();

        $pos = \mb_strrpos($object::class, '\\');
        $name = (false === $pos)
            ? $object::class
            : \mb_substr($object::class, $pos + 1);

        return ('' === $name) ? 'n/a' : $name;
    }
}
