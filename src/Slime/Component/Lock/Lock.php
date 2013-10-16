<?php
namespace Slime\Component\Lock;

use Psr\Log\LoggerInterface;

class Lock
{
    /** @var IAdaptor */
    public $Obj;

    public function __construct($sAdaptor, $aConfig, LoggerInterface $Logger)
    {
        if ($sAdaptor[0] === '@') {
            $sAdaptor = '\\Slime\\Component\\Lock\\Adaptor_' . substr($sAdaptor, 1);
        }
        $this->Obj = new $sAdaptor($aConfig, $Logger);
        if (!$this->Obj instanceof IAdaptor) {
            $Logger->error('{adaptor} must impl Slime.Component.Cache.IAdaptor', array('adaptor' => $sAdaptor));
            exit(1);
        }
    }

    /**
     * @param string $sKey
     * @param int    $iExpire   0:永不过期
     * @param int    $iTimeout  0:永不超时(一直阻塞); -1:异步(发现阻塞不等待立刻返回false)
     *
     * @return bool
     */
    public function acquire($sKey, $iExpire, $iTimeout = -1)
    {
        return $this->Obj->acquire($sKey, $iExpire, $iTimeout);
    }

    /**
     * @param string $sKey
     *
     * @return bool
     */
    public function release($sKey)
    {
        return $this->Obj->release($sKey);
    }
}