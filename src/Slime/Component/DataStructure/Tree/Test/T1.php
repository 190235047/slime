<?php
namespace Slime\Component\DataStructure\Tree;

/*
$Top = new Node(new Tree_Pool(new Logger([new Writer_STDFD()]), 'movie', array('cname' => '电影', 'class' => 'c-1', 'hidden' => false));

$C2 = $Top->bornChild('movie-happy', array('cname' => '喜剧', 'class' => 'c-2', 'hidden' => false));
$C2->bornChild('movie-happy-black', array('cname' => '黑色幽默', 'class' => 'c-3', 'hidden' => false));
$C2->bornChild('movie-happy-yellow', array('cname' => '黄色动漫', 'class' => 'c-3', 'hidden' => true));

$C3 = $Top->bornChild('movie-xuanyi', array('cname' => '悬疑', 'class' => 'c-2', 'hidden' => false));
$C31 = $C3->bornChild('movie-happy-blue', array('cname' => '诙谐喜剧', 'class' => 'c-3', 'hidden' => true));
$C311 = $C31->bornChild('movie-happy-blue-bigbang', array('cname' => '生活大爆炸', 'class' => 'c-3', 'hidden' => true));

$C311->changeParent($C2);

echo $Top->treeString();

$aA = $Top->Pool->findNodesByLevel(2);
foreach ($aA as $Node) {
    echo $Node . "\n";
}
*/

use Slime\Component\Log\Logger;
use Slime\Component\Log\Writer_STDFD;

$aConf = array(
    '/' => array(
        'name' => '首页',
        array(
            '/gray/' => array(
                'name' => '灰度规则',
                'icon' => 'icon-task',
                array(
                    '/gray/service/'    => array(
                        'name' => '服务管理',
                        'icon' => 'icon-reorder',
                        array(
                            '/gray/service/list' => array(
                                'name' => '服务列表'
                            ),
                            '/gray/service/add'  => array(
                                'name' => '添加服务'
                            ),
                            '/gray/service/edit' => array(
                                'name'   => '编辑服务',
                                'hidden' => true
                            )
                        )
                    ),
                    '/gray/cmd/'        => array(
                        'name' => '命令管理',
                        'icon' => 'icon-tasks',
                        array(
                            '/gray/cmd/list' => array(
                                'name' => '命令列表'
                            ),
                            '/gray/cmd/add'  => array(
                                'name' => '添加命令'
                            ),
                            '/gray/cmd/edit' => array(
                                'name'   => '编辑命令',
                                'hidden' => true
                            )
                        )
                    ),
                    '/gray/keyword/'    => array(
                        'name' => '关键字管理',
                        'icon' => 'icon-book',
                        array(
                            '/gray/keyword/list' => array(
                                'name' => '关键字列表'
                            ),
                            '/gray/keyword/add'  => array(
                                'name' => '添加关键字'
                            ),
                            '/gray/keyword/edit' => array(
                                'name'   => '编辑关键字',
                                'hidden' => true
                            )
                        )
                    ),
                    '/gray/rule/'       => array(
                        'name' => '规则管理',
                        'icon' => 'icon-random',
                        array(
                            '/gray/rule/list' => array(
                                'name' => '规则列表'
                            ),
                            '/gray/rule/add'  => array(
                                'name' => '添加规则'
                            ),
                            '/gray/rule/edit' => array(
                                'name'   => '编辑规则',
                                'hidden' => true
                            )
                        )
                    ),
                    '/gray/kv_group/'   => array(
                        'name' => '键值对管理',
                        'icon' => 'icon-edit',
                        array(
                            '/gray/kv_group/list' => array(
                                'name' => '键值对列表'
                            ),
                            '/gray/kv_group/add'  => array(
                                'name' => '添加键值对'
                            ),
                            '/gray/kv_group/edit' => array(
                                'name'   => '编辑键值对',
                                'hidden' => true
                            )
                        )
                    ),
                    '/gray/file_group/' => array(
                        'name' => '文件组管理',
                        'icon' => 'icon-inbox',
                        array(
                            '/gray/file_group/group_add' => array(
                                'name' => '添加文件组'
                            ),
                            '/gray/file_group/file_add'  => array(
                                'name'   => '添加文件',
                                'hidden' => true
                            )
                        )
                    ),
                    '/gray/kv_action/'  => array(
                        'name' => '动作管理',
                        'icon' => 'icon-bell',
                        array(
                            '/gray/action/list' => array(
                                'name' => '动作列表'
                            ),
                            '/gray/action/add'  => array(
                                'name' => '添加动作'
                            ),
                            '/gray/action/edit' => array(
                                'name'   => '编辑动作',
                                'hidden' => true
                            )
                        )
                    ),
                )
            )
        )
    ),
);

$RootNode = Pool::initFromArrayRecursion($aConf, new Logger([new Writer_STDFD()]));
echo $RootNode->treeString();