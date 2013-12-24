<?php
namespace Slime\Component\Pagination;

use Slime\Component\RDS\Model;
use Slime\Component\Http;

/**
 * Class Automatic
 *
 * @package Slime\Component\Pagination
 * @author  smallslime@gmail.com
 */
class ModelPagination
{
    /**
     * @param Http\HttpRequest $HttpRequest
     * @param null|int         $iDefaultNumberPerPage (null: 10)
     * @param null|mixed       $mDefaultPageGetCBOrPageVar (null: page)
     * @param null|mixed       $mDefaultRender (null, array(self, defaultRender))
     */
    public function __construct(
        Http\HttpRequest $HttpRequest,
        $iDefaultNumberPerPage = null,
        $mDefaultPageGetCBOrPageVar = null,
        $mDefaultRender = null
    )
    {
        $this->HttpRequest                 = $HttpRequest;
        $this->iDefaultNumberPerPage      = $iDefaultNumberPerPage===null ? 10 : (int)$iDefaultNumberPerPage;
        $this->mDefaultPageGetCBOrPageVar = $mDefaultPageGetCBOrPageVar === null ? 'page' : $mDefaultPageGetCBOrPageVar;
        $this->mDefaultRender              = $mDefaultRender===null ? array('Slime\\Component\\Pagination\\ModelPagination', 'defaultRender') : $mDefaultRender;
    }

    public function getListFromRelation(
        Model\Item $Item,
        $sRelationModelName,
        &$List,
        $aWhere = array(),
        $sOrderBy = null,
        $iNumberPerPage = null,
        $mPageGetCBOrPageVar = null,
        $mRenderCB = null
    ) {
        return $this->_getList(
            array($Item, "count$sRelationModelName"),
            array($Item, $sRelationModelName),
            $List, $aWhere, $sOrderBy, $iNumberPerPage, $mPageGetCBOrPageVar, $mRenderCB
        );
    }

    public function getListFromRelationOnlyCount(
        Model\Item $Item,
        $sRelationModelName,
        &$List,
        $aWhere = array(),
        $sOrderBy = null,
        $iNumberPerPage = null,
        $mPageGetCBOrPageVar = null
    )
    {
        return $this->_getList(
            array($Item, "count$sRelationModelName"),
            array($Item, $sRelationModelName),
            $List, $aWhere, $sOrderBy, $iNumberPerPage, $mPageGetCBOrPageVar, null, true
        );
    }

    public function getList(
        Model\Model $Model,
        &$List,
        $aWhere = array(),
        $sOrderBy = null,
        $iNumberPerPage = null,
        $mPageGetCBOrPageVar = null,
        $mRenderCB = null
    ) {
        return $this->_getList(
            array($Model, 'findCount'),
            array($Model, 'findMulti'),
            $List, $aWhere, $sOrderBy, $iNumberPerPage, $mPageGetCBOrPageVar, $mRenderCB
        );
    }

    public function getListOnlyCount(
        Model\Model $Model,
        &$List,
        $aWhere = array(),
        $sOrderBy = null,
        $iNumberPerPage = null,
        $mPageGetCBOrPageVar = null
    )
    {
        return $this->_getList(
            array($Model, 'findCount'),
            array($Model, 'findMulti'),
            $List, $aWhere, $sOrderBy, $iNumberPerPage, $mPageGetCBOrPageVar, null, true
        );
    }

    protected function _getList(
        $mCountCB,
        $mListCB,
        &$List,
        $aWhere = array(),
        $sOrderBy = null,
        $iNumberPerPage = null,
        $mPageGetCBOrPageVar = null,
        $mRenderCB = null,
        $bOnlyCount = false
    )
    {
        # number per page
        $iNumberPerPage = max(1, $iNumberPerPage===null ? $this->iDefaultNumberPerPage : $iNumberPerPage);

        # current page
        if ($mPageGetCBOrPageVar===null) {
            $mPageGetCBOrPageVar = $this->mDefaultPageGetCBOrPageVar;
        }
        $iCurrentPage = is_string($mPageGetCBOrPageVar) ?
            max(1, (int)$this->HttpRequest->getGet($mPageGetCBOrPageVar)):
            (int)call_user_func($mPageGetCBOrPageVar);

        # get total
        $iTotalPage = call_user_func($mCountCB, $aWhere);

        # get pagination result
        $List    = call_user_func($mListCB, $aWhere, $sOrderBy, $iNumberPerPage, ($iCurrentPage - 1) * $iNumberPerPage);
        # if only count : return
        if ($bOnlyCount) {
            return $iTotalPage;
        }

        $aResult = Pagination::run($iTotalPage, $iNumberPerPage, $iCurrentPage);

        # render pagination result
        if ($mRenderCB===null) {
            $mRenderCB = $this->mDefaultRender;
        }
        $mRenderCB = $mRenderCB === null ?
            ($this->mDefaultRender === null ?  array($this, 'defaultRender') : $this->mDefaultRender) : $mRenderCB;
        $sPage = call_user_func($mRenderCB, $aResult);

        # result
        return $sPage;
    }

    public static function defaultRender(Http\HttpRequest $HttpRequest, $aResult)
    {
        $sURI = strstr($HttpRequest->getRequestURI(), '?', true);
        $Get  = $HttpRequest->Get;
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
                    $sPage .= $iPage < 0 ?
                        sprintf('<span>%s</span>', 0 - $iPage) :
                        sprintf(
                            '<a href="%s?%s">%s</a>',
                            $sURI,
                            $Get->set('page', $iPage)->buildQuery(),
                            $iPage
                        );
                }
            } else {
                $iPage = $aResult[$sK];
                $sPage .= $iPage <= 0 ?
                    $sV :
                    sprintf(
                        '<a href="%s?%s">%s</a>',
                        $sURI,
                        $Get->set('page', $iPage)->buildQuery(),
                        $sV
                    );
            }
            $sPage .= "</span>";
        }
        $sPage .= '</div>';

        return $sPage;
    }
}