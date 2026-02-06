<?php


namespace Araqel\Archive\Exceptions;

/**
 * Class ArchiveNotWritableDirectoryException
 *
 * @package Araqel\Archive\Exceptions
 */
class ArchiveNotWritableDirectoryException extends ArchiveException
{
    /**
     * @var
     */
    private $path;

    /**
     * ArchiveNotWritableDirectoryException constructor.
     *
     * @param $path
     * @param string $message
     * @param int $code
     * @param null $previous
     */
    public function __construct($path, $message = "", $code = 0, $previous = null)
    {
        $this->path = $path;
        $this->message = sprintf('Folder %s for archive not found or not writable', $this->path);

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }


}