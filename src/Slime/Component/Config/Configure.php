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
final class Configure implements IAdaptor
{
    private $Object;

    public function __construct($sAdaptor, LoggerInterface $Log)
    {
        $sAdaptor = $sAdaptor[0] === '@' ? __NAMESPACE__ . '\\Adaptor_' . substr($sAdaptor, 1) : $sAdaptor;
        $Class    = new \ReflectionClass($sAdaptor);
        $aParam   = array_slice(func_get_args(), 2);
        $aParam[] = $Log;
        $Object   = $Class->newInstanceArgs($aParam);
        if (!$Object instanceof IAdaptor) {
            $Log->error('Configure is not instance of Slime.IAdaptor');
            exit(1);
        }
        $this->Object = $Object;
    }

    /**
     * @param string $sKey
     * @param mixed  $sDefaultValue
     * @param int    $iErrorLevel
     *
     * @return mixed
     */
    public function get($sKey, $sDefaultValue = null, $iErrorLevel = 0)
    {
        return $this->Object->get($sKey, $sDefaultValue, $iErrorLevel);
    }
}
