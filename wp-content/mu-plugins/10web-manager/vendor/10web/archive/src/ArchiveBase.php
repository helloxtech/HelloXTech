<?php


namespace Araqel\Archive;

use Araqel\Archive\Exceptions\ArchiveNotWritableDirectoryException;
use Araqel\Archive\Exceptions\ArchiveWrongTypeException;

/**
 * Class ArchiveBase
 *
 * @package Araqel\Archive
 */
abstract class ArchiveBase
{
    /**
     * @var string
     */
    protected $archive_name;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var int
     */
    protected $file_count = 0;

    /**
     * @var
     */
    protected $archive;

    /**
     * @var string
     */
    protected $ignore_regexp;

    /**
     * @var array
     */
    protected $errors = array();

    /**
     * @var array
     */
    protected $warnings = array();

    /**
     * @var boolean
     */
    protected $is_win = false;

    /**
     * Archive constructor.
     */
    public function __construct()
    {
        $this->setIsWin();
    }


    /**
     * @throws ArchiveNotWritableDirectoryException
     */
    protected function checkIsFolderWritable()
    {
        if (!is_dir(dirname($this->archive_name)) || !is_writable(dirname($this->archive_name))) {
            throw new ArchiveNotWritableDirectoryException(dirname($this->archive_name), 'Folder %s for archive not found or not writable');
        }

    }

    /**
     * @param $file
     *
     * @return bool
     * @throws ArchiveWrongTypeException
     */
    protected function checkFile($file)
    {
        if (!is_string($file)) {
            throw new ArchiveWrongTypeException('string', gettype($file));
        }

        if (!is_readable($file)) {
            $this->raiseWarning(sprintf('File %s does not exist or is not readable', $file));

            return false;
        }

        if ($this->ignore_regexp !== null && preg_match($this->ignore_regexp, '/' . $file)) {
            $this->raiseWarning(sprintf('File %s skipped', $file));

            return false;
        }

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
        foreach ($files as $file) {
            $this->checkFile($file);
        }
    }

    /**
     * @return mixed
     */
    protected function getMethod()
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    protected function getArchiveName()
    {
        return $this->archive_name;
    }

    /**
     * @return int
     */
    public function getFileCount()
    {
        return $this->file_count;
    }

    /**
     *
     */
    public function archiveReload()
    {
        $this->close();
        $this->createArchiveFile();
    }

    /**
     * @param $message
     */
    protected function raiseError($message)
    {
        $this->errors[] = $message;
    }

    /**
     * @param $message
     */
    protected function raiseWarning($message)
    {
        $this->warnings[] = $message;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function getWarnings()
    {
        return $this->warnings;
    }

    /**
     * @return int
     */
    public function getErrorCount()
    {
        return count($this->getErrors());
    }

    /**
     * @return int
     */
    public function getWarningCount()
    {
        return count($this->getWarnings());
    }

    /**
     * @return array
     */
    public function getSummary()
    {
        return array(
            'files_added' => $this->getFileCount(),
            'errors'      => $this->getErrorCount(),
            'warnings'    => $this->getWarningCount(),
        );
    }

    /**
     * @param $file
     *
     * @param $add_dir
     * @param $rm_dir
     *
     * @return mixed
     */
    protected function preprocessFileName($file, $add_dir, $rm_dir)
    {
        if ($this->isWin()) {
            $file = strtr($file, '\\', '/');
            $add_dir = strtr($add_dir, '\\', '/');
            $rm_dir = strtr($rm_dir, '\\', '/');
        }

        $store_name = $file;

        if ($rm_dir != '') {
            if (substr($rm_dir, -1) != '/') {
                $rm_dir .= '/';
            }

            if (substr($file, 0, strlen($rm_dir)) == $rm_dir) {
                $store_name = substr($file, strlen($rm_dir));
            }
        }

        if ($add_dir != '') {
            if (substr($add_dir, -1) == '/') {
                $store_name = $add_dir . $store_name;
            } else {
                $store_name = $add_dir . '/' . $store_name;
            }
        }

        return $store_name;
    }

    /**
     *
     */
    protected function setIsWin()
    {
        $this->is_win = substr(PHP_OS, 0, 3) === 'WIN';
    }

    /**
     * @return bool
     */
    protected function isWin(){
        return $this->is_win;
    }

    /**
     * Init new archive
     * @return mixed
     */
    abstract protected function createArchiveFile();

    /**
     * Close archive
     * @return mixed
     */
    abstract public function close();

    /**
     * Add a single file
     *
     * @param        $file
     * @param        $add_dir
     * @param string $rm_dir
     *
     * @return mixed
     */
    abstract public function addFile($file, $add_dir, $rm_dir = '');

    /**
     * Add many files
     *
     * @param        $files
     * @param        $add_dir
     * @param string $rm_dir
     *
     * @return mixed
     */
    abstract public function addFiles($files, $add_dir, $rm_dir = '');

    /**
     * Add empty folder
     *
     * @param        $dir
     *
     * @param        $add_dir
     * @param string $rm_dir
     *
     * @return mixed
     */
    abstract public function addEmptyFolder($dir, $add_dir, $rm_dir = '');

    /**
     * set ignored files regexp
     *
     * @param $regExp
     *
     * @return mixed
     */
    abstract public function setIgnoreRegexp($regExp);


    /**
     * Gently close archive on destruct
     */
    public function __destruct()
    {
        //$this->close();
        $this->archive = null;
    }


}
