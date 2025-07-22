<?php
/*
 * Copyright (c) 2022-2025 Iomywiab/PN, Hamburg, Germany. All rights reserved
 * File name: ConvertException.php
 * Project: Converting
 * Modified at: 22/07/2025, 23:04
 * Modified by: pnehls
 */

declare(strict_types=1);

namespace Iomywiab\Library\Converting\Exceptions;

use Iomywiab\Library\Converting\Enums\DataTypeEnum;

class ConvertException extends \Exception implements ConvertExceptionInterface
{
    /**
     * @param mixed $value
     * @param DataTypeEnum $toType
     * @param \Throwable|null $previous
     */
    public function __construct(mixed $value, DataTypeEnum $toType, null|\Throwable $previous = null)
    {
        try {
            $fromType = DataTypeEnum::fromData($value);
        } catch (\Throwable) {
            $fromType = DataTypeEnum::UNKNOWN;
        }

        try {
            $val = \is_scalar($value) ? (string)$value : \gettype($value);
        } catch (\Throwable $cause) {
            $val = $cause->getMessage();
        }
        $className = (DataTypeEnum::OBJECT === $fromType) ? '<'.\get_class($value).'>' : '';

        $message = 'Error converting value. from="'.$fromType->name.$className.'" to="'.$toType->name.'" value=['.$val.']';

        parent::__construct($message, 0, $previous);
    }
}
