<?php
/**
 * Generated stub file for code completion purposes
 */

define('AOP_KIND_BEFORE', 2);
define('AOP_KIND_AFTER', 4);
define('AOP_KIND_AROUND', 1);
define('AOP_KIND_PROPERTY', 32);
define('AOP_KIND_FUNCTION', 128);
define('AOP_KIND_METHOD', 64);
define('AOP_KIND_READ', 8);
define('AOP_KIND_WRITE', 16);
define('AOP_KIND_AROUND_WRITE_PROPERTY', 49);
define('AOP_KIND_AROUND_READ_PROPERTY', 41);
define('AOP_KIND_BEFORE_WRITE_PROPERTY', 50);
define('AOP_KIND_BEFORE_READ_PROPERTY', 42);
define('AOP_KIND_AFTER_WRITE_PROPERTY', 52);
define('AOP_KIND_AFTER_READ_PROPERTY', 44);
define('AOP_KIND_BEFORE_METHOD', 66);
define('AOP_KIND_AFTER_METHOD', 68);
define('AOP_KIND_AROUND_METHOD', 65);
define('AOP_KIND_BEFORE_FUNCTION', 130);
define('AOP_KIND_AFTER_FUNCTION', 132);
define('AOP_KIND_AROUND_FUNCTION', 129);

function aop_add_before($pointcut, $advice)
{
}

function aop_add_after($pointcut, $advice)
{
}

function aop_add_around($pointcut, $advice)
{
}

class AopJoinPoint
{
    /**
     * @return array
     */
    public function &getArguments()
    {
    }

    /**
     * @return string
     */
    public function getPropertyName()
    {
    }

    /**
     * @return mixed
     */
    public function getPropertyValue()
    {
    }

    /**
     * @param array $arguments
     *
     * @return void
     */
    public function setArguments(array $arguments)
    {
    }

    /**
     * @return mixed
     */
    public function getKindOfAdvice()
    {
    }

    /**
     * @return mixed
     */
    public function &getReturnedValue()
    {
    }

    /**
     * @return mixed
     */
    public function &getAssignedValue()
    {
    }

    /**
     * @param $value
     *
     * @return void
     */
    public function setReturnedValue($value)
    {
    }

    /**
     * @param $value
     *
     * @return void
     */
    public function setAssignedValue($value)
    {
    }

    /**
     * @return mixed
     */
    public function getPointcut()
    {
    }

    /**
     * @return Object
     */
    public function getObject()
    {
    }

    /**
     * @return string
     */
    public function getClassName()
    {
    }

    /**
     * @return string
     */
    public function getMethodName()
    {
    }

    /**
     * @return string
     */
    public function getFunctionName()
    {
    }

    /**
     * @return \Exception
     */
    public function getException()
    {
    }

    /**
     * @return void
     */
    public function process()
    {
    }
}