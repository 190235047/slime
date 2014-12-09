<?php
namespace AppSTD\C_Page;

use AppSTD\System\Framework\C_Page;
use Slime\Component\RDBMS\DBAL\Bind;
use Slime\Component\RDBMS\DBAL\Condition;

class C_Main extends C_Page
{
    public function actionDefault()
    {
        $this->aData['h1']  = 'Hello world!';
        $this->aData['say'] = 'hi';
    }

    public function actionUser()
    {
        $this->aData['h1'] = 'UserList';

        $B = new Bind($this->REQ->getG(array('id_min')));

        $Where = Condition::buildAnd();
        if ($B->has('id_min')) {
            $Where->add('id', '>=', $B['id_min']);
        }

        $M_U                   = $this->CTX->ORM->M_User();
        $this->aData['G_User'] = $M_U->findMulti($Where);
    }

    public function actionUserPage()
    {
        $this->aData['h1'] = 'UserList';
        $M_U               = $this->CTX->ORM->M_User();
        $P                 = $this->CTX->Pagination;
        list($this->aData['sPage'], $this->aData['G_User'],) =
            $P->generate(
                $M_U,
                $M_U->SQL_SEL()->orderBy('-create_time')
            );

        $this->sTPL = 'Main-User.php';
    }

    public function actionCreateRandom()
    {
        $M_U = $this->CTX->ORM->M_User();
        $bRS = $M_U->insert(
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
            $this->Log->aWriter['WebPage']->bDisabled = true;
        }
        return false;
    }
}