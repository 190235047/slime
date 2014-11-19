<?php
namespace {{{NS}}}\Model;

use Slime\Component\RDBMS\ORM\Model;

class M_User extends Model
{
    protected $naField = array('id', 'name', 'password', 'create_time', 'last_update_time');
    protected $bUseFull = true;
}
