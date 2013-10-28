<?php
namespace Slime\Component\Pagination;

use Slime\Bundle\Framework\Context;
use Slime\Component\RDS\Model;
use Slime\Component\Http;

class Automatic
{
    protected $Context;
    protected $iPerPage;
    protected $sVarPage;
    protected $mRenderCB;

    public function __construct(Context $Context, $iPerPage = 10, $sVarPage = 'page', $mRenderCB = null)
    {
        $this->Context   = $Context;
        $this->iPerPage  = (int)$iPerPage;
        $this->sVarPage  = (string)$sVarPage;
        $this->mRenderCB = $mRenderCB;
    }

    public function getStandardList(
        Model\Model $Model,
        $aWhere = array(),
        $sOrderBy = null,
        $iPerPage = null,
        $mRenderCB = null
    ) {
        $Logger   = $this->Context->Log;
        $iPerPage = $iPerPage === null ? (int)$this->iPerPage : (int)$iPerPage;
        if ($iPerPage === 0) {
            $Logger->error('Number per page must gt than 0');
            exit(1);
        }
        if ($this->sVarPage==='') {
            $Logger->error('page var must be a string');
            exit(1);
        }
        $mRenderCB = $mRenderCB === null ? $this->mRenderCB : $mRenderCB;

        $HttpRequest = $this->Context->HttpRequest;

        $iTotal  = $Model->findCount($aWhere);
        $iPage   = max(1, (int)$HttpRequest->getGet($this->sVarPage));

        $aResult = Core::run($Logger, $iTotal, $iPerPage, $iPage);
        if (empty($aResult)) {
            return array(new Model\Group($Model), '');
        }

        $Group = $Model->findMulti($aWhere, $sOrderBy, $iPerPage, ($iPage - 1) * $iPerPage);

        if ($mRenderCB !== null) {
            $sPage = call_user_func($mRenderCB, $aResult);
        } else {

            $sURI = strstr($HttpRequest->getRequestURI(), '?', true);
            $Get  = $HttpRequest->Get;

            $sPlaceHolder                 = chr(0);
            $aParseBlock['query']['page'] = $sPlaceHolder;

            $sPage            = '<div class="pagination"></div>';
            $aResult['first'] = 1;
            foreach (array(
                         'first' => '首页',
                         'pre'   => '&lt;&lt;',
                         'list'  => '',
                         'next'  => '&gt;&gt',
                         'total' => '末页'
                     ) as $sK => $sV) {
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
        }

        return array($Group, $sPage);
    }
}