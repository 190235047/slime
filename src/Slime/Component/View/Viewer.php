<?php
namespace Slime\Component\View;

/**
 * Class View
 *
 * @package Slime\Component\View
 * @author  smallslime@gmail.com
 */
final class Viewer
{
    /**
     * @param string $sAdaptor
     *
     * @return IAdaptor
     * @throws \Exception
     */
    public static function factory($sAdaptor)
    {
        if ($sAdaptor[0] === '@') {
            $sAdaptor = __NAMESPACE__ . '\\Adaptor_' . substr($sAdaptor, 1);
        }
        $aParam = array_slice(func_get_args(), 1);
        if (empty($aParam)) {
            $Obj = new $sAdaptor();
        } else {
            $Ref = new \ReflectionClass($sAdaptor);
            $Obj = $Ref->newInstanceArgs($aParam);
        }
        if (!$Obj instanceof IAdaptor) {
            throw new \Exception("{$sAdaptor} must implements Slime\\Component\\View\\IAdaptor");
        }
        return $Obj;
    }
}
