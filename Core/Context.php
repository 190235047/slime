<?php
namespace SlimeFramework\Core;

/**
 * Class Context
 * @package SlimeFramework\Component\Framework
 *
 * @property-read string sENV
 * @property-read string $sRunMode
 *
 * @property-read \DateTime $DateTime
 * @property-read Bootstrap $Bootstrap
 * @property-read \SlimeFramework\Component\Config\Configure $Config
 * @property-read \SlimeFramework\Component\Log\Logger $Log
 * @property-read \SlimeFramework\Component\Route\Router $Route
 * @property-read \SlimeFramework\Component\HTTP\IRequest $HttpRequest
 * @property-read \SlimeFramework\Component\HTTP\Response $HttpResponse
 *
 * @property-read array $aServer
 *
 */
class Context
{
    protected $aObject = array();

    /**
     * @return $this
     */
    public static function getInst()
    {
        return $GLOBALS['__sf_context__'][$GLOBALS['__sf_guid__']];
    }

    public static function makeInst($sGUID)
    {
        $GLOBALS['__sf_guid__'] = $sGUID;
        $GLOBALS['__sf_context__'][$sGUID] = new self();
    }

    public function register($sVarName, $Object, $bOverWrite = true, $bAllowExist = true)
    {
        if (isset($this->aObject[$sVarName])) {
            if ($bOverWrite) {
                $this->aObject[$sVarName] = $Object;
            } else {
                if (!$bAllowExist) {
                    $this->Log->error(
                        'Object register failed. {key} has exist{object}',
                        array('key' => $sVarName, 'object' => $Object)
                    );
                    exit(1);
                }
            }
        } else {
            $this->aObject[$sVarName] = $Object;
        }
    }

    public function __get($sVarName)
    {
        if (!isset($this->aObject[$sVarName])) {
            $this->Log->error(
                'Object fetch failed. {key} has not exist',
                array('key' => $sVarName)
            );
            exit(1);
        }
        return $this->aObject[$sVarName];
    }
}