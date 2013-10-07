<?php
namespace SlimeFramework\Component\Log;

interface IWriter
{
    public function acceptData($aRow);
}