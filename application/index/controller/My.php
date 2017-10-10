<?php
namespace app\index\controller;
use app\index\model as model;
use think\Request;
class My extends \app\common\controller\Base
{

    public function read(){
        if(!$this->user){
            return error(['desc'=> '请先登录,然后再访问！'],'请先登录,然后再访问！','401');
        }
        $user = model('user','service')->fullModel($this->user['id'],0);

        //最近10个访客
        $user_visit = model('UserVisit')->distinct(true)->field('user_id')->where('target_id',$this->user['id'])->where('type',101)->page(1,8)->order('c_time desc')->column('user_id');
        if($user_visit){
            $user['visit'] = model('User','service')->getUser($user , $user_visit, ['id'=>$user_visit]);
        }else{
            $user['visit'] = [];
        }

        //访问数加1

        return success($user);
    }

}
