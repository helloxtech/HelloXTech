<?php

namespace Araqel\Archive\Exceptions;

use Exception;

/**
 * Class ArchiveException
 *
 * @package Araqel\Archive\Exceptions
 */
class ArchiveException extends Exception
{
    /**
     * ArchiveException constructor.
     *
     * @param $message
     * @param $code
     * @param $previous
     */
    public function __construct($message, $code, $previous)
    {
        parent::__construct($message, $code, $previous);

    }

}