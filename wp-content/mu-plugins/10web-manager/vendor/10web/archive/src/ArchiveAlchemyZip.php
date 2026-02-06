<?php


namespace Araqel\Archive;

use Alchemy\Zippy\Zippy;
use Araqel\Archive\Exceptions\ArchiveCannotCreateArchiveException;
use Araqel\Archive\Exceptions\ArchiveWrongTypeException;

class ArchiveAlchemyZip extends ArchiveBase
{
    private $zippy;
    private $is_archive_file_created = false;

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
        $this->zippy = Zippy::load();

        if (file_exists($this->archive_name)) {
            $this->archive = $this->zippy->open($this->archive_name);
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

        if (!$this->is_archive_file_created) {
            $this->archive = $this->zippy->create($this->archive_name, [$zip_filename => $file]);
            $this->is_archive_file_created = true;

            return true;
        }

        if (!$this->archive->addMembers([$zip_filename => $file])) {

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

        return true;

    }

    /**
     * Close Zip archive
     *
     * @return mixed|void
     */
    public function close()
    {

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