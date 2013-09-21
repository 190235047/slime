<?php
namespace SlimeFramework\Component\Config;

use SlimeFramework\Component\Config\IAdaptor;

/**
 * Class Configure
 * @package SlimeFramework\Component\Config
 * @author smallslime@gmail.com
 * @version 1.0
 */
final class Configure implements IAdaptor
{
    private $Object;

    public function __construct($sAdaptor)
    {
        $sAdaptor = $sAdaptor[0]==='@' ? __NAMESPACE__ . '\\Adaptor_' . substr($sAdaptor, 1) : $sAdaptor;
        $Class = new \ReflectionClass($sAdaptor);

        $aParam = func_get_args();
        array_shift($aParam);
        $Object = $Class->newInstanceArgs($aParam);
        if (!$Object instanceof IAdaptor) {
            trigger_error('Configure is not instance of Slime.IAdapt', E_USER_ERROR);
            exit(1);
        }
        $this->Object = $Object;
    }

    /**
     * @param string $sKey
     * @param mixed $sDefaultValue
     * @param int $iErrorLevel
     * @return mixed
     */
    public function get($sKey, $sDefaultValue = null, $iErrorLevel = 0)
    {
        return $this->Object->get($sKey, $sDefaultValue, $iErrorLevel);
    }
}
