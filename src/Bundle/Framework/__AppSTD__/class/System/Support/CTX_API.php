<?php
namespace AppSTD\System\Support;

use Slime\Component\Support\Context;

/**
 * Class CTX_API
 *
 * @package AppSTD\System\Support
 *
 * @property-read \Slime\Component\Http\REQ               $REQ
 * @property-read \Slime\Component\Http\RESP              $RESP
 * @property-read \Slime\Component\Config\IAdaptor        $Config
 * @property-read \Slime\Component\Route\Router           $Router
 * @property-read \Slime\Component\Log\Logger             $Log
 * @property-read \Slime\Component\View\IAdaptor          $View
 * @property-read \AppSTD\System\ORM\Factory              $ORM
 * @property-read \Slime\Component\I18N\I18N              $I18N
 * @property-read \Slime\Component\Event\Event            $Event
 */
class CTX_API extends Context
{
}
