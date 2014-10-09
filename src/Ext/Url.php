<?php
namespace Slime\Ext;

/**
 * Class Url
 *
 * @package Slime\Ext
 * @author  smallslime@gmaile.com
 */
class Url
{
    protected $aBlock;
    protected $iEncType;

    public function __construct($sUrl, $iEncType = PHP_QUERY_RFC1738)
    {
        $this->aBlock   = self::parse($sUrl, true, true);
        $this->iEncType = $iEncType;
    }

    /**
     * @param string $sK
     * @param mixed  $mV
     *
     * @return $this
     */
    public function update($sK, $mV)
    {
        $this->aBlock[$sK] = $mV;

        return $this;
    }

    /**
     * @param array $aArr
     *
     * @return $this
     */
    public function updateQuery(array $aArr)
    {
        $this->aBlock['query'] = empty($this->aBlock['query']) ? $aArr : array_merge($this->aBlock['query'], $aArr);

        return $this;
    }

    /**
     * @param array         $aQueryBlock
     * @param null | string $nsFragment
     * @param int           $iEncType
     *
     * @return string
     */
    public function generateNewUrl(array $aQueryBlock, $nsFragment = null, $iEncType = PHP_QUERY_RFC1738)
    {
        $aBlock          = $this->aBlock;
        $aBlock['query'] = empty($aBlock['query']) ? $aQueryBlock : array_merge($aBlock['query'], $aQueryBlock);
        if ($nsFragment !== null) {
            $aBlock['fragment'] = (string)$nsFragment;
        }

        return self::build($aBlock, $iEncType);
    }

    public function doClone()
    {
        return clone $this;
    }

    public function toString()
    {
        return (string)$this;
    }

    public function __toString()
    {
        return self::build($this->aBlock, $this->iEncType);
    }

    public static function parse($sUrl, $bParsePath = true, $bParseQuery = true)
    {
        $aBlock = parse_url($sUrl);
        if ($bParsePath && isset($aBlock['path'])) {
            $aBlock['path'] = explode('/', substr($aBlock['path'], 1));
        }
        if ($bParseQuery) {
            parse_str($aBlock['query'], $aBlock['query']);
        }

        return $aBlock;
    }

    /**
     * @param array $aBlock
     * @param int   $iBuildQueryEncTypeIfQueryIsArr
     *
     * @return string
     */
    public static function build($aBlock, $iBuildQueryEncTypeIfQueryIsArr = PHP_QUERY_RFC1738)
    {
        $sScheme   = isset($aBlock['scheme']) ? $aBlock['scheme'] . '://' : '';
        $sHost     = isset($aBlock['host']) ? $aBlock['host'] : '';
        $sPort     = isset($aBlock['port']) ? ':' . $aBlock['port'] : '';
        $sUser     = isset($aBlock['user']) ? $aBlock['user'] : '';
        $sPass     = isset($aBlock['pass']) ? ":{$aBlock['pass']}" : '';
        $sPass     = ($sUser === '' && $sPass === '') ? '' : "$sPass@";
        $sPath     = isset($aBlock['path']) ?
            (is_array($aBlock['path']) ? '/' . implode('/', $aBlock['path']) : $aBlock['path']) : '';
        $sQuery    = isset($aBlock['query']) ?
            '?' . (
            is_array($aBlock['query']) ?
                http_build_query($aBlock['query'], null, null, $iBuildQueryEncTypeIfQueryIsArr) : $aBlock['query']
            ) : '';
        $sFragment = isset($aBlock['fragment']) ? '#' . $aBlock['fragment'] : '';

        return "{$sScheme}{$sUser}{$sPass}{$sHost}{$sPort}{$sPath}{$sQuery}{$sFragment}";
    }
}
