<?php
namespace AppSTD\System\Support;

use Slime\Component\Support\Context;

/**
 * Class CTX
 *
 * @package AppSTD\System\Support
 *
 * @property-read string                           $__RUN_MODE__
 * @property-read string                           $__ENV__
 * @property-read \Slime\Component\Http\REQ        $REQ
 * @property-read \Slime\Component\Http\RESP       $RESP
 * @property-read \Slime\Component\Config\IAdaptor $Config
 * @property-read \Slime\Component\Log\Logger      $Log
 * @property-read \Slime\Component\Route\Router    $Router
 * @property-read \Slime\Component\Event\Event     $Event
 */
class CTX extends Context
{
}
