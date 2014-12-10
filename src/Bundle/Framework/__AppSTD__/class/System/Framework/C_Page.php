<?php
namespace AppSTD\System\Framework;

use Slime\Bundle\Framework\Controller_Page;

class C_Page extends Controller_Page
{
    /** @var \AppSTD\System\Support\CTX_Page */
    protected $CTX;

    protected $bLoadI18N = true;

    public function __after__()
    {
        if ($this->bLoadI18N && $this->isPageRender()) {
            $this->aData['I18N'] = $this->CTX->I18N;
        }
    }
}
