<?php
namespace app\index\service;
class AssociationUser extends \app\common\service\Base
{

    //列表
    public function index( $a_id = 0){
        $result = model('AssociationUser')->with(['img'])->where('association_id',$a_id)->order('order_weight desc')->order('id desc')->select();
        return $this->success($result);
    }

}