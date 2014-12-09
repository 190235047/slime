<?php
namespace AppSTD\System\Support;

use Slime\Component\Support\Context;

/**
 * Class CTX_CLI
 *
 * @package AppSTD\System\Support
 *
 * @property-read array                                   $aArgv
 * @property-read \Slime\Component\Config\IAdaptor        $Config
 * @property-read \Slime\Component\Route\Router           $Router
 * @property-read \Slime\Component\Log\Logger             $Log
 * @property-read \AppSTD\System\ORM\Factory              $ORM
 * @property-read \Slime\Component\I18N\I18N              $I18N
 * @property-read \Slime\Component\Event\Event            $Event
 * @property-read \Slime\Component\Http\Call              $HttpCall
 */
class CTX_CLI extends Context
{
}
