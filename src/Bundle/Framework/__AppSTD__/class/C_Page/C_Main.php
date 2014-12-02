<?php
namespace AppSTD\C_Page;

use AppSTD\System\Framework\C_Page;
use Slime\Component\Http\Ext;

class C_Main extends C_Page
{
    public function actionDefault()
    {
        $this->aData['h1'] = 'Hell world!';

        /** @var \AppSTD\Model\M_User $M_U */
        $M_U = $this->CTX->ORM->M_User();
        $this->aData['G_User'] = $M_U->findMulti();
    }

    public function actionFetch()
    {
        Ext::ev_LogCost($this->CTX->Event, $this->CTX->Log);

        $HC = $this->CTX->HttpCall;
        $mRS = $HC->setUrl('http://www.baidu.com')->get()->asString();
        var_dump($mRS);

        $this->setAsNoneRender();
    }

    public function actionCreateRandom()
    {
        /** @var \AppSTD\Model\M_User $U */
        $U = $this->CTX->ORM->M_User();
        $bRS = $U->insert(
            array(
                'name' => base64_encode(microtime(true)),
                'password' => md5(mt_rand(1,10)),
                'create_time' => date('Y-m-d H:i:s')
            )
        );

        $this->RESP
            ->setHeader('Content-Type', 'text/javascript; charset=utf-8')
            ->setBody(json_encode($bRS));

        if (isset($this->Log->aWriter['WebPage'])) {
            $this->Log->aWriter['WebPage']->disable();
        }
        return false;
    }
}