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
class Automatic
{
    protected $HttpRequest;
    protected $iPerPage;
    protected $sVarPage;
    protected $mRenderCB;

    public function __construct(Http\HttpRequest $HttpRequest, $iPerPage = 10, $sVarPage = 'page', $mRenderCB = null)
    {
        $this->HttpRequest = $HttpRequest;
        $this->iPerPage    = (int)$iPerPage;
        $this->sVarPage    = (string)$sVarPage;
        $this->mRenderCB   = $mRenderCB;
    }

    public function getStandardListFromRelationModel(
        Model\Item $Item,
        $sRelationModelName,
        $aWhere = array(),
        $sOrderBy = null,
        $iPerPage = null,
        $mRenderCB = null,
        $bOnlyCount = false
    ) {
        return $this->_getList(
            array($Item, "count$sRelationModelName"),
            array($Item, $sRelationModelName),
            $aWhere, $sOrderBy, $iPerPage, $mRenderCB, $bOnlyCount
        );
    }

    public function getStandardList(
        Model\Model $Model,
        $aWhere = array(),
        $sOrderBy = null,
        $iPerPage = null,
        $mRenderCB = null,
        $bOnlyCount = false
    ) {
        return $this->_getList(
            array($Model, 'findCount'),
            array($Model, 'findMulti'),
            $aWhere, $sOrderBy, $iPerPage, $mRenderCB, $bOnlyCount
        );
    }

    protected function _getList(
        $mCountCB,
        $mListCB,
        $aWhere = array(),
        $sOrderBy = null,
        $iPerPage = null,
        $mRenderCB = null,
        $bOnlyCount = false
    )
    {
        $iPerPage = $iPerPage === null ? (int)$this->iPerPage : (int)$iPerPage;
        if ($iPerPage === 0) {
            throw new \Exception('Number per page must gt than 0');
        }
        if ($this->sVarPage==='') {
            throw new \Exception('Page var must be a string');
        }

        $iTotal  = call_user_func($mCountCB, $aWhere);
        $iPage   = max(1, (int)$this->HttpRequest->getGet($this->sVarPage));
        $aResult = $bOnlyCount ? array($iTotal, $iPerPage) : Pagination::run($iTotal, $iPerPage, $iPage);
        $Group   = call_user_func($mListCB, $aWhere, $sOrderBy, $iPerPage, ($iPage - 1) * $iPerPage);

        $mRenderCB = $mRenderCB === null ?
            ($this->mRenderCB === null ?  array($this, 'defaultRender') : $this->mRenderCB) : $mRenderCB;
        $sPage = call_user_func($mRenderCB, $aResult);

        return array($Group, $sPage);
    }

    public function defaultRender($aResult)
    {
        $sURI = strstr($this->HttpRequest->getRequestURI(), '?', true);
        $Get  = $this->HttpRequest->Get;
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

    public function ajaxRender($aResult)
    {
        return $aResult;
    }
}