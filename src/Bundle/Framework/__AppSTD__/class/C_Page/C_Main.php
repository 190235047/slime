<?php
namespace AppSTD\C_Page;

use AppSTD\System\Framework\C_Page;
use Slime\Component\RDBMS\DBAL\Bind;
use Slime\Component\RDBMS\DBAL\Condition;

class C_Main extends C_Page
{
    public function actionDefault()
    {
        $this->aData['h1'] = 'Hello world!';

        $B    = new Bind();
        $aGET = $this->REQ->getG(array('id_min'));
        $B->setMulti($aGET);

        $aWhere = array();
        if (isset($aGET['id_min'])) {
            $aWhere[] = array('id', '>=', $B['id_min']);
        }

        /** @var \AppSTD\Model\Model_User $M_U */
        $M_U                   = $this->CTX->ORM->M_User();
        $this->aData['G_User'] = $M_U->findMulti(
            Condition::buildAnd()->setMulti($aWhere)
        );
    }

    public function actionCreateRandom()
    {
        /** @var \AppSTD\Model\Model_User $U */
        $U   = $this->CTX->ORM->M_User();
        $bRS = $U->insert(
            array(
                'name'        => base64_encode(microtime(true)),
                'password'    => md5(mt_rand(1, 10)),
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