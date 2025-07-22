<?php
/*
 * Copyright (c) 2022-2025 Iomywiab/PN, Hamburg, Germany. All rights reserved
 * File name: MissingConvertImplementationException.php
 * Project: Converting
 * Modified at: 22/07/2025, 23:04
 * Modified by: pnehls
 */

declare(strict_types=1);

namespace Iomywiab\Library\Converting\Exceptions;

class MissingConvertImplementationException extends \Exception implements ConvertExceptionInterface
{
    /**
     * @param non-empty-string $methodName
     */
    public function __construct(string $methodName, null|\Throwable $previous = null)
    {
        $message = 'Missing implementation. methodName="'.$methodName.'"';
        parent::__construct($message, 0, $previous);
    }
}
