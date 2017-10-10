<?php
namespace app\common\service;
class Netease extends \app\common\service\Base
{
    protected $baseUrl      = 'https://api.netease.im';
    protected $app_key      = '1fc96aee8310a6deb1d790a7b7ded3ff';
    protected $app_secret   = '6d4f65b5ecdc';

    protected $api_map      = [
        'create'            => '/nimserver/user/create.action',
        'refreshToken'      => '/nimserver/user/refreshToken.action',
        'updateUinfo'       => '/nimserver/user/updateUinfo.action',
        'update'            => '/nimserver/user/update.action',
        'getUinfos'         => '/nimserver/user/getUinfos.action',
        'add_friend'        => '/nimserver/friend/add.action',
        'del_friend'        => '/nimserver/friend/delete.action',
        'create_group'      => '/nimserver/team/create.action',
        'add_member'        => '/nimserver/team/add.action',
    ];

    //请求 网易云信 接口
    public function request($url,$param = []){
        $curl = new \transfer\Curl();
        $curl->baseUrl($this->baseUrl);

        $curl->header('Content-Type:application/x-www-form-urlencoded;charset=utf-8');
        $curl->header('AppKey:'.$this->app_key);
        $rand = md5(mt_rand(1,999999).THINK_START_TIME.'_hello_i_am_lvlin_:)');
        $curl->header('Nonce:'.$rand);
        $curl->header('CurTime:'.floor(THINK_START_TIME));
        $curl->header('CheckSum:'.$this->getCheckSum($this->app_secret,$rand,floor(THINK_START_TIME)));

        if($curl->url($this->getTrueUrl($url))->post($param,'string')->https()){
            $res = $curl->toArray();
            //是否有错误
            if($this->res_is_error($res)){
                return $this->error(['desc'=>isset($res['desc'])?$res['desc']:'云信响应错误','code'=>isset($res['code'])?$res['code']:'2017'],isset($res['desc'])?$res['desc']:'云信响应错误',isset($res['code'])?$res['code']:'2017');
            }
            if(isset($res['code']))unset($res['code']);
            if(isset($res['desc']))unset($res['desc']);
            return $this->success($res);
        }else{
            return $this->error(['desc'=>'云信无响应！']);
        }
    }

    /***************** 私有 ***********************************/
    protected function getTrueUrl($data = ''){
        if(isset($this->api_map[$data])){
            return $this->api_map[$data];
        }else{
            return $data;
        }
    }

    //生成    checkSum
    protected function getCheckSum($secret,$nonce,$time){
        return sha1($secret.$nonce.$time);
    }

    //判断云信响应是否有错误
    protected function res_is_error($data = []){
        if($data && is_array($data)){
            if(isset($data['code']) && (200 == $data['code'])){
                return false;
            }
        }
        return true;
    }

}
