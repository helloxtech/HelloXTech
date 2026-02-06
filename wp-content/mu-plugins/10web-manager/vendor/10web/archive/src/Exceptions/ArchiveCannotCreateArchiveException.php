<?php


namespace Araqel\Archive\Exceptions;

/**
 * Class ArchiveCannotCreateArchiveException
 *
 * @package Araqel\Archive\Exceptions
 */
class ArchiveCannotCreateArchiveException extends ArchiveException
{
    /**
     * ArchiveCannotCreateArchiveException constructor.
     *
     * @param string $message
     * @param int $code
     * @param null $previous
     */
    public function __construct($message = "", $code = 0,  $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}