<?php


namespace Araqel\Archive\Exceptions;

/**
 * Class ArchiveWrongTypeException
 *
 * @package Araqel\Archive\Exceptions
 */
class ArchiveWrongTypeException extends ArchiveException
{
    /**
     * @var
     */
    protected $type;

    /**
     * @var
     */
    protected $received_type;

    /**
     * @var
     */
    protected $message;

    /**
     * ArchiveWrongTypeException constructor.
     *
     * @param $type
     * @param $received_type
     */
    public function __construct($type, $received_type)
    {
        $this->type = $type;
        $this->received_type = $received_type;
        $this->setMessage();

        parent::__construct($this->message, 0, null);
    }

    /**
     *
     */
    public function setMessage()
    {
        $this->message = sprintf('Wrong argument type, must be %s got %s instead', strtoupper($this->type), strtoupper($this->received_type));

    }


}