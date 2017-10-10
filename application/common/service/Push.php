<?php
namespace app\common\service;
class Push extends \app\common\service\Base
{

    public    $push_id = 0;

    protected $title        = '您有一条新消息！请查看';
    protected $data         = [];
    protected $target       = [];
    protected $platform     = '1';

    protected $fail_desc    = '推送失败！';


    public function __construct( )
    {

    }

    //添加消息内容
    public function data( $data = []){
        if($data){
            $this->data = $data;
        }
        if(isset($this->data['title'])){
            $this->title = $this->data['title'];
        }
        return $this;
    }

    //设置接收推送的目标
    public function target( $type, $id){
        if(isset($this->target[$type])){
            $this->target[$type] = array_merge($this->target[$type],$id);
        }else{
            $this->target[$type] = $id;
        }
        return $this;
    }

    //定义客户端类型
    public function platform( $platform){
        $this->platform = $platform;
        return $this;
    }

    public function push( $push_id = false, $type = false, $id = false){
        if(false !== $type){
            $this->target( $type, $id);
        }

        //开始推送
        //1.判断data是否存在，2.判断目标是否存在
        if(!$this->data){
            $this->fail_desc = '错误的推送内容！';
            return false;
        }
        if(!$this->target){
            $this->fail_desc = '错误的推送目标！';
            return false;
        }
        foreach ($this->target as $target_type => $target_id){
            $this->send($push_id, $target_type,$target_id);
        }
        return true;
    }

    //获取推送失败原因
    public function getError(){
        return $this->fail_desc?$this->fail_desc:'消息推送失败！';
    }

    /******************************** 私有方法 ****************************************/
    protected function send($push_id = false, $type, $id){
        if($type){
            //如果是推送给不同的类型，就分多次推送，如果是推送相同目标类型，就一次推送

            //99推送会话 100全体用户 101某用户 102某标签
            switch ($type){
                //1-全部 2-tag 3-别名 4-registration_id
                case '100':
                    $audience_pre = '';
                    $audience = 1;
                    break;
                case '101':
                    $audience_pre = '';
                    $audience = 3;
                    break;
                default:
                    //当推送标签时，自动添加对应的头，新增标签时，在下面的函数里面加case
                    $audience_pre = $this->getTagPre($type);
                    $audience = 2;
            }

            if($push_id){
                $this->data['session_id'] = $push_id;
            }

            if(!is_array($id)){
                $id = [$id];
            }

            if(2 == $audience){
                foreach ($id as $k => $v){
                    $id[$k] = $audience_pre.$v;
                }
            }
            if($this->jpush($this->title,$this->data,$audience,$id,$this->platform)){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    //根据type值获取标签前缀
    protected function getTagPre( $type){
        switch ($type){
            case '102':
                return '';
                break;
            default:
                return '';
        }
    }

    //使用jpush实际发送推送
    protected function jpush($title,$data,$audience,$receiver,$platform=1){
        if(isset($data['content']) && mb_strlen($data['content'] > 60)){
            $data['content'] = mb_substr($data['content'],0,60);
        }

        $appkeys = config('JPUSH.appkey');//'61a19f462bc1829dbda090c1';
        $masterSecret = config('JPUSH.masterSecret');//'9938e8648c5a9c3d7719b6b7';
        $url = 'https://api.jpush.cn/v3/push';
        switch ($platform) {
            case '1':
                $platform = 'all';
                break;
            case '2':
                $platform = [
                    'android','ios'
                ];
                break;
            case '3':
                $platform = [
                    'android'
                ];
                break;
            case '4':
                $platform = [
                    'ios'
                ];
                break;
            default:
                $platform = 'all';
                break;
        }

        //如果audience不是1,则receiver不能为空
        if($audience != 1){
            if (empty($receiver) || !is_array($receiver)) {
                return FALSE;
            }
        }

        switch ($audience) {
            case '1':
                $audience = 'all';
                break;
            case '2':
                $audience = ['tag'=>array_map(function($a){return $a.'';},$receiver)];
                break;
            case '3':
                $audience = ['alias'=>array_map(function($a){return $a.'';},$receiver)];
                break;
            case '4':
                $audience = ['registration_id'=>array_map(function($a){return $a.'';},$receiver)];
                break;
            default:
                $audience = 'all';
                break;
        }

        $content = [
            'platform' => $platform,
            'audience' => $audience,
            'notification' => [
                "android" =>[
                    "alert" => $data['content'],
                    "title" => $title,
                    "extras"=> $data,
                ],
                "ios" =>[
                    "alert"  => $data['content'],
                    "sound"  => 'sound.caf',
                    "badge"  => '+1',
                    "extras" => $data,
                ]
            ],
            'options' => [
                'time_to_live'    => 60,
                "apns_production" => false,
            ]
        ];


        $curl = new \transfer\Curl();
        $curl->header("Authorization:Basic ".base64_encode("$appkeys:$masterSecret"));
        $curl->header("Content-Type:application/json");
        if($curl->url($url)->post($content,'json')->https()){
            $res = $curl->toArray();
            if(isset($res['error']['message'])){
                return $this->getJPushError($res['error']['message']);
            }else{
                return $res;
            }
        }else{

            return false;
        }
    }

    protected function getJPushError($code){
        switch ($code) {
            case '1000':
                return '系统内部错误';
                break;
            case '1001':
                return '只支持 HTTP Post 方法';
                break;
            case '1002':
                return '缺少了必须的参数';
                break;
            case '1003':
                return '参数值不合法';
                break;
            case '1004':
                return '验证失败';
                break;
            case '1005':
                return '消息体太大';
                break;
            case '1008':
                return 'app_key参数非法';
                break;
            case '1011':
                return '没有满足条件的推送目标';
                break;
            case '1020':
                return '只支持 HTTPS 请求';
                break;
            case '1030':
                return '内部服务超时,请稍候重试';
                break;
        }
        return true;
    }

}