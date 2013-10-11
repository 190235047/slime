<?php
namespace Slime\Component\Log;

interface IWriter
{
    public function acceptData($aRow);
}