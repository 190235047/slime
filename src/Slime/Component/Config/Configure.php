<?php
namespace Slime\Component\Config;

use Psr\Log\LoggerInterface;

/**
 * Class Configure
 *
 * @package Slime\Component\Config
 * @author  smallslime@gmail.com
 * @version 1.0
 */
final class Configure
{
    /** @var \Slime\Component\Config\IAdaptor */
    private $Object;

    public function __construct($sAdaptor)
    {
        $sAdaptor = $sAdaptor[0] === '@' ? __NAMESPACE__ . '\\Adaptor_' . substr($sAdaptor, 1) : $sAdaptor;
        $Class    = new \ReflectionClass($sAdaptor);
        $aParam   = array_slice(func_get_args(), 1);
        $Object   = $Class->newInstanceArgs($aParam);
        if (!$Object instanceof IAdaptor) {
            throw new \Exception('Configure is not instance of \Slime\Component\Config\IAdaptor');
        }
        $this->Object = $Object;
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
        return $this->Object->get($sKey, $mDefaultValue, $bForce);
    }
}
