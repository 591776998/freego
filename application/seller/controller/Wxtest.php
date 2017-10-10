<?php
namespace app\seller\controller;
use think\Request;
class Wxtest
{
   public function  index(Request $request){
        $nick_name=trim($request->param('nick_name'));
        $level= $request->param('level');
       if(!$nick_name){
           return error("请填写用户昵称");
       }
       $data=model('user')->where('nick_name',$nick_name)->find();
        if($data){
            $result=model('user')->where('nick_name',$nick_name)->update(['level'=>$level]);
            return success($result);
        }
        else {
            $datastatus="无该昵称用户";
            return error($datastatus);
        }

    }
}