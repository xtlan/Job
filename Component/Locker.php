<?php
namespace Xtlan\Job\Component;

use Yii;

class LockerException extends \Exception {}
/**
 * Locker
 *
 * Работа блокировщика основана на 2 файлах
 *
 * 1 файл - файл на котором блокируется сам блокировшик 
 * на время создания 2 типа файла
 * Это постоянный файл создаваемый один раз в runtime
 * (flock)
 *
 * 2 файл - файл зависящий от ключевого слова (хеш)
 * показывает что процесс уже идет
 * Это временный файл создаваемый и умираемый постоянно
 * 
 *
 * @version 1.0.0
 * @copyright Copyright 2011 by Kirya <cloudkserg11@gmail.com>
 * @author Kirya <cloudkserg11@gmail.com>
 */
class Locker
{
    const FLOCK = 'flock';

    /**
     * _maxTime
     *
     * @var int
     */
    protected $_maxTime;

    /**
     * _workFile
     *
     * @var string
     */
    protected $_workFile;

    /**
     * _lockFile
     *
     * @var string
     */
    protected $_lockFile;

    /**
     * __construct
     *
     * @param string $key
     * @param string|null $path
     * @param int|null $maxTime
     * @return void
     */
    public function __construct($key, $path = null, $maxTime =null)
    {
        if (!isset($path)) {
            $path = Yii::getAlias('@app/runtime');
        }

        if (!isset($maxTime)) {
            $maxTime = 3 * 60 * 60;
        }
        $this->_maxTime = $maxTime;

        $this->_lockFile = $path . '/' . self::FLOCK;
        $this->_workFile = $path . '/' . md5($key);
    }

    /**
     * lock
     *
     * Проверяет работает ли уже процесс 
     * есть ли fwork
     * Если есть - возвращает false
     *
     * Иначе блокируемся на flock
     * и создаем fwork
     * Возвращаем true
     *
     *
     * @return boolean
     */
    public function lock()
    {
        if ($this->existWorkFile()) {
            return false;
        }

        $lockHandle = $this->openLockFile(); 

        //Входим в режим блокировки чтобы создать файл-показатель одного процесс
        try{
            $this->lockFile($lockHandle);
            $this->createWorkFile();
        
        } catch (Exception $e) {
            $this->unlockFile($lockHandle);
            throw new \Exception($e->getMessage());
        }

        //Выходим из режима блокировки
        $this->unlockFile($lockHandle);

        return true;
    }


    /**
     * unlock
     *
     * @return void
     */
    public function unlock()
    {
        if (!unlink($this->_workFile)) {
            throw new \Exception('Не удалось удалить файл показатель');
        } 
    }

    /**
     * existWorkFile
     *
     * @return boolean
     */
    private function existWorkFile()
    {
        if (!file_exists($this->_workFile)) {
            return false;
        }

        $mtime = filemtime($this->_workFile);
        if (time() - $mtime > $this->_maxTime) {
            unlink($this->_workFile);
            return false;
        }
        
        return true;
    }

    /**
     * openLockFile
     *
     * @return resourse
     */
    private function openLockFile()
    {
        if (!file_exists($this->_lockFile)) {
            touch($this->_lockFile);
        }

        $handle = fopen($this->_lockFile, 'r');
        if (!isset($handle)) {
            throw new \Exception('Не удалось открытить файл блокировки');
        }

        return $handle;
    }

    /**
     * lockFile
     *
     * @param resourse $handle
     * @return void
     */
    private function lockFile($handle)
    {
        if (!flock($handle, LOCK_EX)) {
            throw new \Exception('Не удалось заблокировать файл блокировки');
        }
    }

    /**
     * unlockFile
     *
     * @param resourse $handle
     * @return void
     */
    private function unlockFile($handle)
    {
        if (!flock($handle, LOCK_UN)) {
            throw new \Exception('Не удалось разблокировать файл блокировки');
        }
    
    }

    /**
     * createWorkFile
     *
     * @return void
     */
    private function createWorkFile()
    {
        if(!touch($this->_workFile)) {
            throw new LockerException('Не удалось создать файл показатель');
        }
    }


    /**
     * getWorkFile
     *
     * @return string
     */
    public function getWorkFile()
    {
        return $this->_workFile;
    }


}
