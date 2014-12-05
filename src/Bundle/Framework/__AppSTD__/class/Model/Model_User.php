<?php
namespace AppSTD\Model;

use AppSTD\System\ORM\Model;

class Model_User extends Model
{
    protected $naField = array('id', 'name', 'password', 'create_time', 'last_update_time');
    protected $bUseFull = true;

    protected $nsDB = 'default';
    protected $sItemClass;
    protected $sTable;
    protected $sPKName;
    protected $sFKName;
    protected $aRelConf = array();
}
