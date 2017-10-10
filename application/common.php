<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件


/************************************ 工具函数 ************************************************/

//判断一个数组是否是 关联数组
function is_assoc($arr){
    return array_keys($arr) !== range(0, count($arr) - 1);
}

//验证手机号格式
function is_phone($phone){
    return preg_match("/^1[34578]{1}\d{9}$/",$phone);
}

//验证是否是真实存在的邮箱 ,1匹配格式 2.查询MX记录
function is_true_email($email){
    if(filter_var($email, FILTER_VALIDATE_EMAIL) && checkdnsrr(array_pop(explode("@",$email)),"MX")){
        return true;
    }
    return false;
}

//数字左侧补0
function format_no( $no, $length = 1){
    $temp = pow(10,$length);
    if($temp > $no){
        return substr(strval($no+$temp),1,$length).'';
    }else{
        return substr($no,0,$length).'';
    }
}

/********************************** 分隔线 ******************************************/

//递归替换 null和非字符型变量
function result_filter( $data){
    if(is_array( $data)){
        return array_map(function($vo){
            return result_filter($vo);
        },$data);
    }elseif( is_object($data)){
        if( empty( (array)$data)){
            return $data;
        }else{
            if(method_exists($data,'toArray') ){
                return array_map(function($vo){
                    return result_filter($vo);
                },$data->toArray());
            }else{
                return $data;
            }
        }
    }if (is_null($data)){
        return (object)null;
    }else{
        return $data.'';
    }
}

//格式化的输出
function result($data = [],$desc = 'success',$code = '200'){
    header("Access-Control-Allow-Origin:*");
    header("Access-Control-Allow-Methods:GET,POST,PUT,DELETE");
    return json([
        'desc'  => $desc.'',
        'code'  => $code.'',
        'data'  => result_filter($data)
    ]);
}

//格式化的成功的输出
function success($data = [],$desc = 'success',$code = '200'){
    if(is_array($data)){
        if($data && isset($data['code']) && isset($data['desc']) && isset($data['data'])){
            return result($data['data'],$data['desc'],$data['code']);
        }else{
            return result($data,$desc,$code);
        }
    }else{
        return result($data,$desc,$code);
    }
}

//格式化失败输出
function error($data = [],$desc = 'error', $code = '201'){
    if($data && is_array($data)){
        if( 'error' == $desc && isset($data['desc'])){
            $desc = $data['desc'];
        }
        if( '201' == $code && isset($data['code'])){
            $code = $data['code'];
        }
        if(isset($data['data'])){
            $data = $data['data'];
        }
    }
    $result = [];
    if( !is_array($data) || !isset($data['debug'])){
        $result['debug'] = $data;
    }else{
        $result = $data;
    }

    if( 'error' == $desc && $result['debug']){
        //取出调试信息里的值
        if( is_string( $result['debug'])){
            $desc = $result['debug'];
        }else if ( isset($result['debug']['desc']) && is_string( $result['debug']['desc'])){
            $desc = $result['debug']['desc'];
        }
    }

    if( '201' == $code && $result['debug']){
        //取出调试信息里的值
        if ( isset($result['debug']['code'])){
            $code = $result['debug']['code'];
        }
    }

    return result($result,$desc,$code);
}

//获取配置
function systemConfig( $name, $default = false){
    $system = model('System','service');
    return $system->get($name, $default);
}

//输入参数验证
function checkModel( $data = []){
    $model = model('validate','service');
    if($data){
        return $model->data($data);
    }
    return $model;
}

//debug 函数
function debug( $data, $name ='',$type = 'array'){
    if(config('app_debug') && ('develop' == config('app_status'))){
        if($name)echo $name.':';
        if('json' == $type){
            echo json_encode($data);
        }else{
            dump(json_decode(json_encode($data),true));
        }
    }else{
        if(is_array($data) || is_object($data)){
            $data = json_encode($data);
        }
        model('debug')->isUpdate(false)->data(['val'=>$data,'name'=>$name,'c_time'=>THINK_START_TIME])->save();
    }
}

function imma($data = ''){
    if(is_array($data) || is_object($data)){
        $data = json_encode($data);
    }
    echo "$data
            ";
    //有换行时 浏览器会显示
    ob_flush();//刷新php缓冲
    flush();//刷新apache缓冲
}

