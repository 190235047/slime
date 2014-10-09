<?php
namespace Slime\Component\RDBMS\ORM;

/**
 * Class Pagination
 *
 * @package Slime\Component\RDBMS\ORM
 * @author  smallslime@gmail.com
 */
class Pagination
{
    /**
     * @param \Slime\Component\Http\HttpRequest $HttpRequest
     * @param null|int                          $iNumPerPage
     * @param null|mixed                        $m_PageVar_PageVarCB
     * @param null|mixed                        $mCBRender
     */
    public function __construct(
        $HttpRequest,
        $iNumPerPage,
        $m_PageVar_PageVarCB = 'page',
        $mCBRender = array('\\Slime\\Component\\RDBMS\\ORM\\Pagination', 'renderDefault')
    ) {
        $this->HttpRequest         = $HttpRequest;
        $this->iNumPerPage         = $iNumPerPage;
        $this->m_PageVar_PageVarCB = $m_PageVar_PageVarCB;
        $this->mCBRender           = $mCBRender;
    }

    /**
     * @param \Slime\Component\RDBMS\ORM\Model      $Model
     * @param \Slime\Component\RDBMS\DAL\SQL_SELECT $SQL_SEL
     *
     * @return mixed
     */
    public function generate($Model, $SQL_SEL)
    {
        return $this->_generate(
            array($Model, 'findCount'),
            array($Model, 'findMulti'),
            $SQL_SEL
        );
    }

    /**
     * @param callable                              $mCBCount
     * @param callable                              $mCBList
     * @param \Slime\Component\RDBMS\DAL\SQL_SELECT $SQL_SEL
     *
     * @return array [page_string, List_Group, total_item, total_page, current_page, number_per_page]
     */
    public function _generate(
        $mCBCount,
        $mCBList,
        $SQL_SEL
    ) {
        # number per page
        $iNumPerPage = max(1, $this->iNumPerPage);

        # current page
        $iCurrentPage = is_string($this->m_PageVar_PageVarCB) ?
            max(1, (int)$this->HttpRequest->getG($this->m_PageVar_PageVarCB)) :
            (int)call_user_func($this->m_PageVar_PageVarCB);

        # get total
        $iToTal = call_user_func($mCBCount, $SQL_SEL);

        # get pagination data
        $aResult = self::doPagination($iToTal, $iNumPerPage, $iCurrentPage);

        $SQL_SEL->limit($iNumPerPage)->offset(($iCurrentPage - 1) * $iNumPerPage);

        # get list data
        $List = call_user_func($mCBList, $SQL_SEL);

        $sPage = $this->mCBRender === null ? $aResult : call_user_func($this->mCBRender, $this->HttpRequest, $aResult);

        # result
        return array($sPage, $List, $iToTal, $aResult['total_page'], $iCurrentPage, $iNumPerPage);
    }

    /**
     * @param \Slime\Component\Http\HttpRequest $HttpRequest
     * @param array                             $aResult
     *
     * @return string
     */
    public static function renderDefault($HttpRequest, $aResult)
    {
        if (empty($aResult['list'])) {
            return '';
        }

        $sURI             = strstr($HttpRequest->getRequestURI(), '?', true);
        $aGet             = $HttpRequest->BagGET->aData;
        $sPage            = '<div class="pagination">';
        $aResult['first'] = 1;
        foreach (
            array(
                'first' => '首页',
                'pre'   => '&lt;&lt;',
                'list'  => '',
                'next'  => '&gt;&gt',
                'total' => '末页'
            ) as $sK => $sV
        ) {
            $sPage .= "<span class=\"page-$sK\">";
            if ($sK === 'list') {
                foreach ($aResult[$sK] as $iPage) {
                    $aGet['page'] = $iPage;
                    $sPage .= $iPage < 0 ?
                        sprintf('<span>%s</span>', 0 - $iPage) :
                        sprintf(
                            '<a href="%s?%s">%s</a>',
                            $sURI,
                            http_build_query($aGet),
                            $iPage
                        );
                }
            } else {
                $iPage        = $aResult[$sK];
                $aGet['page'] = abs($iPage);
                $sPage .= $iPage <= 0 ?
                    $sV :
                    sprintf(
                        '<a href="%s?%s">%s</a>',
                        $sURI,
                        http_build_query($aGet),
                        $sV
                    );
            }
            $sPage .= "</span>";
        }
        $sPage .= '</div>';

        return $sPage;
    }

    /**
     * @param int      $iTotalItem
     * @param int      $iNumPerPage
     * @param int      $iCurrentPage
     * @param int      $iDisplayBefore
     * @param int|null $iDisplayAfter
     *
     * @return \ArrayObject [pre:int list:int[] next:int total:int] If pre||list[]||next < 0, it means abs(value) is current page
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public static function doPagination($iTotalItem, $iNumPerPage, $iCurrentPage, $iDisplayBefore = 3, $iDisplayAfter = null)
    {
        if ($iCurrentPage < 1) {
            throw new \InvalidArgumentException('[PAG] : Offset can not be less than 1');
        }
        if ($iTotalItem == 0) {
            return array();
        }

        if (empty($iDisplayAfter)) {
            $iDisplayAfter = $iDisplayBefore;
        }

        $iTotalPage = (int)ceil($iTotalItem / $iNumPerPage);
        if ($iCurrentPage > $iTotalPage) {
            throw new \LogicException('[PAG] : Offset can not be more than total page');
        }

        # count start
        $iStart = $iCurrentPage - $iDisplayBefore;
        $iEnd   = $iCurrentPage + $iDisplayAfter;

        $iFixStart = max(1, $iStart - max(0, $iCurrentPage + $iDisplayAfter - $iTotalPage));
        $iFixEnd   = min($iTotalPage, $iEnd + (0 - min(0, $iCurrentPage - $iDisplayBefore - 1)));

        # build array
        $aResult = array();
        for ($i = $iFixStart; $i <= $iFixEnd; $i++) {
            if ($i == $iCurrentPage) {
                $aResult[] = 0 - $i;
            } else {
                $aResult[] = $i;
            }
        }

        # build data
        $iPre  = $iCurrentPage - 1;
        $iNext = $iCurrentPage + 1;
        if ($iCurrentPage == 1) {
            $iPre = -1;
        }
        if ($iCurrentPage == $iTotalPage) {
            $iNext = 0 - $iTotalPage;
        }

        return new \ArrayObject(array('pre' => $iPre, 'list' => $aResult, 'next' => $iNext, 'total_page' => $iTotalPage));
    }
}