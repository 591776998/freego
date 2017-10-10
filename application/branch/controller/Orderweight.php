<?php
namespace app\branch\controller;
class Orderweight extends \app\common\controller\BranchBase
{

    public function index()
    {


    }

    //推送弹窗页面
    public function forumselect(){
        return $this->fetch();
    }

    //获取二级的论坛列表
    public function forumlist(){
        //传入location信息，判断所属论坛，获取下级论坛列表
        $location_name  = $this->param('location_name','');
        $location_value = $this->param('location_value','');
        $data = $this->getSub($location_name,$location_value);
        return success($data);
    }

    //传入location信息返回下级分会
    protected function getSub($location_name,$location_value){
        $data = [];
        if('all' == $location_name){
            //全球 查询出下级所有分会
            $data = model('forum')->where(function ($query){
                return $query->where('location_name','s_code')->whereOr('location_name','p_code');
            })->select();
        }elseif ('s_code' == $location_name && 1 == $location_value){
            //中国论坛
            $data = model('forum')->where('location_name','p_code')->select();
        }elseif('s_code' == $location_name || 'p_code' == $location_name){
            //国外论坛 或者 省论坛
            $data = model('forum')->where(function ($query)use($location_value){
                return $query->where(function ($query)use($location_value){
                    return $query->where('location_name','city')->where('s_code',$location_value);
                })->whereOr(function ($query)use($location_value){
                    return $query->where('location_name','c_code')->where('p_code',$location_value);
                });
            })->select();
        }
        return $data;
    }

}