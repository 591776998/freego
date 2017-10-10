<?php
namespace app\index\controller;
use think\Request;
class Bind extends \app\common\controller\Base
{
    //
    public function save(){
        if(
            !$this->check('login_type',['number','r','min:0'])||
            !$this->check('only_sign',['string','r',32])||
            !$this->check('phone',['number','r','phone'])||
            !$this->check('pwd',['string','r',32])
        ){
            return error($this->checkError());
        }
        $pre = $this->param('pre','+86');

        $user_type_arr = [102=>'微信账号',103=>'微博账号'];

        if(!in_array($this->param('login_type'),array_keys($user_type_arr))){
            return error(['desc'=>'不支持该类型的账号！']);
        }

        if(model('UserLogin')->where('only_sign',$this->param('only_sign'))->where('login_type',$this->param('login_type'))->find()) {
            return error(['desc' => '当前'.$user_type_arr[$this->param('login_type')].'已经绑定账号！']);
        }

        //1.验证本地账号有没有绑定，没有绑定就继续，有一方绑定，就停止
        $res = model('UserLogin')->where('phone',$this->param('phone'))->where('pre',$pre)->find();
        if($res){
            $user_id = $res['id'];
            if($res['pwd'] != $this->param('pwd')){
                return error(['desc' => '密码错误！']);
            }
            if(model('UserLogin')->where('id',$user_id)->where('login_type',$this->param('login_type'))->find()){
                return error(['desc' => '该账号已经绑定了其他账号！']);
            }

            $token = model('User','service')->createToken( mt_rand(1,99999999));
            $save_data = [
                'id'            => $user_id,
                'login_type'    => $this->param('login_type'),
                'only_sign'     => $this->param('only_sign'),
                'token'         => $token
            ];

            if(isset($save_data)&&$save_data){
                //添加一种登录方式
                if(!model('UserLogin')->save($save_data)){
                    return error(['desc'=>'绑定失败！']);
                }
            }
        }else{
            return error(['desc'=>'该账号未注册！']);
        }

        //
        $update_data = [];
        if($this->has('head_img_url')){
            //保存图片，然后返回id
            $img_data = model('Image','service')->transfer($this->param('head_img_url'),105);
            if($img_data){
                $update_data['head_img_id'] = $img_data['id'];
            }
        }
        if($this->has('sex'))$update_data['sex'] = $this->param('sex');
        if($this->has('nick_name'))$update_data['nick_name'] = $this->param('nick_name');

        if($update_data){
            model('User','service')->update($user_id,$update_data);
        }

        $user_model = model('User','service')->fullModel($user_id);
        if(!$user_model){
            return error(['desc'=>'没找到该用户']);
        }
        //查询该用户
        $user_model['token'] = $token;
        return success(['user'=>$user_model,'token'=>$token]);

    }



}