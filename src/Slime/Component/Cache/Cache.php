<?php
namespace Slime\Component\Cache;

use Psr\Log\LoggerInterface;

final class Cache
{
    /** @var IAdaptor */
    private $Obj;

    public function __construct($sAdaptor, array $aConfig, LoggerInterface $Logger)
    {
        if ($sAdaptor[0] === '@') {
            $sAdaptor = '\\Slime\\Component\\Cache\\Adaptor_' . substr($sAdaptor, 1);
        }
        $Obj = new $sAdaptor($aConfig, $Logger);
        if (!$Obj instanceof IAdaptor) {
            $Logger->error('{adaptor} must impl Slime.Component.Cache.IAdaptor', array('adaptor' => $sAdaptor));
            exit(1);
        }
        return $Obj;
    }

    /**
     * @param string $sKey
     *
     * @return mixed
     */
    public function get($sKey)
    {
        return $this->Obj->get($sKey);
    }

    /**
     * @param string $sKey
     * @param mixed  $mValue
     * @param int    $iExpire
     *
     * @return bool
     */
    public function set($sKey, $mValue, $iExpire)
    {
        return $this->Obj->set($sKey, $mValue, $iExpire);
    }

    /**
     * @param $sKey
     *
     * @return bool
     */
    public function delete($sKey)
    {
        return $this->Obj->delete($sKey);
    }

    /**
     * @return bool
     */
    public function flush()
    {
        return $this->Obj->flush();
    }

    /**
     * @return IAdaptor
     */
    public function getAdaptor()
    {
        return $this->Obj;
    }
}