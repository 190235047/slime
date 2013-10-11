<?php
namespace Slime\Component\Html;

use Psr\Log\LoggerInterface;

class Pagination_Core
{
    public function __construct(LoggerInterface $Log)
    {
        $this->Log = $Log;
    }

    public function run($iTotalItem, $iNumPerPage, $iCurrentPage, $iDisplayBefore = 3, $iDisplayAfter = null)
    {
        if ($iCurrentPage < 1) {
            $this->Log->error('Offset can not be less than 1');
            exit(1);
        }
        if ($iTotalItem == 0) {
            return array();
        }

        if (empty($iDisplayAfter)) {
            $iDisplayAfter = $iDisplayBefore;
        }

        $iTotalPage = (int)ceil($iTotalItem / $iNumPerPage);
        if ($iCurrentPage > $iTotalPage) {
            $this->Log->error('Offset can not be more than total page');
            exit(1);
        }

        # count start
        if ($iCurrentPage - $iDisplayBefore <= 0) {
            $iAddAfter = $iDisplayBefore - $iCurrentPage + 1;
            $iStart    = 1;
        } else {
            $iAddAfter = 0;
            $iStart    = $iCurrentPage - $iDisplayBefore;
        }

        # count end
        if ($iCurrentPage + $iAddAfter + $iDisplayAfter > $iTotalPage) {
            $iEnd = $iTotalPage;
        } else {
            $iEnd = $iCurrentPage + $iAddAfter + $iDisplayAfter;
        }

        # build array
        $aResult = array();
        for ($i = $iStart; $i <= $iEnd; $i++) {
            if ($i == $iCurrentPage) {
                $aResult[] = 0 - $i;
            } else {
                $aResult[] = $i;
            }
        }

        # add first page and last page
        if (abs($aResult[0]) > 1) {
            array_unshift($aResult, 0);
            array_unshift($aResult, 1);
        }
        if (abs($aResult[count($aResult) - 1]) < $iTotalPage) {
            array_push($aResult, 0);
            array_push($aResult, $iTotalPage);
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

        return new Pagination_Bean($iPre, $aResult, $iNext, $iTotalPage);
    }
}