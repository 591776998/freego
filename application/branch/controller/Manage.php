<?php
namespace app\branch\controller;
class Manage extends \app\common\controller\BranchBase
{

    public function main()
    {
        if(!$this->branch){
            return $this->errorLogin();
        }

        $all_user = model('branchLogin')->where('branch_id',$this->branch['branch_id'])->select();

        $this->assign('u',$all_user);
        $this->assign('data',$this->branch);
        return $this->fetch();
    }
}