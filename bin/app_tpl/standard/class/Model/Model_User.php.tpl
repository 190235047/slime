<?php
namespace {{{NS}}}\Model;

use Slime\Bundle\Framework\Context;
use {{{NS}}}\System\Model\Model_Base;

/**
 * Class Model_User
 *
 * @package {{{NS}}}\Model
 *
 * @method Item_User createItem() createItem(array $aData)
 * @method Item_User find() find(mixed $mPKOrWhere)
 * @method Item_User[] | \Slime\Component\RDS\Model\Group findMulti() findMulti(array $aWhere = array(), string $sOrderBy = null, int $iLimit = null, int $iOffset = null, string $sTable = null, string $sSelect = null)
 */
class Model_User extends Model_Base
{
    const LEVEL_DAQUJINGLI  = 1;
    const LEVEL_QUDAOJINGLI = 2;
    const LEVEL_JISHUYUAN   = 4;

    private static $aIdentityMap = array(
        self::LEVEL_DAQUJINGLI  => '大区经理',
        self::LEVEL_QUDAOJINGLI => '渠道经理',
        self::LEVEL_JISHUYUAN   => '技术员'
    );

    public function getUserFromBDUid($sBDUid)
    {
        return $this->find(array('bduid' => $sBDUid));
    }

    public static function getIdentityName($iLevel, $sDefault = '未知')
    {
        return isset(self::$aIdentityMap[$iLevel]) ? self::$aIdentityMap[$iLevel] : $sDefault;
    }

    /**
     * @return bool
     */
    public static function createUserFromGlobal()
    {
        $CTX = Context::getInst();
        $sBDUSS = $CTX->HttpRequest->getCookie('BDUSS');
        if ($sBDUSS===null) {
            return false;
        }

        //@todo
        return true;
    }
}
