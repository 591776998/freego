<?php
namespace app\index\controller;
class ActivityUser extends \app\common\controller\Base
{
    //参加获得的用户
    public function index(){
        if(!$this->check('id',['number','r','min:0']))
        {
            return error($this->checkError());
        }
        $id = $this->param('id',0);


        $data = model('artJoin')->field('user_id')->where('main_id',$id)->where('status',101)->select(false);




    }


}