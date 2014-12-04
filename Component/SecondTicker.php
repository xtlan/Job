<?php
namespace Xtlan\Job\Component;

/**
 * Description of SecondTicker
 *
 * @author art3mk4 <Art3mk4@gmail.com>
 */
class SecondTicker
{
    private $_limit;
    private $_mails = 0;
    private $_time;

    //Создаем обхект с лимитом
    public function __construct($limit)
    {
        $this->_limit = $limit;
        $this->_time = $this->getTime();

    }

    //Если секунда новая обновляем счетчик
    //Увеличивем на 1 количство писем в секунду
    //Проверяем пределы и ждем требуемое количество времени
    public function tick()
    {
        //Если текущая секунда не равна сохраненной
        //то обновляем данные
        if ($this->isEnded()) {
            $this->clear();    
        }

        //Увеличиваем счетчик посланных писем
        $this->_mails++;
        //Если мы превысили пределе
        //То дожидаемся оставшееся время в текущей секунде
        if ($this->_mails > $this->_limit) {
            usleep($this->leftMicroseconds()+300000);
            //начинаем новую секунду с одного письма
            $this->_time = $this->getTime();
            $this->_mails = 1;
        }
    }
    
    /**
     * leftMicroseconds
     * 
     * @return int
     */
    private function leftMicroseconds()
    {
        $timeDiff = ($this->_time+1)-(microtime(true));
        if ($timeDiff < 0) {
            return 0;
        }
        
        return round($timeDiff,6)*1000000;
    }

    //Проверяем что секунда кончилась
    private function isEnded() {
        //Сравниваем текущую секунду и имеющуюся
        return $this->getTime() !== $this->_time;
    }

    //Очищаем данные секунды
    private function clear()
    {
        //Обновляем количество писем в секунду
        //Приравниваем текущее время
        $this->_time = $this->getTime();
        $this->_mails = 0;
    }
    
    private function getTime()
    {
        return floor(microtime(true));
    }
}
