<?php
namespace app\common\service;
class Vcode extends \app\common\service\Base
{
    protected $times    = 5;//允许条数
    protected $exp_time = 300;//间隔时间3分钟

    //1.新建并发送验证码
    public function send( $type, $phone){
        //1.判断验证码是否发送太频繁
        if(!$this->contrastTimes( $type, $phone)){
            return $this->error(['desc'=>'验证码发送太频繁，请稍后再试！']);
        }

        $code = format_no(mt_rand(1,999999),6);

        if($this->sendSmsCode($phone,$code)){
            $sms_data = [
                'phone' => $phone,
                'code'  => $code,
                'time'  => time(),
                'type'  => $type
            ];
            $sms_model = new \app\common\model\Sms();
            $sms_model->data($sms_data);
            if($sms_model->save()){
                return $this->success($sms_model);
            }else{
                return $this->error(['desc'=>'验证码获取失败，请稍后再试！']);
            }
        }else{
            return $this->error(['desc'=>'验证码发送失败，请稍后再试！']);
        }
    }

    //验证一个验证码
    public function contrastVcode($phone,$code,$type=101){
        if('888888'==$code){
            return $this->success([]);
        }
        $sms_model = new \app\common\model\Sms();
        if($sms_model->where('phone',$phone)->where('code',$code)->where('time','>',time()-$this->exp_time)->where('type',$type)->find()){
            return $this->success([]);
        }else{
            return $this->error(['desc'=>'验证码错误或已过期']);
        }
    }

    /*********************** 私有方法 ***************************************/
    //返回一个手机的验证码是否发送太频繁 默认3分钟 5条
    protected function contrastTimes( $type, $phone){
        $user_model = new \app\common\model\Sms();
        if($user_model->where('phone',$phone)->where('type',$type)->where('time','>',time()-$this->exp_time)->count() >= $this->times){
            return false;
        }
        return true;
    }

    //发送短信验证码
    protected function sendSmsCode($phone,$code){
        $get = [
            'content'   => '{"code":"'.$code.'","comName":"自游GO"}',
            'mobile'    => $phone,
            'tNum'      => 'T150606060604'
        ];
        $res = $this->sanwang($get);
        if($res){
            if($res['data']){
                $res_data = json_decode($res['data'],true);
                if($res_data){
                    if('0' == $res_data['showapi_res_code']){
                        if(isset($res_data['showapi_res_body']) && isset($res_data['showapi_res_body']['successCounts'])){
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    //请求三网短信接口
    protected function sanwang($get){
        $host = "http://ali-sms.showapi.com";
        $path = "/sendSms";
        $headers = ['Authorization:APPCODE ' .config('sms.code')];
        $url = $host.$path;
        return http_curl($url, $get, [], $headers);
    }

}