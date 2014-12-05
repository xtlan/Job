<?php
namespace Xtlan\Job\Component\Controller;

use yii\base\Behavior;
use Xtlan\Job\Component\JobManager;
use yii\base\Model;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;
use Yii;
use Xtlan\Core\Component\Ajax;
use Xtlan\Job\Component\ProcessRunner;

/**
 * CreateJobBehavior
 *
 * @version 1.0.0
 */
class CreateJobBehavior extends Behavior
{

    /**
     * workerName
     *
     * @var string
     */
    public $workerName;

    /**
     * actionName
     *
     * @var string
     */
    public $actionName = 'index';

    /**
     * modelName
     *
     * @var string
     */
    public $modelName;

    /**
     * _jobManager
     *
     * @var JobManager
     */
    private $_jobManager;


    /**
     * actionIndex
     */
    public function actionIndex()
    {
        return $this->render('index');
    }


    /**
     * startWithFilter
     *
     * @param Model $filter
     * @return void
     */
    public function startWithFilter(Model $filter)
    {
        if ($filter->load(Yii::$app->request->post()) and $filter->validate()) {
            return $this->start($filter->attributes);
        } 
        
        throw new BadReqeustHttpException('Данные не прошли валидацию'.print_r($filter->errors, true));
    }


    /**
     * start
     *
     * @param array $params
     * @return void
     */
    public function start($params = array())
    {
        $modelName = $this->modelName;

        $job = new $modelName;
        $job->name = $this->workerName;
        $job->action = $this->actionName;
        $job->params = $params;
        $job->uid = $job::UID;
        if (!$job->save()) {
            throw new BadRequestHttpException('Не удалось сохранить задачу'.print_r($job->errors, true));
        }

        try {
            $this->jobManager->start($job);
        } catch (\Exception $e) {
            throw new ServerErrorHttpException($e->getMessage());
        }

        $ajax = new Ajax;
        return $ajax->sendRespond(
            true,
            'Задача запущена успешно',
            array(
                'job_id' => $job->id
            )
        );
    }

    /**
     * actionInfo
     * 
     */
    public function actionInfo($job_id)
    {
        $job = $this->getJob($job_id);
        $this->jobManager->checkError($job);

        $ajax = new Ajax;
        return $ajax->sendRespond(
            true,
            'Идет выполнение команды',
            array(
                'finish'  => ($job->status != JobStatus::PROCESS),
                'progress' => $job->progress
            )
        );
    }

    /**
     * actionStop
     * 
     * @throws CHttpException
     */
    public function actionStop()
    {
        $job = $this->getJob(Yii::$app->request->post('job_id', 'NONE'));
            
        try {
            $this->jobManager->stop($job);
        } catch(\Exception $e) {
            throw new ServerErrorHttpException($e->getMessage());
        }

        if (!$job->delete()) {
            throw new ServerErrorHttpException($e->getMessage());
        }

        $ajax = new Ajax;
        return $ajax->sendRespond(true, 'Задача остановлена успешно');
    }

    /**
     * getJob
     * 
     * @param unknown $job_id
     * @return Xtlan\Job\Model\JobModel
     */
    public function getJob($job_id)
    {
        if (!is_numeric($job_id)) {
            throw new NotFoundHttpException('Идентификатор не верный');
        }


        $modelName = $this->modelName;
        $job = $modelName::findOne($job_id);

        if (!isset($job)) {
            throw new NotFoundHttpException('Нет задачи с данным id');
        }

        return $job;
    }


    /**
     * getJobManager
     *
     * @return JobManager
     */
    public function getJobManager()
    {
        if (!isset($this->_jobManager)) {
            $modelName = $this->modelName;

            $this->_jobManager = new JobManager();
            $this->_jobManager->setJobQuery($modelName::find());

            $this->_jobManager->setProcessRunner(
                new ProcessRunner(
                    Yii::getAlias('@app')
                )
            );
        }

        return $this->_jobManager;
    }

    /**
     * setJobManager
     *
     * @param obManager $jobManager
     * @return void
     */
    public function setJobManager(JobManager $jobManager)
    {
        $this->_jobManager = $jobManager;
    }

}
