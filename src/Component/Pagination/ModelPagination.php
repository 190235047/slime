<?php
namespace Slime\Component\Pagination;

/**
 * Class Automatic
 *
 * @package Slime\Component\Pagination
 * @author  smallslime@gmail.com
 */
class ModelPagination
{
    /**
     * @param \Slime\Component\Http\HttpRequest $HttpRequest
     * @param null|int                          $iDefaultNumberPerPage      (null: 10)
     * @param null|mixed                        $mDefaultPageGetCBOrPageVar (null: page)
     * @param null|mixed                        $mDefaultRender             (null, array(self, defaultRender))
     */
    public function __construct(
        $HttpRequest,
        $iDefaultNumberPerPage = null,
        $mDefaultPageGetCBOrPageVar = null,
        $mDefaultRender = null
    ) {
        $this->HttpRequest                = $HttpRequest;
        $this->iDefaultNumberPerPage      = $iDefaultNumberPerPage === null ? 10 : (int)$iDefaultNumberPerPage;
        $this->mDefaultPageGetCBOrPageVar = $mDefaultPageGetCBOrPageVar === null ? 'page' : $mDefaultPageGetCBOrPageVar;
        $this->mDefaultRender             = $mDefaultRender === null ? array(
            'Slime\\Component\\Pagination\\ModelPagination',
            'defaultRender'
        ) : $mDefaultRender;
    }

    /**
     * @param \Slime\Component\RDS\Model\Model $Item
     * @param string                           $sRelationModelName
     * @param mixed                            $List
     * @param array                            $aWhere
     * @param null | string                    $sOrderBy
     * @param null | int                       $iNumberPerPage
     * @param null | mixed                     $mPageGetCBOrPageVar
     * @param null | mixed                     $mRenderCB
     * @param mixed                            $iTotal
     *
     * @return mixed
     */
    public function getListFromRelation(
        $Item,
        $sRelationModelName,
        &$List,
        $aWhere = array(),
        $sOrderBy = null,
        $iNumberPerPage = null,
        $mPageGetCBOrPageVar = null,
        $mRenderCB = null,
        &$iTotal = null
    ) {
        return $this->getListFromCB(
            array($Item, "count$sRelationModelName"),
            array($Item, $sRelationModelName),
            $List,
            $aWhere,
            $sOrderBy,
            $iNumberPerPage,
            $mPageGetCBOrPageVar,
            $mRenderCB,
            $iTotal
        );
    }

    /**
     * @param \Slime\Component\RDS\Model\Model $Model
     * @param mixed                            $List
     * @param array                            $aWhere
     * @param null | string                    $sOrderBy
     * @param null | int                       $iNumberPerPage
     * @param null | mixed                     $mPageGetCBOrPageVar
     * @param null | mixed                     $mRenderCB
     * @param mixed                            $iTotal
     *
     * @return mixed
     */
    public function getList(
        $Model,
        &$List,
        $aWhere = array(),
        $sOrderBy = null,
        $iNumberPerPage = null,
        $mPageGetCBOrPageVar = null,
        $mRenderCB = null,
        $iTotal = null
    ) {
        return $this->getListFromCB(
            array($Model, 'findCount'),
            array($Model, 'findMulti'),
            $List,
            $aWhere,
            $sOrderBy,
            $iNumberPerPage,
            $mPageGetCBOrPageVar,
            $mRenderCB,
            $iTotal
        );
    }

    public function getListFromCB(
        $mCountCB,
        $mListCB,
        &$List,
        $aWhere = array(),
        $sOrderBy = null,
        $iNumberPerPage = null,
        $mPageGetCBOrPageVar = null,
        $mRenderCB = null,
        &$iToTal = null
    ) {
        # number per page
        $iNumberPerPage = max(1, $iNumberPerPage === null ? $this->iDefaultNumberPerPage : $iNumberPerPage);

        # current page
        if ($mPageGetCBOrPageVar === null) {
            $mPageGetCBOrPageVar = $this->mDefaultPageGetCBOrPageVar;
        }
        $iCurrentPage = is_string($mPageGetCBOrPageVar) ?
            max(1, (int)$this->HttpRequest->getG($mPageGetCBOrPageVar)) :
            (int)call_user_func($mPageGetCBOrPageVar);

        # get total
        $iTotalItem = call_user_func($mCountCB, $aWhere);
        $iToTal     = $iTotalItem;

        # get pagination data
        $aResult               = Core::run($iTotalItem, $iNumberPerPage, $iCurrentPage);
        $aResult['total_item'] = $iTotalItem;

        # get list data
        $List = call_user_func($mListCB, $aWhere, $sOrderBy, $iNumberPerPage, ($iCurrentPage - 1) * $iNumberPerPage);

        # render pagination result
        if ($mRenderCB === null) {
            $mRenderCB = $this->mDefaultRender;
        }
        $mRenderCB = $mRenderCB === null ?
            ($this->mDefaultRender === null ? array($this, 'defaultRender') : $this->mDefaultRender) : $mRenderCB;
        $sPage     = call_user_func($mRenderCB, $this->HttpRequest, $aResult);

        # result
        return $sPage;
    }

    /**
     * @param \Slime\Component\Http\HttpRequest $HttpRequest
     * @param array                             $aResult
     *
     * @return string
     */
    public static function defaultRender($HttpRequest, $aResult)
    {
        if (empty($aResult['list'])) {
            return '';
        }

        $sURI             = strstr($HttpRequest->getRequestURI(), '?', true);
        $Get              = $HttpRequest->Get;
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

    /**
     * @param \Slime\Component\Http\HttpRequest $HttpRequest
     * @param array                             $aResult
     *
     * @return array
     */
    public static function noRender($HttpRequest, $aResult)
    {
        return $aResult;
    }
}