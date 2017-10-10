<?php
namespace app\branch\controller;
class Index extends \app\common\controller\BranchBase
{

    public function index()
    {
        $this->assign('data',$this->branch);
        return $this->fetch();
    }
}