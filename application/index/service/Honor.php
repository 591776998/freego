<?php
namespace app\index\service;
class Honor extends \app\common\service\Base
{

    //判断1个用户的等级应该处于什么等级
    public function is( $user_id){
        //1.获取条件组
        $h = model('honor')->select();
        $c = model('honor_set')->select();
        if($h){
            //1.获取各个等级的条件，2.然后所有等级需要计算的参数
            //1.取出所有需要参与运算的key
            $c_key = array_column($c,'key');

        }else{
            //没有配置等级
            return ['level'=>'0'];
        }
    }

    //取消赞
    public function delete($user, $id){




    }
}