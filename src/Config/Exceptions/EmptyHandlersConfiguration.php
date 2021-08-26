<?php

namespace Grixu\Synchronizer\Config\Exceptions;

use Throwable;

class EmptyHandlersConfiguration extends \Exception
{
    public function __construct($message = "Empty default handlers configuration", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