//图片完整路径获取
function img_show($img){
    if(!$img)return '';
    if(filter_var($img, FILTER_VALIDATE_IP) || filter_var($img, FILTER_VALIDATE_URL)) {
        return strtr($img,'\\','/');
    }else {
        return strtr(config('img_url').$img,'\\','/');
    }
}

//生成url路径 默认当前页面
function web_url($url = '', $param = [], $domain = false,$paramodel = '1'){
    if(0 !== strpos($url,'/') ){
        $url = explode('/',$url);
        $url = implode('/',array_merge(array_slice(['index','web'],0,3-count($url)),$url));
    }else{
        $url = substr($url,1);
    }
    if(1 == $paramodel){
        if($param){
            $limiter = '?';
            if(false !== stripos($url,'?'))$limiter = '&';
        }else{
            $limiter = '';
        }
        return ($domain?config('request_base_url'):config('domain_correct')).$url.$limiter.(is_array($param)?http_build_query($param):$param);
    }else{
        if($param){
            $url = rtrim($url,'/').'/';
            if(is_array($param)){
                $temp = [];
                foreach ($param as $k => $v){
                    $temp[] = $k.'/'.$v;
                }
                $param = implode('/',$temp);
            }
        }

        return ($domain?config('request_base_url'):config('domain_correct')).$url.(is_array($param)?http_build_query($param):$param);
    }
}

//视频完整路径获取
function video_show($url){
    if(!$url)return '';
    if(filter_var($url, FILTER_VALIDATE_IP) || filter_var($url, FILTER_VALIDATE_URL)) {
        return $url;
    }else {
        return config('video_url').$url;
    }
}

/**
 *  @desc 根据两点间的经纬度计算距离
 *  @param float $lat 纬度值
 *  @param float $lng 经度值
 */
function get_distance($lat1, $lng1, $lat2, $lng2)
{
    $earthRadius = 6367000; //approximate radius of earth in meters
    $lat1 = ($lat1 * pi() ) / 180;
    $lng1 = ($lng1 * pi() ) / 180;

    $lat2 = ($lat2 * pi() ) / 180;
    $lng2 = ($lng2 * pi() ) / 180;

    $calcLongitude = $lng2 - $lng1;
    $calcLatitude = $lat2 - $lat1;
    $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);  $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
    $calculatedDistance = $earthRadius * $stepTwo;

    return round($calculatedDistance);
}


/************************************ 网络 *****************************************/

/**
 * 极光推送V3版本
 *----------------------------------------
 * $title    推送消息的标题
 * $message  推送消息的 extras字段，里面放自定义信息
 * $audience 推送类型            1-全部 2-tag 3-别名 4-registration_id
 * $receiver 如果推送类型不为1时， receiver为一维数组且不能为空
 * $platform 推送消息平台         1-全部 2-android和ios 3-仅android 4-仅ios
 *----------------------------------------
 **/
function push3($title='',$message='',$audience=1,$receiver='',$platform=1){
    $appkeys = config('JPUSH.appkey');//'61a19f462bc1829dbda090c1';
    $masterSecret = config('JPUSH.masterSecret');//'9938e8648c5a9c3d7719b6b7';
    $url = 'https://api.jpush.cn/v3/push';

    $header=array("Authorization:Basic ".base64_encode("$appkeys:$masterSecret"),"Content-Type:application/json");
    switch ($platform) {
        case '1':
            $platform = 'all';
            break;
        case '2':
            $platform = array(
                'android','ios'
            );
            break;
        case '3':
            $platform = array(
                'android'
            );
            break;
        case '4':
            $platform = array(
                'ios'
            );
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
            $audience = array('tag'=>$receiver);
            break;
        case '3':
            $audience = array('alias'=>$receiver);
            break;
        case '4':
            $audience = array('registration_id'=>$receiver);
            break;
        default:
            $audience = 'all';
            break;
    }

    $content = array(
        'platform' => $platform,
        'audience' => $audience,
        'notification' => array (
            "android" =>array(
                "alert" => $message['content'],
                "title" => $title,
                "extras"=> $message,
            ),

            "ios" =>array(
                "alert"  => $message['content'],
                "sound"  => 'sound.caf',
                "badge"  => '+1',
                "extras" => $message,
            )
        ),
        'options' => array(
            'time_to_live'    => 60,
            "apns_production" => true,

        )
    );

    $curlPost = json_encode($content);

    $data = http_curl($url,[],$curlPost,$header);
//    $ch = curl_init();//初始化curl
//    curl_setopt($ch, CURLOPT_URL,$postUrl);//指定网页
//    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
//    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
//    curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
//    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
//    curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
//    //增加 HTTP Header（头）里的字段
//    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
//    //终止从服务端进行验证
//    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
//    $data = curl_exec($ch);//运行curl
//


    $res =  json_decode($data['data'],true);
    $error_code =  getPushError($res['error']['message']);

    if (!empty($error_code)) {
        return $error_code;
    }else{
        return TRUE;
    }
}
/**
 * 推送失败原因
 *----------------------------------------
 * @param  string null
 *----------------------------------------
 * @author chenhao
 * @access protected
 * @return void
 */
