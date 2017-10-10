<?php
namespace app\index\controller;
class Orderconfirm extends \app\common\controller\Base
{
    //
    public function read(){
        if(!$this->user){
            return error(['desc'=> '请先登录！'],'请先登录！','401');
        }
        $pay_info = model('order','service')->payInfo($this->param('id',0));
        if($this->isError($pay_info)) {
            return error($pay_info);
        }else{
            return success($pay_info['data']);
        }
    }

}