<?php
namespace Xtlan\Job\Component\Enum;

use Xtlan\Core\Component\ConstEnum;

/**
 * JobStatus
 *
 * @version 1.0.0
 * @copyright Copyright 2011 by Kirya <cloudkserg11@gmail.com>
 * @author Kirya <cloudkserg11@gmail.com>
 */
class JobStatus extends ConstEnum
{

    const CREATED = 0;
    const PROCESS = 1;
    const FINISH = 2;
    const ERROR = 3;


    /**
     * _titles
     *
     * @var array
     */
    public function getTitles()
    {
        return array(
            self::CREATED => 'Создано',
            self::PROCESS => 'Обрабатывается',
            self::FINISH => 'Завершено',
            self::ERROR => 'Завершено с ошибкой'
        );
    }


}
