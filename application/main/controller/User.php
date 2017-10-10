<?php
namespace app\main\controller;
class User extends \app\common\controller\MainBase
{

    public function all(){
        return $this->fetch();
    }

    public function index(){
        $user_service = new \app\index\service\User();
        $data = $user_service->index($this->user, 104,'',$this->param('page'),$this->param('page_size'));
        return success($data);
    }
}