<?php


namespace Araqel\Archive;

use Araqel\Archive\Exceptions\ArchiveCannotCreateArchiveException;
use Araqel\Archive\Exceptions\ArchiveWrongTypeException;
use PhpZip\ZipFile;

class ArchiveNelexaZipStream extends ArchiveNelexaZip
{



    /**
     * Import single file to Zip archive
     *
     * @param        $file
     * @param        $add_dir
     * @param string $rm_dir
     *
     * @return bool|mixed
     * @throws ArchiveWrongTypeException
     */
    public function addFile($file, $add_dir, $rm_dir = '')
    {
        if (!$this->checkFile($file)) {
            return false;
        }

        $zip_filename = $this->preprocessFileName($file, $add_dir, $rm_dir);

        $hanlder = fopen($file,'rb');

        if (!$this->archive->addFromStream($hanlder, $zip_filename)) {

            $this->raiseError(sprintf('Cannot add "%s" to zip archive!', $file));

            return false;
        }

        $this->file_count++;

        return true;
    }

}