<?php
namespace app\index\controller;
use think\Request;
class Chanel extends \app\common\controller\Base
{
    //栏目

    //列表
    public function index(){
        //101首页
        if(!$this->check('chanel_type',['number','r','min:0'])||
            !$this->check('page',['number','min:1'])||
            !$this->check('page_size',['number','min:1']))
        {
            return error($this->checkError());
        }

        $result_chanel = model('Chanel','service')->index( $this->param('chanel_type'), $this->param('page',1), $this->param('page_size',10));
        if($this->isError($result_chanel)){
            return success([]);
        }else{
            return success($result_chanel);
        }
    }

}