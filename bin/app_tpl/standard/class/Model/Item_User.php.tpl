<?php
namespace {{{NS}}}\Model;

use {{{NS}}}\System\Model\Item_Base;

/**
 * Class Item_User
 *
 * @package {{{NS}}}\Model
 *
 * @property int $id
 * @property int $level
 * @property int $parent_id
 */
class Item_User extends Item_Base
{
    public function isJiShuYuan()
    {
        return $this->level == Model_User::LEVEL_DAQUJINGLI;
    }

    public function isDaQuJingLi()
    {
        return $this->level == Model_User::LEVEL_QUDAOJINGLI;
    }

    public function isQuDaoJingLi()
    {
        return $this->level == Model_User::LEVEL_DAQUJINGLI;
    }

    public function getIdentityName($sDefault = '未知')
    {
        return Model_User::getIdentityName($this->level, $sDefault);
    }

    const MAX_FIND_PARENT_TIMES = 5;

    /**
     * @param int | null $iLevel
     *
     * @return Item_User | null
     * @throws \RuntimeException
     */
    public function findParent($iLevel = null)
    {
        if ($iLevel === null) {
            $iLevel = $this->level >> 2;
        }
        $iLevel = max(Model_User::LEVEL_DAQUJINGLI, $iLevel);
        $U      = $this;
        $i      = 0;
        while ($U->level > $iLevel) {
            //@todo 这里可以根据Group优化多条SQL为一条
            $U = $U->Model->find(array('id' => $U->parent_id));
            if ($U === null) {
                throw new \RuntimeException("[ITEM] : User[{$this->id}] has no parent[level:$iLevel]");
            }
            if ($i++ >= self::MAX_FIND_PARENT_TIMES) {
                throw new \RuntimeException(
                    sprintf(
                        "[ITEM] : Finding user[{$this->id}] parent[level:$iLevel] is reached max times[%d]",
                        self::MAX_FIND_PARENT_TIMES
                    )
                );
            }
        }
        return $U;
    }

    /**
     * @param string | null $sOrderBy
     * @param int | null    $iLimit
     * @param int | null    $iOffset
     *
     * @return Item_User[] | \Slime\Component\RDS\Model\Group
     * @throws \RuntimeException
     */
    public function getChildren($sOrderBy = null, $iLimit = null, $iOffset = null)
    {
        if ($this->isJiShuYuan()) {
            throw new \RuntimeException("[ITEM] : User {$this->id} has no children");
        }
        return $this->Model->findMulti(array('parent_id' => $this->id), $sOrderBy, $iLimit, $iOffset);
    }

    public function getTotalJiShuYuanData($sDate)
    {
        if ($this->isJiShuYuan()) {
            throw new \RuntimeException('[ITEM] : Tec user can not be called');
        }
    }

    /**
     * @param \Slime\Component\Pagination\ModelPagination $Pagination
     * @param mixed                                       $List
     * @param string                                      $sDate
     * @param string                                      $sOrderBy
     * @param int                                         $iNumber
     *
     * @return mixed
     * @throws \RuntimeException
     */
    public function getPageListDataFromDaQuForQuDao($Pagination, &$List, $sDate, $sOrderBy = '', $iNumber = 20)
    {
        if (!$this->isDaQuJingLi()) {
            throw new \RuntimeException('[ITEM] : Only zone user be allowed called');
        }
        return $Pagination->getList(
            $this->Model->Factory->get('UserCount'),
            $List,
            array('date' => $sDate, 'level' => Model_User::LEVEL_DAQUJINGLI, 'user_id' => $this->id),
            $sOrderBy,
            $iNumber
        );
    }

    /**
     * @param \Slime\Component\Pagination\ModelPagination $Pagination
     * @param mixed                                       $List
     * @param string                                      $sDate
     * @param string                                      $sOrderBy
     * @param int                                         $iNumber
     *
     * @return mixed
     * @throws \RuntimeException
     */
    public function getPageListDataFromQuDaoForJiShuYuan($Pagination, &$List, $sDate, $sOrderBy = '', $iNumber = 20)
    {
        if (!$this->isQuDaoJingLi()) {
            throw new \RuntimeException('[ITEM] : Only channel user be allowed called');
        }
        return $Pagination->getList(
            $this->Model->Factory->get('UserCount'),
            $List,
            array('date' => $sDate, 'level' => Model_User::LEVEL_QUDAOJINGLI, 'user_id' => $this->id),
            $sOrderBy,
            $iNumber
        );
    }

    public function getDataFromTecUserSelf($sDate)
    {
        if (!$this->isJiShuYuan()) {
            throw new \RuntimeException('[ITEM] : Only tec user be allowed called');
        }
    }

    public function fetchInvitationCode($iNumber = 10)
    {
        if ($this->isJiShuYuan()) {
            throw new \RuntimeException('[ITEM] : Tec user can not fetch inv code');
        }
    }

    public function getPageListInvitationCode()
    {
        ;
    }
}
