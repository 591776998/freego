<?php
namespace app\index\controller;
use think\Request;
class Photo extends \app\common\controller\Base
{
    //图片方式浏览文章浏览
//    public function index(){
//        if(!$this->check('code_name',['string'])||
//            !$this->check('code_value',['string'])||
//            !$this->check('page',['number','min:0'])||
//            !$this->check('page_size',['number','min:0']))
//        {
//            return error($this->checkError());
//        }
//        return success(model('ArtPara','service')->paraList(102,$this->param('code_name',''),$this->param('code_value',''),$this->param('page',1),$this->param('page_size',10)));
//    }

    //获取精彩影像列表
    public function index(){
        $location_name  = $this->param('location_name','');
        $location_value = $this->param('location_value','');
        $p              = $this->param('page',1);
        $ps             = $this->param('page_size',10);
        $user_id        = $this->user?$this->user['id']:'-1';
        $data = model('Photo','service')->index($user_id,$location_name,$location_value,$p,$ps);
        return success($data['data']);
    }

    //上传精彩影像
    public function save(){
        $file = $this->request->file();
        if(!$this->user){
            return error(['desc'=>'请登录后再上传']);
        }
        if(!$this->check('location_name',['string','r'])||
            !$this->check('location_value',['string','r'])
        ){
            return error($this->checkError());
        }

        //1保存图片
        $save_data = $this->param;
        unset($save_data['token']);
        $save_data['source_id']     = $this->user['id'];
        $save_data['source_type']   = 99;
        $save_data['user_id']       = $this->user['id'];
        $save_data['location_str']  = model('address','service')->getFullName($save_data['location_name'],$save_data['location_value']);
        $save_data['status']        = 102;
        $save_data['c_time']        = floor(THINK_START_TIME);

        //获取上级全部location
        $location_arr = model('address','service')->getFather($save_data['location_name'],$save_data['location_value']);
        if('c_code' == $save_data['location_name']){
            $save_data['c_code'] = $save_data['location_value'];
            if(isset($location_arr['father']['p_code'])){
                $save_data['p_code'] = $location_arr['father']['p_code'];
                if(isset($location_arr['father']['father']['s_code'])){
                    $save_data['s_code'] = $location_arr['father']['father']['s_code'];
                }
            }
        }elseif ('city' == $save_data['location_name']){
            $save_data['city'] = $save_data['location_value'];
            if(isset($location_arr['father']['s_code'])){
                $save_data['s_code'] = $location_arr['father']['s_code'];
            }
        }

        if(isset($file['img']) && $file['img']){
            $img_result = model('image','service')->upload($file['img'],114);
            $save_data['img_id'] = $img_result['id'];
        }

        if(model('WonderfulImg')->data($save_data)->save()){
            return success([]);
        }else{
            return error(['desc'=>'保存失败']);
        }
    }
}