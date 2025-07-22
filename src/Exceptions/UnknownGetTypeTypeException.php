<?php
/*
 * Copyright (c) 2022-2025 Iomywiab/PN, Hamburg, Germany. All rights reserved
 * File name: UnknownGetTypeTypeException.php
 * Project: Converting
 * Modified at: 22/07/2025, 23:04
 * Modified by: pnehls
 */

declare(strict_types=1);

namespace Iomywiab\Library\Converting\Exceptions;

class UnknownGetTypeTypeException extends \Exception implements ConvertExceptionInterface
{
    /**
     * @param non-empty-string $getTypeType
     */
    public function __construct(string $getTypeType, null|\Throwable $previous = null)
    {
        parent::__construct('Unknown type from getType(). value="'.$getTypeType.'"', 0, $previous);
    }
}
