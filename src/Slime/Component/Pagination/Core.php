<?php
namespace Slime\Component\Pagination;

/**
 * Class Core
 *
 * @package Slime\Component\Pagination
 * @author  smallslime@gmail.com
 */
class Core
{
    public static function run($iTotalItem, $iNumPerPage, $iCurrentPage, $iDisplayBefore = 3, $iDisplayAfter = null)
    {
        if ($iCurrentPage < 1) {
            throw new \Exception('Offset can not be less than 1');
        }
        if ($iTotalItem == 0) {
            return array();
        }

        if (empty($iDisplayAfter)) {
            $iDisplayAfter = $iDisplayBefore;
        }

        $iTotalPage = (int)ceil($iTotalItem / $iNumPerPage);
        if ($iCurrentPage > $iTotalPage) {
            throw new \Exception('Offset can not be more than total page');
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

        return new \ArrayObject(array('pre' => $iPre, 'list' => $aResult, 'next' => $iNext, 'total' => $iTotalPage));
    }
}