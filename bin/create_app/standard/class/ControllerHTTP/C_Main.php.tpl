<?php
namespace {{{NS}}}\ControllerHTTP;

use Slime\Bundle\Framework\Controller_Page;

class C_Main extends Controller_Page
{
    public function actionDefault()
    {
        $this->aData['h1'] = 'Hell world!';

        /** @var \{{{NS}}}\Model\M_User $M_U */
        $M_U = $this->CTX->ORM->M_User();
        $this->aData['G_User'] = $M_U->findMulti();
    }

    public function actionCreateRandom()
    {
        /** @var \{{{NS}}}\Model\M_User $U */
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