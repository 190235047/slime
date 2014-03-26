<?php
namespace Slime\Component\Cache;

/**
 * Class Adaptor_File
 *
 * @package Slime\Component\Cache
 * @author  smallslime@gmail.com
 */
class Adaptor_File implements IAdaptor
{
    protected $sCacheDir;
    protected $mCBKey2File;

    /**
     * @param string $sCacheDir
     * @param mixed  $mCBKey2File (callback 回调, 参数为缓存key, 期待返回缓存文件名)
     * @param int    $iCreateMode
     *
     * @throws \RuntimeException
     */
    public function __construct($sCacheDir, $mCBKey2File = null, $iCreateMode = 0777)
    {
        $this->sCacheDir = rtrim($sCacheDir, '/') . '/';
        if (!file_exists($this->sCacheDir)) {
            if (!@mkdir($this->sCacheDir, $iCreateMode, true)) {
                throw new \RuntimeException("[CACHE] : Create dir[$sCacheDir] failed");
            }
        }
        $this->mCBKey2File = $mCBKey2File;
    }

    /**
     * @param string $sKey
     *
     * @return mixed
     */
    public function get($sKey)
    {
        $sFile = $this->getFileFromKey($sKey);

        if (!file_exists($sFile)) {
            return null;
        }

        $aData = file($sKey);
        if (count($aData) !== 2) {
            return null;
        }

        if (time() - $aData[0] > 0) {
            $this->delete($sKey);
            return null;
        }
        return json_decode($aData[1]);
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
        return file_put_contents(
            $this->getFileFromKey($sKey),
            sprintf("%s\n%s", time() + $iExpire, json_encode($mValue))
        ) !== false;
    }

    /**
     * @param $sKey
     *
     * @return bool
     */
    public function delete($sKey)
    {
        $sFile = $this->getFileFromKey($sKey);
        return file_exists($sFile) ? unlink($sFile) : true;
    }

    /**
     * @return bool
     */
    public function flush()
    {
        $rDir = opendir($this->sCacheDir);
        while (($sFile = readdir($rDir)) !== false) {
            if (ltrim($sFile, '.') !== '') {
                @unlink($this->sCacheDir . $sFile);
            }
        }
        closedir($rDir);
        return true;
    }

    private function getFileFromKey($sKey)
    {
        return $this->sCacheDir .
        (
        $this->mCBKey2File === null ?
            md5($sKey) . '.cache.php' :
            call_user_func($this->mCBKey2File, $sKey)
        );
    }
}