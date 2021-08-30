<?php

namespace Grixu\Synchronizer\Process\Exceptions;

use Throwable;

class EmptyJobsConfiguration extends \Exception
{
    public function __construct($message = "Empty jobs configuration", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
