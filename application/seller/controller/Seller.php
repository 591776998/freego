<?php
namespace app\seller\controller;
class Seller extends \app\common\controller\SellerBase
{

    public function index(){
        return 'seller';
    }

    public function save(){
        if(!$this->check('pre_phone',['string','r'])||
            !$this->check('phone',['number','r','phone'])||
            !$this->check('pwd',['string','r',32])||
            !$this->check('vcode',['string','r']))
        {
            return error($this->checkError());
        }
        $data = $this->param();
        //验证验证码是否正确
        $result_vcode = model('Vcode','service')->contrastVcode( $this->param('phone'),$this->param('vcode'),112);
        if($this->isError($result_vcode)){
            return error($result_vcode);
        }

        $login = model('sellerLogin')->where('phone',$this->param('phone',''))->find();
        if($login){
            return error(['desc'=>'该手机号已经注册']);
        }

        $res = model('seller')->data($data)->allowField(true);
        if($res->save()){
            $data['seller_id'] = $res['id'];
            if(model('SellerLogin')->data($data)->allowField(true)->save()){
                $user = model('seller')->with(['info'])->where('id',$res->id)->find();

                //通过，查询用户信息
                if(!$user){
                    return error(['desc'=>'未找到该用户']);
                }
                $user['login_type'] = $login['type'];
                //保存session
                session(config('admin_seller_key'),$user);

                return success($user);
            }else{
                $res->destroy($res['id']);
                return error(['desc'=>'创建账号失败']);
            }
        }else{
            return error(['desc'=>'创建账号失败']);
        }
    }

    //注册页面
    public function register(){

        return $this->fetch();
    }

    //资质提交页面
    public function info(){
        return $this->fetch();
    }

    //资质提交结果页面
    public function infores(){
        return $this->fetch();
    }

    //账号页面
    public function account(){
        if(!$this->seller){
            return $this->errorPage('请登录再继续操作');
        }

        $data = model('sellerLogin')->where('seller_id',$this->seller['id'])->select();

        $a = [];
        $b = [];
        foreach ($data as $k => $v){
            if(101 == $v['type'])$a[] = $v;
            if(102 == $v['type'])$b[] = $v;
        }

        $this->assign('a',$a);
        $this->assign('b',$b);
        return $this->fetch();
    }

    //创建一个商家的响应者
    public function create(){
        if(!$this->seller){
            return $this->errorLogin();
        }

        if(!$this->check('pwd',['string','r',32])){
            return error(['desc'=>'请输入密码']);
        }

        if(101 != $this->seller['login_type'])
        {
            return error(['desc'=>'您不能新建响应者账号']);
        }
        if(!$this->check('phone',['string','r','phone']))
        {
            return error(['desc'=>'请输入正确的手机号']);
        }

        if(model('sellerLogin')->where('phone',$this->param('phone',''))->find()){
            return error(['desc'=>'该手机号已注册']);
        }

        if(model('sellerLogin')->where('seller_id', $this->seller['id'])->where('type',102)->find()){
            return error(['desc'=>'只能设置一个响应者']);
        }

        $save_data = [
            'phone'     => $this->param('phone',''),
            'pwd'       => $this->param('pwd',''),
            'seller_id' => $this->seller['id'],
            'type'      => '102',
        ];

        $data = model('sellerLogin')->data($save_data);
        if($data->save()){
            return success($data);
        }else{
            return error(['desc'=>'创建失败']);
        }
    }

    //修改密码
    public function updatepwd(){
        if(!$this->seller){
            return $this->errorPage('请登录再继续操作');
        }

        $id = $this->param('id',0);

        $data = model('sellerLogin')->where('id',$id)->find();
        if(!$data){
            return error(['desc'=>'没找到该账号']);
        }

        if(101 != $this->seller['login_type'])
        {
            if(101 == $data['type']){
                return error(['desc'=>'您不能修改该账号']);
            }
        }

        $save_data = [];
        if($this->has('pwd')){
            if(!$this->param('pwd')){
                return error(['desc'=>'请输入密码']);
            }
            $save_data['pwd'] = $this->param('pwd','');
        }
        if($this->has('phone')){
            $save_data['phone'] = $this->param('phone','');
        }
        if(!$save_data){
            return error(['desc'=>'请输入账号或密码']);
        }

        if($data->where('id',$id)->update($save_data)){
            return success($data);
        }else{
            return error(['desc'=>'没有做任何修改']);
        }
    }
}