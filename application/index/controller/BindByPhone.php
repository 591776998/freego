<?php
namespace app\index\controller;
class Bindv2 extends \app\common\controller\Base
{
    //
    public function save(){
        if(
            !$this->check('login_type',['number','r','min:0'])||
            !$this->check('only_sign',['string','r',32])||
            !$this->check('phone',['number','r','phone'])||
            !$this->check('pwd',['string','r',32])||
            !$this->check('vcode',['string','r'])
        ){
            return error($this->checkError());
        }

        $pre = $this->param('pre','+86');
        $phone = $this->param('phone');
        $user_type_arr = [102=>'微信账号',103=>'微博账号'];
        if(!in_array($this->param('login_type'),array_keys($user_type_arr))){
            return error(['desc'=>'不支持该类型的账号！']);
        }

        $vcode = new \transfer\Vcode();
        //$phone,$pre,$type,$code
        $result_vcode = $vcode->contrast( $phone,$pre,103,$this->param('vcode'));
        if($this->isError($result_vcode)){
            return error($result_vcode);
        }

        if(model('UserLogin')->where('only_sign',$this->param('only_sign'))->where('login_type',$this->param('login_type'))->find()) {
            return error(['desc' => '当前'.$user_type_arr[$this->param('login_type')].'已经绑定账号！']);
        }

        //1.验证本地账号有没有绑定，没有绑定就继续，有一方绑定，就停止
        $res = model('UserLogin')->where('phone',$phone)->where('pre',$pre)->find();
        if($res){
            return error(['desc'=>'该账号已注册！']);
        }

        $user_data = [
            'nick_name'     => $this->param('nick_name'),
            'sex'           => $this->param('sex',103),
            'phone'         => $phone,
            'pre'           => $pre,
            'pwd'           => $this->param('pwd')
        ];
        if($this->has('head_img_url')){
            //保存图片，然后返回id
            $img_data = model('Image','service')->transfer($this->param('head_img_url'),105);
            if($img_data){
                $user_data['head_img_id'] = $img_data['id'];
            }
        }elseif ($this->has('head_img_id','86')){
            $user_data['head_img_id'] = $this->param('head_img_id','86');
        }

        $user = model('user','service')->addUser($user_data);
        if($this->isError($user)){
            return error($user);
        }

        $token = model('User','service')->createToken( mt_rand(1,99999999));
        $save_data = [
            'id'            => $user['data']['id'],
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

        //查询该用户
        $user = model('user','service')->fullModel($user['data']['id']);
        $user_model['token'] = $token;
        return success(['user'=>$user,'token'=>$token]);

    }



}