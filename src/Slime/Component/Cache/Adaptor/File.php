<?php
namespace Slime\Component\Cache;

class Adaptor_File implements IAdaptor
{
    protected $aData;

    protected $sCacheDir;
    protected $mCBKey2File;

    public function __construct($sCacheDir, $mCBKey2File = null, $iCreateMode = 0777)
    {
        $this->sCacheDir = rtrim($sCacheDir, '/') . '/';
        if (!file_exists($this->sCacheDir)) {
            if (!@mkdir($this->sCacheDir, $iCreateMode, true)) {
                throw new \Exception("Create dir[$sCacheDir] failed");
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

        if (!isset($this->aData[$sFile])) {
            $mData = require $sFile;
            $this->aData[$sFile] = is_array($mData) ? $mData : array();
        }
        if (!isset($this->aData[$sFile][$sKey])) {
            return null;
        }

        if (time() - $this->aData[$sFile][$sKey]['expire'] > 0) {
            $this->delete($sKey);
            return null;
        }
        return json_decode($this->aData[$sFile][$sKey]['data']);
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
        $mValue = json_encode($mValue);

        $sFile = $this->getFileFromKey($sKey);

        if (!isset($this->aData[$sFile])) {
            $mData = require $sFile;
            $this->aData[$sFile] = is_array($mData) ? $mData : array();
        }
        $this->aData[$sFile][$sKey] = array(
            'expire' => time() + $iExpire,
            'data'   => $mValue
        );

        return file_put_contents($sFile, '<?php return ' . var_export($this->aData[$sFile], true) . ';?>') !== false;
    }

    /**
     * @param $sKey
     *
     * @return bool
     */
    public function delete($sKey)
    {
        $sFile = $this->getFileFromKey($sKey);

        if (!isset($this->aData[$sFile])) {
            $mData = require $sFile;
            $this->aData[$sFile] = is_array($mData) ? $mData : array();
        }

        unset($this->aData[$sFile][$sKey]);
        $sStr = empty($this->aData[$sFile]) ? 'array()' : var_export($this->aData[$sFile], true);
        return file_put_contents($sFile, '<?php return ' . $sStr . ';?>') !== false;
    }

    /**
     * @return bool
     */
    public function flush()
    {
        $rDir = opendir($this->sCacheDir);
        while (($sFile = readdir($rDir)) !== false) {
            if (ltrim($sFile, '.')!=='') {
                @unlink($this->sCacheDir . $sFile);
            }
        }
        closedir($rDir);
        return true;

    }

    private function getFileFromKey($sKey)
    {
        $sCacheFile = $this->sCacheDir . (
            $this->mCBKey2File===null ?  'cache.php' : call_user_func($this->mCBKey2File, $sKey)
        );

        if (!file_exists($sCacheFile)) {
            if (!@touch($sCacheFile)) {
              throw new \Exception("Create file[$sCacheFile] failed");
            }
        }

        return $sCacheFile;
    }
}