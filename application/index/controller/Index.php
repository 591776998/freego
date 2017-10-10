<?php
namespace app\index\controller;
use app\index\model as model;
trait play{
    public function go(){
        return 'ok go!';
    }
}

class Index extends \app\common\controller\Base
{
    use play;

    protected $name = 'lvlin';

    public function index(){

        return 'success!';
        //return ['code'=>'200','msg'=>'success','data'=>'this is data'];
    }

    //测试模型
    public function userAdd(){

        $user = new model\User;

        $user->nick_name = 'lvlin';
        $user->sex       = 101;
        $user->is_del    = 102;
        $user->status    = 101;
        $user->phone     = 18380335706;
        if($user->save()){
            dump($user->sex);
            dump($user);
        }
    }

    // 读取用户数据
    public function userRead($id=1)
    {

        $user = model\User::get(['id'=>$id]);

        $user->phone = 110;

        if($user->save()){

            dump($user);

        }

    }

    //读取文章
    public function artRead( $id = 1){

        $art = model\Art::get(['id'=>$id]);
        if(!$art){
            return error('没找到文章');
        }
        $res = $art;
        $res['comments'] = $art->comments;
        return success($res);
    }
}

