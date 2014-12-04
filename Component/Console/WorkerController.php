<?php
namespace Xtlan\Job\Component\Console;

use yii\base\Controller;
use Xtlan\Core\Helper\ConsoleHelper;

use Xtlan\Job\Component\Enum\JobStatus;
use Xtlan\Job\Model\Job;

/**
 * WorkerController
 *
 * @version 1.0.0
 * @copyright Copyright 2011 by Kirya <cloudkserg11@gmail.com>
 * @author Kirya <cloudkserg11@gmail.com>
 */
abstract class WorkerController extends Controller
{

    /**
     * _jobId
     *
     * @var int
     */
    private $_jobId;

    /**
     * _job
     *
     * @var Job
     */
    private $_job;

    /**
     * jobClass
     *
     * @var string
     */
    public $jobClass;

    /**
     * getJob
     *
     * @return Job
     */
    public function getJob()
    {
        if (!isset($this->_job)) {
            $jobClass = $this->jobClass;
            $this->_job = $jobClass::findOne($this->_jobId);
            if (!isset($this->_job)) {
                ConsoleHelper::error(
                    'Информация по задаче c id = ' . $this->_jobId . ' не найдена'
                );
            }
        }

        return $this->_job;
    }

    /**
     * setJob
     *
     * @param Job $job
     * @return void
     */
    public function setJob(Job $job)
    {
        $this->_job = $job;
    }

    /**
     * runAction
     *
     * @param mixed $id
     * @param mixed $params
     * @return void
     */
    public function runAction($id, $params = [])
    {
        if ($id == 'index') {
            $this->_jobId = $params['job_id'];
        }
        return parent::runAction($id, $params = []);
    }


    /**
     * actionIndex
     *
     * @param int $job_id
     * @return void
     */
    abstract public function actionIndex($job_id);

    /**
     * error
     *
     * @param string $msg
     * @return void
     */
    protected function error($msg)
    {
        if (isset($this->_job)) {
            $this->_job->status = JobStatus::ERROR;
            $this->_job->error = $msg;
            $this->_job->save();
        }
        ConsoleHelper::error($msg);
    }

    /**
     * updateProgress
     *
     * @param mixed $index
     * @param mixed $limit
     * @return void
     */
    protected function updateProgress($index, $limit)
    {
        $newProgress = round($index / $limit * 100);

        if ($newProgress - $this->job->progress > 1) {
            $this->job->updateProgress($newProgress);
        }
    }

}
