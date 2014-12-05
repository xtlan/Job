<?php
namespace Xtlan\Job\Component;
class JobManagerException extends \Exception {};
use Yii;
use Xtlan\Job\Model\Job;
use Xtlan\Job\Model\JobStatus;
use Xtlan\Job\Model\JobQuery;

/**
 * JobManager
 *
 * @version 1.0.0
 * @copyright Copyright 2011 by Kirya <cloudkserg11@gmail.com>
 * @author Kirya <cloudkserg11@gmail.com>
 */
class JobManager
{

    /**
     * Интервал после которого задача считается ZOMBIE
     * в сек
     */
    const ZOMBIE_MIN_INTERVAL = 600;


    /**
     * _processRunner
     *
     * @var ProcessRunnerInterface
     */
    private $_processRunner;

    /**
     * _job
     *
     * @var Job
     */
    private $_job;

    /**
     * Gets the value of processRunner
     *
     * @return ProcessRunnerInterface
     */
    public function getProcessRunner()
    {
        if (!isset($this->_processRunner)) {
            $this->_processRunner = new ProcessRunner(
                Yii::$app->getAlias('@app')
            );
        }
        return $this->_processRunner;
    }
    
    /**
     * setProcessRunner
     *
     * @param ProcessRunner $processRunner
     * @return void
     */
    public function setProcessRunner(ProcessRunner $processRunner)
    {
        $this->_processRunner = $processRunner;
    }


    /**
     * getJobQuery
     *
     * @return JobQuery
     */
    public function getJobQuery()
    {
        return $this->_query;
    }
    
    /**
     * setJobQuery
     *
     * @param JobQuery $query
     * @return void
     */
    public function setJobQuery(JobQuery $query)
    {
        $this->_query = $query;
    }

    


    /**
     * start
     *
     * @param Job $job
     * @return void
     */
    public function start(Job $job)
    {
        $this->clearOldJobs($job->oldMinInterval, $job->uid);
        $this->clearZombieJobs();
  
        try {
            $pid = $this->getProcessRunner()->runCommand($job->name, $job->action, ['job_id' => $job->id]);
        } catch (\Exception $e) {
            throw new JobManagerException('Не удалось запустить комманду');
        }

        $job->setStart($pid);
    }


    /**
     * stop
     *
     * @param Job $job
     * @return void
     */
    public function stop(Job $job)
    {
         try {
            $this->getProcessRunner()->stopCommand($job->pid);
        } catch (\Exception $e) {
            throw new JobManagerException('Не удалось остановить комманду');
        }

         $job->setStop();

    }


    /**
     * isRun
     *
     * @param Job $job
     * @return boolean
     */
    public function isRun(Job $job)
    {
        return $this->getProcessRunner()->isRunCommand($job->pid);
    }

    /**
     * clearOldJobs
     *
     * @param int $oldMinInterval
     * @param string $uid
     * @return void
     */
    private function clearOldJobs($oldMinInterval, $uid)
    {

        $yesterday = time() - $oldMinInterval;
        $query = $this->getJobQuery();
        $query->andWhere(['<', 'start', $yesterday]);
        $query->forUid($uid);
        $jobs = $query->all();

        foreach ($jobs as $job) {
            if (!$job->delete()) {
                throw new \Exception('Невозможно удалить задачу');
            }
        }
        return true;
    }
    
    /**
     * clearZombieJobs
     *
     * delete all tasks with duration more than timeMinInterval
     *
     * @return void
     */
    private function clearZombieJobs()
    {
        $tenMinIntervall = time() - self::ZOMBIE_MIN_INTERVAL;
        $jobs = $this->getJobQuery()
            ->andWhere(['<', 'start', $tenMinIntervall])
            ->all();
        
        foreach ($jobs as $job) {
            $job->setStop();
        }
    }




    /**
     * checkError
     * 
     * @param Job $job
     */
    public function checkError(Job $job)
    {
        if ($job->isRun() && !$this->isRun($job)) {
            $job->status = JobStatus::ERROR;
            if (!$job->save()) {
                throw new JobManagerException('Ошибка');
            }
        }
    }


}
