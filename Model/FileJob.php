<?php
namespace Xtlan\Job\Model;

/**
 * FileJob
 *
 * @version 1.0.0
 * @copyright Copyright 2011 by Kirya <cloudkserg11@gmail.com>
 * @author Kirya <cloudkserg11@gmail.com>
 */
abstract class FileJob extends Job
{

    /**
     * init
     *
     * @return void
     */
    public function init()
    {
        $path = $this->getFilePath();
        if (!is_dir($path)) {
            throw new Exception("Папки {$path} не существует.");
        }

        return parent::init();
    }


    /**
     * getFileFullname
     *
     * @return string
     */
    public function getFileFullname()
    {
        return $this->getFilePath() . '/' . $this->getFileName();
    }


    /**
     * getFilePath
     *
     * @return string
     */
    abstract public function getFilePath();

    /**
     * getFileName
     *
     * @return string
     */
    abstract public function getFileName();

    /**
     * afterDelete
     * 
     * (non-PHPdoc)
     * @see CActiveRecord::afterDelete()
     */
    public function afterDelete()
    {
        $filename = $this->getFileFullname();

        if (file_exists($filename)) {
            if (!unlink($filename)) {
                throw new \Exception("не удалось удалить файл: ".$filename);
            }
        }

        return parent::afterDelete();
    }

}
