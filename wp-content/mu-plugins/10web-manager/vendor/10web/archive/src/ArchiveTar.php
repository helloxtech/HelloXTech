<?php


namespace Araqel\Archive;

use Araqel\Archive\Exceptions\ArchiveNotWritableDirectoryException;
use Araqel\Archive\Exceptions\ArchiveWrongTypeException;
use Archive_Tar;

/**
 * Class ArchiveTar
 *
 * @package Araqel\Archive
 */
class ArchiveTar extends ArchiveBase
{
    /**
     * ArchiveTar constructor.
     *
     * @param        $archive_name
     *
     * @param string $method
     *
     * @throws ArchiveNotWritableDirectoryException
     */
    public function __construct($archive_name, $method='tar')
    {
        parent::__construct();
        $this->method = $method;
        $this->archive_name = $archive_name;

        $this->checkIsFolderWritable();

        $this->createArchiveFile();
    }

    /**
     * @return mixed|void
     */
    protected function createArchiveFile()
    {
        $this->archive = new Archive_Tar($this->archive_name, true);
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
        $this->archive->setIgnoreRegexp($regExp);
    }

    /**
     * Import single file to Tar archive
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
        if(!$this->checkFile($file)){
            return false;
        }

        if (!$this->archive->addModify(array($file), $add_dir, $rm_dir)) {

            $this->raiseError(sprintf('Cannot add "%s" to zip archive!', $file));

            return false;
        }
        $this->file_count++;

        return true;
    }

    /**
     * Batch import files to Tar archive
     *
     * @param        $files
     * @param        $add_dir
     * @param string $rm_dir
     *
     * @return bool|mixed
     * @throws ArchiveWrongTypeException
     */
    public function addFiles($files, $add_dir, $rm_dir = '')
    {
        $this->checkFiles($files);
        if (!$this->archive->addModify($files, $add_dir, $rm_dir)) {
            $this->raiseError(sprintf('something went wrong when adding batch files to archive'));

            return false;
        }
        $this->file_count += count($files);

        return true;

    }


    /**
     * @param $files
     *
     * @throws ArchiveWrongTypeException
     */
    protected function checkFiles($files)
    {
        if (!is_array($files)) {
            throw new ArchiveWrongTypeException('array', gettype($files));
        }
    }

    /**
     * Close Tar archive
     * @return mixed|void
     */
    public function close()
    {
        $this->archive->_close();
    }

    /**
     * @param        $dir
     *
     * @param        $addir
     * @param string $rmdir
     *
     * @return mixed|void
     */
    public function addEmptyFolder($dir, $addir, $rmdir='')
    {
        $this->archive->_dirCheck();

    }

    /**
     * @param $file
     * @param $add_dir
     * @param $rm_dir
     *
     * @return mixed|void
     */
    protected function preprocessFileName($file, $add_dir, $rm_dir)
    {

    }

}