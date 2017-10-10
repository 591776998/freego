<?php
namespace app\index\controller;
use think\Request;
class AssociationUser extends \app\common\controller\Base
{
    //1.协会成员
    public function index(){
        if(!$this->check('association_id',['number','min:0'])){
            return error($this->checkError());
        }

        //1.类型
        $result = model('AssociationUser','service')->index($this->param('association_id',0));
        return success($result);
    }
}