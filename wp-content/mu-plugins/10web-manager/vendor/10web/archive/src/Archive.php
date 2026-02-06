<?php

namespace Araqel\Archive;

use Araqel\Archive\Exceptions\ArchiveCannotCreateArchiveException;
use Araqel\Archive\Exceptions\ArchiveNotWritableDirectoryException;

/**
 * Class Archive
 * used as factory for creating archives
 *
 * @package Araqel\Archivator
 */
class Archive
{
    /**
     * Archive constructor.
     */
    private function __construct()
    {
    }

    /**
     * Creates and returns Archive
     *
     * @param $name
     * @param string $type
     *
     * @return ArchiveBase
     * @throws ArchiveCannotCreateArchiveException
     * @throws ArchiveNotWritableDirectoryException
     */
    public static function create($name, $type = 'zip')
    {
        switch ($type) {
            case 'zip':
                return new ArchiveZip($name);
                break;
            case 'nelexa_zip':
                return new ArchiveNelexaZip($name);
                break;
            case 'nelexa_zip_stream':
                return new ArchiveNelexaZipStream($name);
                break;
//            case 'alchemy_zip':
//                return new ArchiveAlchemyZip($name);
//                break;
            case 'tar':
            case 'gzip':
                return new ArchiveTar($name);
                break;
            default:
                throw new ArchiveCannotCreateArchiveException('Type '.$type.' unsupported, please use zip or tar instead');
        }
    }

}