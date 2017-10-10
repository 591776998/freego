<?php
namespace app\exception;

use Exception;
use think\exception\Handle;
//use think\exception\HttpException;

class Http extends Handle
{
    protected $ignoreReport = [
        '\\think\\exception\\HttpException',

    ];

    public function render(Exception $exception)
    {

//        $data = [
//            'file'    => $exception->getFile().' line:'.$exception->getLine(),
//            'code'    => $this->getCode($exception),
//            'message' => $this->getMessage($exception),
//        ];

        $data = [
            'message' => $this->getMessage($exception),
            'file'    => $exception->getFile().' line:'.$exception->getLine(),
            'code'    => $this->getCode($exception),
            'source'  => $this->getSourceCode($exception),
            'datas'   => $this->filter($this->getExtendData($exception)),
//            'trace'   => $exception->getTrace(),
            'tables'  => [
                'GET Data'              => $_GET,
                'POST Data'             => $_POST,
                'Files'                 => $_FILES,
                'Cookies'               => $_COOKIE,
                'Session'               => isset($_SESSION) ? $_SESSION : [],
                'Server/Request Data'   => $_SERVER,
                'Environment Variables' => $_ENV,
            ],
        ];
        $this->addLog($data,$data['message'],$data['file'],$exception->getLine());

        $this->sendEmail(json_encode($data),'服务器错误：'.$data['message']);

        return error($data, '网络错误，请稍后再试', '500');
    }

    protected function filter($data){
        if(isset($data['Database Config']))unset($data['Database Config']);
        return $data;
    }

    //添加500日志
    protected function addLog($data,$message,$file,$line){
        return model('\app\common\model\ErrorLog')->isUpdate(false)->data([
            'message'=> $message,
            'c_time'=> floor(THINK_START_TIME),
            'file'  => $file,
            'line'  => $line,
            'data'  => json_encode($data)
        ])->save();
    }

    //发送邮件
    protected function sendEmail($content,$title){
        if('production' == config('app_status')){
            $mail = new \transfer\Mail();
            return $mail->send($content, $title);
        }
        return true;
    }

}