<?php
namespace app\index\controller;
use app\index\model as model;
use think\Request;
class Version extends \app\common\controller\Base
{
    //1检查版本号
    public function read(){
        $platform = $this->param('platform',0);
        if('android' == $platform){
            $platform = 1;
        }elseif ('ios' == $platform){
            $platform = 2;
        }
        //1.在发布时间内的， status=101
        $data = model('app_version');
        if($platform){
            $data->where(function ($query)use($platform){
                return $query->where('system_type',$platform)->whereOr('system_type',3);
            });
        }
        $data = $data->where('status',101)->where('p_time','<',floor(THINK_START_TIME))->order('id desc')->find();
        if($data){
            $data['download_url'] = img_show($data['download_url']);
            return success($data);
        }else{
            return error(['desc'=>'没有找到最新版本信息']);
        }
    }
}