function getPushError($code){
    return true;
    if (empty($code)) {
        return '错误码不能为空';
    }
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
}

//curl
function http_curl($url, $get=[], $post=[], $header=false, $method = false){
    if($get){
        if(is_array($get)){
            $get = http_build_query($get);
        }
        if(false !== strpos($url,'?'))
            $url .= '&'.$get;
        else
            $url .= '?'.$get;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    if($method){
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }

    if($header){
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    else{
        curl_setopt($ch, CURLOPT_HEADER, 0);
    }

    if($post){
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    curl_setopt($ch, CURLOPT_TIMEOUT,10);

    //终止从服务端进行验证
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

    $output = curl_exec($ch);

    if($header){
        // 获得响应结果里的：头大小
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        // 根据头大小去获取头信息内容
        return ['data'=>substr($output, $headerSize),'header'=>substr($output, 0, $headerSize)];
    }else{
        curl_close($ch);
        return $output;
    }
}

//截取二维数组的键生成子集的二维数组
function subArr( $arr, $keys=array()){
    if(!is_array($keys))$keys = explode(',',$keys);
    if(!$arr||!$keys)return array();
    return array_map(function($array)use($keys){
        $temp = [];
        foreach ($keys as $key){
            $temp[$key] = $array[$key];
        }
        return $temp;
    },$arr);
}

/*
PHP CURL 多线程 GET/POST
Email:szj1006@vip.qq.com/QQ：690204663
Powered by XiaoSang
curl(array('url?get=data','url'),array('','post_data'));
$get 是公共的参数 $post 是单独的参数
*****************************************************************/
function http_curl_multi($urls,$get = array(),$post=array('','post_data'),$randip=false) {
    $queue = curl_multi_init();
    $map = array();

    foreach ($urls as $key => $url) {
        if($get){
            if(false !== strpos($url,'?'))
                $url .= '&'.http_build_query($get);
            else
                $url .= '?'.http_build_query($get);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 22);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        if($randip)curl_setopt($ch, CURLOPT_HTTPHEADER, randIp());

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOSIGNAL, true);
        curl_multi_add_handle($queue, $ch);
        $map[(string) $ch] = $url;
    }
    $responses = array();
    do {
        while (($code = curl_multi_exec($queue, $active)) == CURLM_CALL_MULTI_PERFORM) ;
        if ($code != CURLM_OK) { break; }
        while ($done = curl_multi_info_read($queue)) {
            $error = curl_error($done['handle']);
            $results = curl_multi_getcontent($done['handle']);
            $responses[$map[(string) $done['handle']]] = compact('error', 'results');
            curl_multi_remove_handle($queue, $done['handle']);
            curl_close($done['handle']);
        }
        if ($active > 0) {
            curl_multi_select($queue, 0.5);
        }
    } while ($active);
    curl_multi_close($queue);
    return $responses;
}

//此函数提供了国内的随机IP地址
function randIP(){
    $ip_long = array(
        array('607649792', '608174079'),    //36.56.0.0-36.63.255.255
        array('1038614528', '1039007743'), //61.232.0.0-61.237.255.255
        array('1783627776', '1784676351'), //106.80.0.0-106.95.255.255
        array('2035023872', '2035154943'), //121.76.0.0-121.77.255.255
        array('2078801920', '2079064063'), //123.232.0.0-123.235.255.255
        array('-1950089216', '-1948778497'), //139.196.0.0-139.215.255.255
        array('-1425539072', '-1425014785'), //171.8.0.0-171.15.255.255
        array('-1236271104', '-1235419137'), //182.80.0.0-182.92.255.255
        array('-770113536', '-768606209'), //210.25.0.0-210.47.255.255
        array('-569376768', '-564133889'), //222.16.0.0-222.95.255.255
    );
    $rand_key = mt_rand(0, 9);
    $ip= long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
    $headers['CLIENT-IP'] = $ip;
    $headers['X-FORWARDED-FOR'] = $ip;

    $headerArr = array();
    foreach( $headers as $n => $v ) {
        $headerArr[] = $n .':' . $v;
    }
    return $headerArr;
}

//左侧补齐0
function formatNumTo($num,$length,$str = '0',$left = true){
    if($length > strlen($num)){
        $num = str_pad($num,$length,$str,$left?STR_PAD_LEFT:STR_PAD_RIGHT);
    }
    return $num;
}

//从第一个数组中截取 与 后面数组的key 的 交集的数组 的 子集
function sub_intersect_arr($main_arr,$sub){
    $temp = [];
    $sub_k_arr = array_keys($sub);
    foreach ($main_arr as $k => $v){
        if(in_array($k,$sub_k_arr)){
            $temp[$sub[$k]] = $v;
        }else{
            $temp[$sub[$k]] = '';
        }
    }
    return $temp;
}

//数组批量赋值
function paramSet($param_arr,$relation){
    $temp = [];
    $p_key_arr = array_keys($param_arr);
    foreach ($relation as $k => $v){
        if(in_array($k,$p_key_arr)){
            if(is_array($v)){
                $temp[$v[0]] = $param_arr[$k];
            }else{
                $temp[$v]    = $param_arr[$k];
            }
        }else{
            //不存在该元素
            if(is_array($v)){
                $temp[$v[0]] = isset($v[1])?$v[1]:'';
            }else{
                $t = strstr( $v, '|');
                if(false !== $t){
                    $t = substr($t,1);
                }else{
                    $t = '';
                }
                $temp[$v] = $t;
            }
        }
    }
    return $temp;
}

//把小时转换成秒为单位
function timeToSecond($time = ''){
    return strtotime('1970-01-01 '.trim($time) )- strtotime('1970-01-01 00:00:00');
}

//秒转换成格式化的时间 1.转换成当年的日期 2.转成某日期 3转成从1天开始的小时:分钟数
function formatTimeStr($time = '',$level = '1'){
    switch ($level){
        case '1':
            //日期加时间
            if(date('Y') == date('Y',$time)){
                return date('m-d H:i',$time);
            }
            return date('Y-m-d H:i',$time);
            break;
        case '2':
            //日期
            if(date('Y') == date('Y',$time)){
                return date('m-d',$time);
            }
            return date('Y-m-d',$time);
            break;
        case '3':
            //时间
            return date('H:i',$time-28800);
        default:
            //完整日期时间
            return date('Y-m-d H:i:s',$time);
    }
}

//把时间戳格式化为人性化的时间字符
function humanizedTime($time = 0){
    $temp = THINK_START_TIME - $time;
    $abs = abs($temp);
    $today_start = strtotime(date('Y-m-d'));
    $year_start  = strtotime(date('Y-00-00'));
    if(0 < $temp){
        if($abs < 60){
            $str = '刚刚';
        }elseif ($abs < 3600){
            $str = (int)($temp/60).'分钟前';
        }elseif ($abs < 86400) {
            $str = (int)($temp / 3600) . '小时前';
        }elseif($abs < (THINK_START_TIME - ( $today_start - 86400 ))){
            $str = '昨天';
        }elseif($abs < (THINK_START_TIME - ( $today_start - ( 10 *86400)))){
            $str = (int)($temp/86400).'天前';
        }elseif($abs < (THINK_START_TIME - $year_start)){
            $str = ''.date('n月j日',$time);
        }else{
            $str = ''.date('Y年n月j日',$time);
        }
    }else{
        if($abs < 60){
            $str = '即将';
        }elseif ($abs < 3600){
            $str = (int)($temp/60).'分钟后';
        }elseif ($abs < 86400) {
            $str = (int)($temp / 3600) . '小时后';
        }elseif($abs < (THINK_START_TIME - ( $today_start - 86400 ))){
            $str = '明天';
        }elseif ($abs < (THINK_START_TIME - ( $today_start -  ( 10 *86400)))){
            $str = (int)($temp/86400).'天后';
        }elseif($abs < (THINK_START_TIME - $year_start)){
            $str = ''.date('n月j日',$time);
        }else{
            $str = ''.date('Y年n月j日',$time);
        }
    }
    return $str;
}

//人性化的距离显示
function humanizedDistance($m){
    $m = abs($m);
    if($m < 1000){
        return '<'.$m.'M';
    }else{
        return '<'.number_format($m/1000,2).'KM';
    }
}

/**
 * XML编码
 * @param mixed $data 数据
 * @param string $encoding 数据编码
 * @param string $root 根节点名
 * @return string
 */
function xml_encode($data, $encoding='utf-8', $root='data') {
    $xml    = '<?xml version="1.0" encoding="' . $encoding . '"?>';
    $xml   .= '<' . $root . '>';
    $xml   .= data_to_xml($data);
    $xml   .= '</' . $root . '>';
    return $xml;
}

/**
 * 数据XML编码
 * @param mixed $data 数据
 * @return string
 */
function data_to_xml($data) {
    $xml = '';
    foreach ($data as $key => $val) {
        is_numeric($key) && $key = "item id=\"$key\"";
        $xml    .=  "<$key>";
        $xml    .=  ( is_array($val) || is_object($val)) ? data_to_xml($val) : $val;
        list($key, ) = explode(' ', $key);
        $xml    .=  "</$key>";
    }
    return $xml;
}

//根据中心点经纬度和距离，计算最大最小经纬度
function getAround($lat,$lng,$range = 0){
    $range = (int)$range;
    if(20040000 < $range){
        return [
            'minlat'    => '-90',
            'maxlat'    => '90',
            'minlng'    => '-180',
            'maxlng'    => '180',
        ];
    }else{
        $degree = (24901*1609)/360;
        $dpmLat = 1/$degree;
        $radiusLat = $dpmLat*$range;
        $minLat = $lat - $radiusLat;
        $maxLat = $lat + $radiusLat;

        $mpdLng = $degree*cos($lat * (M_PI/180));
        $dpmLng = 1 / $mpdLng;
        $radiusLng = $dpmLng*$range;
        $minLng = $lng - $radiusLng;
        $maxLng = $lng + $radiusLng;
        return [
            'minlat'    => $minLat,
            'maxlat'    => $maxLat,
            'minlng'    => $minLng,
            'maxlng'    => $maxLng,
        ];
    }
}


/**
 * aes加密
 *
 * @author yzx
 * @param string $str
 * @return string
 */
function aesEncode($str){
    $str.= mt_rand(10000000,99999999);
    $CIPHER = MCRYPT_RIJNDAEL_128;
    $MODE = MCRYPT_MODE_ECB;
    $key = config('aes_key');
    $iv = mcrypt_create_iv(mcrypt_get_iv_size($CIPHER, $MODE), MCRYPT_RAND);

    return base64_encode(mcrypt_encrypt($CIPHER, $key, $str, $MODE, $iv));
}

/**
 * aes解密
 *
 * @author yzx
 * @param string $str
 * @return string
 */
function aesDecode($str){
    $CIPHER = MCRYPT_RIJNDAEL_128;
    $MODE = MCRYPT_MODE_ECB;
    $key = config('aes_key');
    $string = base64_decode($str);
    $iv = mcrypt_create_iv(mcrypt_get_iv_size($CIPHER, $MODE), MCRYPT_RAND);
    $dec_str = mcrypt_decrypt($CIPHER, $key, $string, $MODE, $iv);
    $other_str = substr($dec_str, strlen($dec_str)-1,strlen($dec_str));
    if (!is_numeric($other_str)){
        $sub_str = str_replace($other_str, "", $dec_str);
    }else {
        $sub_str = $dec_str;
    }
    $other_str = substr($sub_str, strlen($sub_str)-1,strlen($sub_str));
    if (!is_numeric($other_str)){
        $sub_str = str_replace($other_str, "", $sub_str);
    }
    $sub_str = substr($sub_str, 0,strlen($sub_str)-8);
    if (strpos($sub_str, "\\")){
        $sub_str = str_replace("\\", '', $sub_str);
    }
    return $sub_str;
}


//验证身份证
function isIdCard($IDCard){
    if (strlen($IDCard) == 18) {
        return check18IDCard($IDCard);
    } elseif ((strlen($IDCard) == 15)) {
        $IDCard = convertIDCard15to18($IDCard);
        return check18IDCard($IDCard);
    } else {
        return false;
    }
}

//计算身份证的最后一位验证码,根据国家标准GB 11643-1999
function calcIDCardCode($IDCardBody) {
    if (strlen($IDCardBody) != 17) {
        return false;
    }

    //加权因子
    $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
    //校验码对应值
    $code = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
    $checksum = 0;
    for ($i = 0; $i < strlen($IDCardBody); $i++) {
        $checksum += substr($IDCardBody, $i, 1) * $factor[$i];
    }

    return $code[$checksum % 11];
}

// 将15位身份证升级到18位
function convertIDCard15to18($IDCard) {
    if (strlen($IDCard) != 15) {
        return false;
    } else {
        // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
        if (array_search(substr($IDCard, 12, 3), array('996', '997', '998', '999')) !== false) {
            $IDCard = substr($IDCard, 0, 6) . '18' . substr($IDCard, 6, 9);
        } else {
            $IDCard = substr($IDCard, 0, 6) . '19' . substr($IDCard, 6, 9);
        }
    }
    $IDCard = $IDCard . calcIDCardCode($IDCard);
    return $IDCard;
}

// 18位身份证校验码有效性检查
function check18IDCard($IDCard) {
    if (strlen($IDCard) != 18) {
        return false;
    }
    $IDCardBody = substr($IDCard, 0, 17); //身份证主体
    $IDCardCode = strtoupper(substr($IDCard, 17, 1)); //身份证最后一位的验证码
    if (calcIDCardCode($IDCardBody) != $IDCardCode) {
        return false;
    } else {
        return true;
    }
}

//去除字符的换行
function replace_eol($str){
    return str_replace(["\t","\n","\r"], '', $str);
}

//判断数组的维度数
function arr_dim($array){
    if(is_array($array) || is_object($array)){
        if (count($array) == count($array, 1)){
            return 1;
        }
        else{
            return 2;
        }
    }else{
        return 0;
    }

}

//分块处理
function chunk($func,$data,$times,$sleep = 0){
    $res = [];
    $max = count($data);
    $num = $max/$times;
    for ($i = 0;$i < $num; $i++) {
        $r = $func(array_slice($data,$i*$times,$times));
        if(is_array($r) || is_object($r)){
            $res = array_merge($res,$r);
        }else{
            $res[] = $r;
        }
    }
    if($sleep){
        sleep($sleep);
    }
    return $res;
}

//二维数组去重
function array_unique_arr($data,$delimiter = '<<<lv_delimiter>>>'){
    //判断纬度
    if(1 == arr_dim($data)){
        return array_unique($data);
    }
    return array_map(function($vo)use($delimiter){
        return explode($delimiter,$vo);
    },array_unique(array_map(function ($vo)use($delimiter){
        return implode($delimiter,$vo);
    },$data)));
}

//百度坐标转国测局坐标
function bd09togcj02($lng, $lat){
    $x_pi = pi() * 3000.0 / 180.0;
    $x = $lng - 0.0065;
    $y = $lat - 0.006;
    $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
    $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
    $gg_lng = $z * cos($theta);
    $gg_lat = $z * sin($theta);
    return [$gg_lng, $gg_lat];
}

function uniqueSign($data){
    $limiter = '<<<lv_uniqueSign>>>';
    return md5(implode($limiter,$data));
}