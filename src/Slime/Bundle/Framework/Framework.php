<?php
namespace Slime\Bundle\Framework;

class Framework
{
    public function __construct(array $aLogConfig, Context $Context = null)
    {
        if ($Context===null) {
        }

        Context::makeInst();
        /** @var Context $Context */
        $Context = Context::getInst();
        $Context->registerObjWithArgs('Log', 'Slime\\Component\\Log\\Logger', $aLogConfig);
        $Context->registerObjWithArgs('Config', 'Slime\\Component\\Config\\Adaptor_PHP');

        $this->loadModulesAutomatic($Context);
        $Context->registerAutomatic();
    }

    public static function loadModulesAutomatic(Context $Context)
    {
        $aConfig = $Context->Config->get('modules');
        if (empty($aConfig) || !is_array($aConfig)) {
            return;
        }
        foreach ($aConfig as $sClassName => $aModuleConfig) {
            $Context->registerAutomatic();
        }
    }
}
