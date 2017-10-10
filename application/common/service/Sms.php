<?php
namespace app\common\service;
class Sms extends \app\common\service\Base
{
    protected $content = [];
    protected $t_num   = '';
    protected $phone   = [];

    //模板内容
    public function template($t_num,$content = []){
        if($t_num)$this->t_num = $t_num;
        if($content)$this->content = $content;
        return $this;
    }

    //接收的手机
    public function phone($phone = '',$pre = '+86'){
        if($phone && !in_array($phone,array_column($this->phone,'phone'))){
            $this->phone[] = [
                'phone' => $phone,
                'pre'   => $pre
            ];
        }
        return $this;
    }

    //发送
    public function send(){
        if(!$this->phone || !$this->t_num){
            return false;
        }
        $get = [
            'content'   => json_encode($this->content),
            'mobile'    => $this->phone[0],
            'tNum'      => $this->t_num//'T150606060604'
        ];

        $host = "http://ali-sms.showapi.com";
        $path = "/sendSms";
        $headers = ['Authorization:APPCODE ' .config('sms.code')];
        $url = $host.$path;
        return http_curl($url, $get, [], $headers);
    }

    //发送国外短信
    protected function sendAbroad(){

    }



}