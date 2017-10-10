<?php
namespace app\common\service;
class Cache extends \app\common\service\Base
{
    //1.文件缓存
    public function file( $name, $data = false, $time = 0){
        if(false !== $data){
            //保存
            $options = [
                // 缓存类型为File
                'type'  =>  'File',
                // 缓存有效期为永久有效
                'expire'=>  $time,
                // 缓存前缀
                'prefix'=>  'think',
                // 指定缓存目录
                'path'  =>  APP_PATH.'runtime/cache/file/',
            ];
            \think\Cache::connect($options);
            return \think\Cache::set($name,$data,$time);
        }else{
            //读取
            $options = [
                'type'  => 'File',
                'prefix'=> 'think',
                'path'  =>  APP_PATH.'runtime/cache/file/',
            ];
            \think\Cache::connect($options);
            return \think\Cache::get($name);
        }
    }


}