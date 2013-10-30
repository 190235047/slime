<?php
namespace Slime\Component\Config;

/**
 * Class Configure
 *
 * @package Slime\Component\Config
 * @author  smallslime@gmail.com
 */
final class Configure
{
    /**
     * @param string $sAdaptor
     *
     * @throws \Exception
     * @return IAdaptor
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
            throw new \Exception("{$sAdaptor} must implements Slime\\Component\\Configure\\IAdaptor");
        }
        return $Obj;
    }

    /**
     * @param string $sKey
     * @param mixed  $mDefaultValue
     * @param bool   $bForce
     *
     * @return mixed
     */
    public function get($sKey, $mDefaultValue = null, $bForce = false)
    {
        return $this->Obj->get($sKey, $mDefaultValue, $bForce);
    }

    /**
     * @return IAdaptor
     */
    public function getAdaptor()
    {
        return $this->Obj;
    }
}
