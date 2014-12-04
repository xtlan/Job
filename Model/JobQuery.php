<?php
namespace Xtlan\Job\Model;

use Xtlan\Core\Model\Query;

/**
* JobQuery
*
* @version 1.0.0
* @author Kirya <cloudkserg11@gmail.com>
*/
class JobQuery extends Query
{
    /**
     * forUid
     *
     * @param mixed $uid
     * @return ProcessJob
     */
    public function forUid($uid)
    {
        return $this->andWhere(['uid' => $uid]);
    }
    
}
