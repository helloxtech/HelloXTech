<?php


namespace Araqel\Archive;

use Araqel\Archive\Exceptions\ArchiveCannotCreateArchiveException;
use Araqel\Archive\Exceptions\ArchiveWrongTypeException;
use PhpZip\ZipFile;

class ArchiveNelexaZip extends ArchiveBase
{
    public function __construct($archive_name)
    {
        parent::__construct();
        $this->method = 'zip';
        $this->archive_name = $archive_name;
        $this->checkIsFolderWritable();

        $this->createArchiveFile();
    }

    /**
     * @return mixed|void
     * @throws ArchiveCannotCreateArchiveException
     */
    protected function createArchiveFile()
    {
        $this->archive = new ZipFile();

        if (file_exists($this->archive_name)) {
            $this->archive->openFile($this->archive_name);
        }


    }

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

        if (!$this->archive->addFile($file, $zip_filename)) {

            $this->raiseError(sprintf('Cannot add "%s" to zip archive!', $file));

            return false;
        }

        $this->file_count++;

        return true;
    }

    /**
     * Batch import files to Zip archive
     *
     * @param        $files
     * @param        $add_dir
     * @param string $rm_dir
     *
     * @return mixed|void
     * @throws ArchiveWrongTypeException
     */
    public function addFiles($files, $add_dir, $rm_dir = '')
    {
        foreach ($files as $file) {
            if (!is_dir($file)) {
                $this->addFile($file, $add_dir, $rm_dir);
            } else {
                $this->addEmptyFolder($file, $add_dir, $rm_dir);
            }
        }

        $this->archive->saveAsFile($this->archive_name);
    }

    /**
     * Add empty directory to Zip archive
     *
     * @param        $dir
     *
     * @param        $addir
     * @param string $rmdir
     *
     * @return bool|mixed
     */
    public function addEmptyFolder($dir, $addir, $rmdir = '')
    {
        if (!is_dir($dir)) {
            return true;
        }

        $relative_dir = $this->preprocessFileName($dir, $addir, $rmdir);

        if (!$this->archive->addEmptyDir($relative_dir)) {
            $this->raiseWarning(sprintf('Cannot add "%s" empty dir to zip archive!', $dir));

            return false;
        }

        return true;

    }

    /**
     * Close Zip archive
     *
     * @return mixed|void
     */
    public function close()
    {
        if (is_object($this->archive)) {
            @$this->archive->close();
        }
    }

    /**
     * Set regexp to ignore form importing to Tar archive
     *
     * @param $regExp
     *
     * @return mixed|void
     */
    public function setIgnoreRegexp($regExp)
    {
        $this->ignore_regexp = $regExp;
    }

}