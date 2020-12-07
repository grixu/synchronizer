<?php

namespace Grixu\Synchronizer\Exceptions;

use Exception;

/**
 * Class EmptyMd5FieldNameInConfigException
 * @package Grixu\Synchronizer\Exceptions
 */
class EmptyMd5FieldNameInConfigException extends Exception
{
    public function render()
    {
        return response()->json(
            [
                'title' => 'Empty configuration variable which describe field name in model which contains md5 checksum'
            ],
            500
        );
    }
}
