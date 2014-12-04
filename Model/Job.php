<?php
namespace Xtlan\Job\Model;
class JobException extends \Exception {};

use Xtlan\Core\Model\ActiveRecord;
use Xtlan\Job\Component\Enum\JobStatus;


/**
* Job
*
* @version 1.0.0
* @copyright Copyright 2011 by Kirya <cloudkserg11@gmail.com>
* @author Kirya <cloudkserg11@gmail.com>
*/
class Job extends ActiveRecord
{

    //const UID = 'job';

    /**
     * oldMinInterval
     * after that job delete
     *
     * @var int
     */
    public $oldMinInterval = 86400;

    /**
     * _params
     *
     * @var array
     */
    private $_params;

    /**
    * tableName
    *
    * @return string
    */
    public static function tableName()
    {
        return 'process_jobs';
    }

    /**
     * find
     *
     * @return JobQuery 
     */
    public static function find()
    {
        return new JobQuery(get_called_class());
    }

    /**
    * behaviors
    *
    * @return array
    */
    public function behaviors() 
    {
        return [
            [
                'class' => TimestampFieldBehavior::className(),
                'fields' => ['start', 'end']
            ]
        ];
    }


    /**
    * rules
    *
    * @return array
    */
    public function rules()
    {
        return [
            [['uid', 'name'], 'required'],

            [['pid', 'uid', 'name', 'action', 'status'], 'string', 'max' => 512],

            [['start', 'end', 'progress'], 'integer'],
            [['status', 'result'], 'string']
        ];
    }


    /**
     * setParams
     *
     * @param array $params
     * @return Job
     */
    public function setParams(array $params)
    {
        $this->encode_params = serialize($params);
        return $this;
    }

    /**
     * getStatusTitle
     *
     * @return string
     */
    public function getStatusTitle()
    {
        return JobStatus::getInstance()->getTitle($this->status);
    }
    
    /**
     * getParams
     *
     * @return array
     */
    public function getParams()
    {
        if (!isset($this->_params)) {
            $params = unserialize($this->encode_params);
            if (!array($params)) {
                $params = array();
            }

            $this->_params = $params;
        }

        return $this->_params;
    }

    /**
     * getParam
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        $params = $this->getParams();
        if (!isset($params[$name])) {
            return $default;
        }
        return $params[$name];
    }


    /**
     * setStart
     *
     * @return Job
     */
    public function setStart($pid)
    {
        $this->pid = $pid;
        $this->status = JobStatus::CREATED;
        $this->progress = 0;
        $this->start = time();
        if (!$this->save()) {
            throw new JobException('Не сохранена информация о задаче');
        }

        return $this;
    
    }

    /**
     * setProcess
     *
     * @return void
     */
    public function setProcess()
    {
        $this->status = JobStatus::PROCESS;
        if (!$this->save()) {
            throw new JobException('Не сохранена информация о запуске задачи');    
        }
    }


    /**
     * setStop
     *
     * @return Job
     */
    public function setStop()
    {
        $this->status = JobStatus::FINISH;
        $this->progress = 100;
        $this->end = time();
        if (!$this->update(array('status', 'progress', 'end'))) {
            throw new JobException('Не сохранена информация о задаче');
        }

        return $this;
    }


    /**
     * isRun
     *
     * @return boolean
     */
    public function isRun()
    {
        if ($this->status === JobStatus::CREATED) {
            return true;
        }

        if ($this->status === JobStatus::PROCESS) {
            return true;
        }

        return false;
    }



    /**
     * updateProgress
     *
     * @param mixed $progress
     * @return void
     */
    public function updateProgress($progress)
    {
        $this->progress = $progress;
        if (!$this->save()) {
            throw new JobException(
                'Не удалось обновить прогресс' . print_r($progress->errors, true)
            );
        }
    }


    


}
