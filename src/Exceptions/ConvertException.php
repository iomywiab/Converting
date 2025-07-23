<?php
/*
 * Copyright (c) 2022-2025 Iomywiab/PN, Hamburg, Germany. All rights reserved
 * File name: ConvertException.php
 * Project: Converting
 * Modified at: 23/07/2025, 19:49
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
            DataTypeEnum::ARRAY => 'array('.\count($value).')',
            DataTypeEnum::BOOLEAN => 'boolean:'.($value ? 'true' : 'false'),
            DataTypeEnum::FLOAT => 'float:'.$value,
            DataTypeEnum::INTEGER => 'integer:'.$value,
            DataTypeEnum::NULL => 'null',
            DataTypeEnum::RESOURCE => 'resource:'.\get_resource_type($value),
            DataTypeEnum::RESOURCE_CLOSED,
            DataTypeEnum::UNKNOWN => $valueType->value,
            DataTypeEnum::OBJECT => $this->getClassName($value),
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
        try {
            $name = (new \ReflectionClass($object))->getShortName();
        } catch (\Throwable $cause) {
            $name = $cause->getMessage();
        }

        return ('' === $name) ? 'n/a' : $name;
    }
}
