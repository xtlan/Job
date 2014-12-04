<?php
namespace Xtlan\Job\Component;

use yii\base\Object;

/**
 * Description of YiiProcessRunner
 *
 * @author art3mk4 <Art3mk4@gmail.com>
 */
class ProcessRunner extends Object
{
    /**
     *
     * @var type 
     */
    private $_path;
    
    /**
     *
     * @var type 
     */
    private $_yiiparameters;

    /**
     * _lastCommand
     *
     * @var string
     */
    private $_lastCommand;
    
    /**
     * 
     * @param string $path
     * @param array $yiiparameters
     */
    public function __construct($path = null, array $yiiparameters = array())
    {
        $this->_path = isset($path) ? $path : Yii::$app->getAlias('@app');
        
        $this->_yiiparameters = $this->parseParams($yiiparameters);
    }

    /**
     * execCommand
     *
     * direct exec с прерыванием
     * 
     * @param string $nameCommand
     * @param string $nameAction
     * @param array $parameters
     * @return mixed
     */
    public function execCommand($nameCommand, $nameAction = null, array $parameters = array())
    {
        $this->_lastCommand = $this->prepareCommand($nameCommand, $nameAction, $parameters);
        exec($this->_lastCommand, $result);
        return $result;
    }


    /**
     * runCommand
     *
     * background exec в фоновом режиме
     * 
     * @param string $nameCommand
     * @param string $nameAction
     * @param array $parameters
     * @return int
     */
    public function runCommand($nameCommand, $nameAction = null, $parameters = array())
    {
        $command = $this->prepareCommand($nameCommand, $nameAction, $parameters);
        $this->_lastCommand = "nohup {$command} > /dev/null 2>&1 & echo $!";
        $pid = exec($this->_lastCommand);
        if  (!$this->isRunCommand($pid)) {
            throw new \Exception("Команда {$nameCommand} не запустилась {$pid}");
        }
        
        return $pid;
    }
    
    /**
     * dumpLastCommand
     *
     * @return void
     */
    public function dumpLastCommand()
    {
        return $this->_lastCommand;
    }


    /**
     * stopCommand
     * 
     * @param type $pid
     */
    public function stopCommand($pid)
    {
        exec("kill $pid");
    }
    
    /**
     * isRunCommand
     * 
     * @param type $pid
     * @return boolean
     */
    public function isRunCommand($pid)
    {
        $status = array();
        exec("ps -p $pid", $status);
        if (count($status) >= 2) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * prepareCommand
     *
     * @param string $nameCommand
     * @param string $nameAction
     * @param array $params
     * @return string
     */
    private function prepareCommand($nameCommand, $nameAction = null, array $params)
    {
        $yii = "{$this->_path}/yii {$this->_yiiparameters} ";
        $command = $nameCommand;
        if (isset($nameAction)) {
            $command .= '/' . $nameAction;
        }

        $params =  $this->parseParams($params);

        return "{$yii} {$command} {$params}";
    }

    /**
     * parseParams
     * 
     * @param type $parameters
     * @return type
     */
    private function parseParams($parameters)
    {
        $paramString = '';
        foreach ($parameters as $parameter => $value) {
            if (is_numeric($parameter)) {
                $paramsString .= " $value";
            } else {
                $paramString .= " --$parameter=$value";
            }
        }
        return $paramString;
    }
    
}
