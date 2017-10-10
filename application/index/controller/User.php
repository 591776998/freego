<?php
namespace app\index\controller;
use app\index\model as model;
use think\Request;
class User extends \app\common\controller\Base{

    //用户
    //一个restful风格的控制器

    //get list
    public function index(){
        if(!$this->user){
            return error(['desc'=>'请先登录，然后继续操作']);
        }
        if(!$this->check('type',['number','r','min:0'])){
            return error(['desc'=>'错误的类型']);
        }

        if(101 == $this->param('type','0')){
            if(!$this->check('lat',['number','r'])){
                return error(['desc'=>'请上传位置信息']);
            }
            if(!$this->check('lng',['number','r'])){
                return error(['desc'=>'请上传位置信息']);
            }
            if(!$this->check('sex',['number','min:0'])){
                return error($this->checkError());
            }
            if(!$this->check('range',['number','min:0'])){
                return error($this->checkError());
            }
        }

        $page = $this->param('page',1);
        $page_size = $this->param('page_size',10);

        $user_service = model('User','service');
        switch ($this->param('type').''){
            case '101':
                //限制最多返回两百
                if(200 < ($page * ($page_size-1))){
                    $result = [];
                }else{
                    $result = $user_service->nearby($this->user, $this->param('sex',0),$this->param('lat'),$this->param('lng'),$this->param('range',2000000),$page,$page_size);
                }
                break;
            case '102':
            case '103':
            case '104':
            case '105':
            case '106':
            case '107':
            case '108':
                $result = $user_service->index($this->user,$this->param('type','104'), $this->param('key_word',''), $page,$page_size,$this->param('art_id',0));
                break;
            default:
                return success([]);
        }
        return success($result);
    }

    //get 需要id
    public function read(){
        if(!$this->check('id',['number','r','min:0'])){
            return error(['desc'=>'错误的用户Id']);
        }

        //如果
        $now_user_id = isset($this->user['id'])?$this->user['id']:'0';
        $id = $this->param('id',0);
        if($id && $now_user_id && ($now_user_id != $id)){
            //1.查询是否访问过
            $visit = model('userVisit')->where('user_id',$now_user_id)->where('target_id',$id)->find();
            if($visit){
                //修改
                $visit->c_time = floor(THINK_START_TIME);
                $visit->save();
            }else{
                //新增
                model('userVisit')->data([
                    'user_id'       => $now_user_id,
                    'target_id'     => $id,
                    'type'          => 101,
                    'c_time'        => floor(THINK_START_TIME)
                ])->save();
            }
            model('User')->where('id',$id)->setInc('look_count');
        }
        return success(model('User','service')->fullModel($id, $now_user_id));
    }

    //post 注册用户
    public function save(){
        $file = $this->request->file();

        //1.数据验证
        if(!$this->check('login_type',['number','r','min:0'])||
            !$this->check('phone',['number','r','phone'])||
            !$this->check('pwd',['string','r',32])||
            !$this->check('vcode',['string','r']))
        {
            return error($this->checkError());
        }

        //验证验证码是否正确

        $pre   = $this->param('pre','+86');
        $phone = $this->param('phone');

        $vcode = new \transfer\Vcode();
        //$phone,$pre,$type,$code
        $result_vcode = $vcode->contrast( $phone,$pre,101,$this->param('vcode'));

//        $result_vcode = model('Vcode','service')->contrastVcode( $this->param('phone'),$this->param('vcode'),101);
        if($this->isError($result_vcode)){
            return error($result_vcode);
        }

        $user_service = model('User','service');
        if($user_service->isRegister($phone,$pre)){
            return error(['desc'=>'该手机号已经注册']);
        }

        //判断如果有图片的话，就上传图片
        if($file){
            //上传头像，并修改
            $img_result = model('image','service')->upload($file['head_img'],105);
            $save_data['head_img_id'] = $img_result['id'];
        }

        $result = $user_service->addUser($this->param);
        if($this->isError($result)){
            return error($result);
        }

        $userModel = $user_service->fullModel($result['data']['id']);
        if(is_null($userModel)){
            $userModel = (object)null;
        }
        return success($userModel);
    }

    //put 需要id
    public function update(){
        $file = $this->request->file();
        //1.数据验证
        if(
            !$this->check('id',        ['number','r','min:0'])||
            !$this->check('nick_name', ['string'])||
            !$this->check('sex',       ['number'])||
            !$this->check('birth_date',['string'])||
            !$this->check('s_code',    ['string'])||
            !$this->check('p_code',    ['string'])||
            !$this->check('c_code',    ['string'])||
            !$this->check('head_img_id',['number','min:0'])
        ){
            return error($this->checkError());
        }

        if(!$this->user){
            return error(['desc'=>'请登录再修改个人资料'],'请登录再修改个人资料','401');
        }

        $save_data  = [];
        if($this->has('nick_name')){
            if( 50 < mb_strlen($this->param('nick_name'))){
                return error(['desc'=>'昵称不能大于8个字符']);
            }
            $save_data['nick_name'] = $this->param('nick_name');
        }
        if($this->has('birth_date')){
            if(THINK_START_TIME < strtotime($this->param('birth_date'))){
                return error(['desc'=>'出生日期不能晚于当前时间']);
            }
            $save_data['birth_date'] = $this->param('birth_date');
        }
        if($this->has('sex') && $this->param['sex'])$save_data['sex'] = $this->param['sex'];
        if($this->has('live_location_name') && $this->param['live_location_name'])$save_data['live_location_name'] = $this->param['live_location_name'];
        if($this->has('live_location_value') && $this->param['live_location_value'])$save_data['live_location_value'] = $this->param['live_location_value'];
        if($this->has('live_location_str') && $this->param['live_location_str'])$save_data['live_location_str'] = $this->param['live_location_str'];
        if($this->has('sign') && $this->param['sign'])$save_data['sign'] = $this->param['sign'];
        if($this->has('club_id') && $this->param['club_id'])$save_data['club_id'] = $this->param['club_id'];

        if(isset($file['head_img'])){
            if(is_array($file['head_img'])){
                $file['head_img'] = $file['head_img'][0];
            }
            $img_result = model('image','service')->upload($file['head_img'],105);
            $save_data['head_img_id'] = $img_result['id'];
        }elseif ($this->has('head_img_id')){
            $save_data['head_img_id'] = $this->param('head_img_id','0');
        }
        if(isset($file['cover_img'])){
            if(is_array($file['cover_img'])){
                $file['cover_img'] = $file['cover_img'][0];
            }
            $img_result2 = model('image','service')->upload($file['cover_img'],105);
            $save_data['cover_img_id'] = $img_result2['id'];
        }elseif ($this->has('cover_img_id')){
            $save_data['cover_img_id'] = $this->param('cover_img_id','0');
        }

        $result = model('User','service')->update($this->param('id'),$save_data);
        if($this->isError($result)){
            return error($result);
        }
        $user_model = model('User','service')->fullModel($this->param('id','0'),$this->user['id']);
        if($user_model){
            $user_model['token'] = $this->param('token','');
        }
        return success($user_model);
    }

    //delete 需要id
    public function delete(){

        return 'delete';
    }

    /************************************* 私有 ***********************************************/

}