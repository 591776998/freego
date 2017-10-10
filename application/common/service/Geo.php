<?php
namespace app\common\service;
class Geo extends \app\common\service\Base
{
    protected $data   = [];
    protected $out    = [];

    //初始化
    public function sl($arg1 = false,$arg2 = false,$arg3 = false){
        if(is_array($arg1) || is_object($arg1)){
            //如果是经纬度
            if(2 == arr_dim($arg1)){
                //二维数组，批量赋值
                foreach ($arg1 as $vo){
                    $this->_push($this->_arrDe($vo));
                }
            }else{
                //一维数组
                $this->_push($this->_arrDe($arg1));
            }
        }else{
            $this->_push($arg1,$arg2 ,$arg3?:'');
        }
        return $this;
    }

    //获取位置
    public function gl(){
        //格式化位置信息，输出
        return $this->success($this->_outPut());
    }

    protected function _outPut(){
        $temp = $this->data;

        $la = [];
        $lo = [];

        foreach ($temp as $k => $v){
            if(1 == $v[0]){
                $la[$k] = [
                    'lat'   => $v[1],
                    'lng'   => $v[2],
                    'title' => $v[3]
                ];
            }elseif(2 == $v[0]){
                $lo[$k] = [
                    'location_name' => $v[1],
                    'location_value'=> $v[2],
                    'title'         => $v[3]
                ];
            }
        }
        $la_res = [];
        if($la){
            $la_res = $this->geoDecodes($la);
            if(!$this->isError($la_res)){
                $la_res = $la_res['data'];
            }
        }
        $lo_res = [];
        if($lo){
            //转换格式
            $lo_res = $this->success($this->_fullLocationArr($lo));
            if(!$this->isError($lo_res)){
                $lo_res = $lo_res['data'];
            }
        }


        return array_map(function($vo)use($lo_res,$la_res){
            $temp = [];
            if(1 == $vo[0]){
                foreach ($la_res as $k => $v){
                    if( ($v['lat'] == $vo[1]) && ($v['lng'] == $vo[2])){
                        if($vo[3])$v['title'] = $vo[3];
                        return $v;
                    }
                }
            }elseif (2 == $vo[0]){
                foreach ($lo_res as $k => $v){
                    if( ($v['location_name'] == $vo[1]) && ($v['location_value'] == $vo[2])){
                        if($vo[3])$v['title'] = $vo[3];
                        return $v;
                    }
                }
            }
            return $temp;
        },$temp);
    }

    //拆解数组形式的输入参数
    protected function _arrDe($arr = []){
        $data = $this->_empty();
        if(is_assoc($arr)){
            //关联数组
            if(isset($arr['lat']) && isset($arr['lng'])){
                //经纬度位置
                $data = [
                    1,
                    $arr['lat'],
                    $arr['lng'],
                    ''
                ];
                if(isset($arr['title']))$data[] = $arr['title'];
            }elseif (isset($arr['location_name']) && isset($arr['location_value'])){
                //location_name 位置
                $data = [
                    2,
                    $arr['location_name'],
                    $arr['location_value'],
                    ''
                ];
                if(isset($arr['title']))$data[] = $arr['title'];
            }
        }else{
            //索引数组
            if(isset($arr[0]) && isset($arr[1])){
                if(is_numeric($arr[0]) && is_numeric($arr[1])){
                    $data[0] = 1;
                }else{
                    $data[0] = 2;
                }
                $data[1] = $arr[0];
                $data[2] = $arr[1];
                if(isset($arr[2]))$data[3] = $arr[2];
            }
        }
        return $data;
    }

    //单个输入位置入栈
    protected function _push($arg1 = false,$arg2 = false,$title = '',$type = false){
        if(is_array($arg1) ){
            if(false === $arg2 && '' === $title && false === $type ){
                $this->data[] = $arg1;
            }else{
                $this->data[] = $this->_empty();
            }
        }else{
            if(is_numeric($arg1) && is_numeric($arg2)){
                if(false === $type)$type = 1;
                $this->data[] = [
                    $type,
                    $arg1,
                    $arg2,
                    $title
                ];
            }else{
                if(false === $type)$type = 2;
                $this->data[] = [
                    $type,
                    $arg1,
                    $arg2,
                    $title
                ];
            }
        }
        return true;
    }

    //给一个空的位置数组
    protected function _empty(){
        return [
            0,
            0,
            0,
            ''
        ];
    }

    //返回一个空的输出位置信息
    protected function _emptyOut($data = []){
        //type 1经纬度 2.location 3.
        $out = [
            'lat'               => '0',
            'lng'               => '0',

            'location_name'     => '',
            'location_value'    => '',

            't_code'            => '',
            'c_code'            => '',
            'p_code'            => '',
            'city'              => '',
            's_code'            => '',

            'title'             => '',
            'pre_city_title'    => '',

            't_name'            => '',
            'c_name'            => '',
            'p_name'            => '',
            'city_name'         => '',
            's_name'            => '',
        ];
        if($data){
            $out = array_merge($out,$data);
        }
        return $out;
    }

    //批量对经纬度反地理编码
    protected function geoDecodes($arr){
        $temp = $this->_req($arr);
//        debug($temp,'$temp');
        if($this->isError($temp)){
            return $temp;
        }else{
            return $this->success($this->_fullLocationArr($temp['data']));
        }
    }

    //发出多线程请求
    protected function _req($arr,$coordtype = 'gcj02ll'){
        $url = 'http://api.map.baidu.com/geocoder/v2/';
        $arr = array_map(function($a)use($url,$coordtype){
            return [
                'url'   => $url.'?'.http_build_query([
                                'coordtype'     => $coordtype,
                                'location'      => (isset($a['lat'])?$a['lat']:'30.541154').','.(isset($a['lng'])?$a['lng']:'104.075276'),
                                'output'        => 'json',
                                'pois'          => '0',
                                'ak'            => 'sNgblvoj7soi2Z4zgcUGfkapCXxh2zVD'
                            ]),
                'lat'   => $a['lat'],
                'lng'   => $a['lng'],
                'title' => isset($a['title'])?$a['title']:''
            ];
        },$arr);
        $result = http_curl_multi(array_column($arr,'url'));
        if($result){
            $res_data = array_map(function ($a)use($result){
                $temp = [
                    'lat'   => $a['lat'],
                    'lng'   => $a['lng'],
                    'title' => $a['title'],
                    'location_name' => '',
                    'location_value'=> ''
                ];
                foreach ($result as $res_k => $res){
                    if($a['url'] == $res_k){
                        $res = json_decode($res['results'],true);
                        if($res && isset($res['status']) && 0 === $res['status']){

                            if(0 == $res['result']['addressComponent']['country_code']){
                                $temp['location_name']  = 't_code';
                                $temp['location_value'] = $res['result']['addressComponent']['adcode'];
                            }else{
                                $temp['location_name']  = 'city';
                                $temp['location_value'] = $res['result']['cityCode'];
                            }

                            if(!$temp['title'])$temp['title'] = $res['result']['sematic_description'];

                            //下面是保存国家和城市信息
                            $this->addAddress($res['result']);

                        }else{
//                            debug($res['results'],'paserError');
                        }
                    }
                }
                return $temp;
            },$arr);
            return $this->success($res_data);
        }else{
            return $this->error(['desc'=>'解析位置信息失败！']);
        }
    }

    //
    protected function addAddress( $data = []){
        $s_code         = isset($data['addressComponent']['country_code'])?$data['addressComponent']['country_code']:'-1';
        if(isset($data['addressComponent']['country_code']) && (-1 != $s_code)){
            $s_name         = isset($data['addressComponent']['country'])?$data['addressComponent']['country']:'';
            $city           = isset($data['cityCode'])?$data['cityCode']:'';
            $city_name      = isset($data['addressComponent']['city'])?$data['addressComponent']['city']:'';
            $province_name  = isset($data['addressComponent']['province'])?$data['addressComponent']['province']:'';
            $t_code         = isset($data['addressComponent']['province'])?$data['addressComponent']['adcode']:'';
            $t_name         = isset($data['addressComponent']['province'])?$data['addressComponent']['district']:'';

            if($s_code) {
                $this->addCountry([
                    's_code' => $s_code,
                    'name'   => $s_name
                ]);
            }

            if($city){
                if($s_code){
                    //外国
                    $this->addCity([
                        'city'      => $city,
                        'name'      => $city_name,
                        'province'  => $province_name,
                        's_code'    => $s_code,
                    ]);
                }else{
                    $this->addCity2([
                        'city'      => $city,
                        'name'      => $city_name,
                        'province'  => $province_name,
                        'c_code'    => floor($t_code/100)*100,
                        'p_code'    => floor($t_code/10000)*10000,
                    ]);
                }
            }

            if($t_code){
                $this->addTown([
                    'name'      => $t_name,
                    'c_code'    => floor($t_code/100)*100,
                    't_code'    => $t_code,
                ]);
            }
        }
    }

    //添加国内县区
    protected function addTown($data){
        if($data['t_code'] && !model('addressTown')->where('t_code',$data['t_code'])->find()){
            return model('addressTown')->data([
                'c_code'    => $data['c_code'],
                't_code'    => $data['t_code'],
                'name'      => trim(strtolower($data['name'])),
            ])->isUpdate(false)->save();
        }else{
            return false;
        }
    }

    //添加国内城市
    protected function addCity2($data){
        if($data['city'] && !model('addressCity')->where('city',$data['city'])->find()){
            return model('addressCityOther')->data([
                'c_code'    => $data['c_code'],
                'city'      => $data['city'],
                'name'      => trim(strtolower($data['name'])),
                'del_time'  => '0',
                'img_id'    => '0',
                'p_code'    => $data['p_code'],
            ])->isUpdate(false)->save();
        }else{
            return false;
        }
    }

    //保存不存在的城市到数据库
    protected function addCity($data){
        if($data['city'] && !model('addressCityOther')->where('city',$data['city'])->find()){
            return model('addressCityOther')->data([
                's_code'    => $data['s_code'],
                'city'      => $data['city'],
                'name'      => trim(strtolower($data['name'])),
                'main_name' => trim(strtolower($data['province'])),
                'del_time'  => '0',
                'img_id'    => '0',
            ])->isUpdate(false)->save();
        }else{
            return false;
        }
    }

    //保存不存在的国家到数据库
    protected function addCountry($data){
        if($data['s_code'] && !model('addressCountry')->where('s_code',$data['s_code'])->find()){
            model('addressCountry')->data($s_data = [
                's_code'    => $data['s_code'],
                'i_code'    => '1',
                'name'      => trim(strtolower($data['name'])),
                'del_time'  => '0',
                'img_id'    => '0',
            ])->isUpdate(false)->save();
        }
    }

    //输入location,返回完整信息
    protected function _fullLocation($code_name = '', $code_value = ''){
        $code_name  = trim($code_name);
        $code_value = trim($code_value);
        $res = [
            'location_name'     => $code_name,
            'location_value'    => $code_value,
            'location_str'      => '',

            't_code'            => '',
            'c_code'            => '',
            'p_code'            => '',
            'city'              => '',
            's_code'            => '',

//          'title'             => '',
            'pre_city_title'    => '',

            't_name'            => '',
            'c_name'            => '',
            'p_name'            => '',
            'city_name'         => '',
            's_name'            => '',
        ];

        $data = [];
        switch ($code_name){
            case 'p_code':
                $data = model('AddressProvince')->with(['father'])->where('p_code',$code_value)->find();
                break;
            case 'c_code':
                $data = model('AddressCity')->with(['father'=>function($query){
                    return $query->with(['father']);
                }])->where('c_code',$code_value)->find();
                break;
            case 't_code':
                    $data = model('AddressTown')->with(['father'=>function($query){
                        return $query->with(['father'=>function($query){
                            return $query->with(['father']);
                        }]);
                    }])->where('t_code',$code_value)->find();
                    break;
            case 'city':
                    $data = model('AddressCityOther')->with(['father'])->where('city',$code_value)->find();
                    break;
            case 's_code':
                    $res['s_code']        = $code_value;
                    break;
        }
        if($data){
            $res['location_str'] = $data['name'];
            $res = array_merge($res,$this->_loop($code_name,$data));
        }
        return $res;
    }

    //批量返回完整location信息
    protected function _fullLocationArr($arr = []){
        //初始化空的数组
        $res = array_map(function($vo){
            return $this->_emptyOut($vo);
        },$arr);

        $name_arr = [];
        foreach ($arr as $vo){
            if(isset($vo['location_name'])) {
                $name_arr[$vo['location_name']][] = $vo['location_value'];
            }
        }

        $res_temp = [];
        foreach ($name_arr as $name => $code){
            $res_temp = array_merge($res_temp,array_map(function($vo)use($name){
                $temp = $this->_loop($name,$vo);
                $temp['location_str']   = $vo['name'];
                $temp['location_name']  = $name;
                $temp['location_value'] = '';
                if(isset($temp[$name]))$temp['location_value'] = $temp[$name];
                return $temp;
            },$this->_getDbData($name,$code)));
        }

        return array_map(function($vo)use($res_temp){
            foreach ($res_temp as $v) {
                if($vo['location_name'] == $v['location_name'] && $vo['location_value'] == $v['location_value']){
                    //合并
                    if(isset($vo['title']) && $vo['title'])$v['title'] = $vo['title'];

                    if(!isset($v['lat']))$v['lat'] = '0';
                    if(isset($vo['lat'])&& $vo['lat'])$v['lat'] = $vo['lat'];

                    if(!isset($v['lng']))$v['lng'] = '0';
                    if(isset($vo['lng']) && $vo['lng'])$v['lng'] = $vo['lng'];

                    return array_merge($vo,$v);
                }
            }
            return $vo;
        },$res);
    }

    //批量查询某类型的location信息
    protected function _getDbData($code_name,$code_value = []){
        $data = [];
        switch ($code_name){
            case 't_code':
                $data = model('AddressTown')->with(['father'=>function($query){
                    return $query->with(['father'=>function($query){
                        return $query->with(['father']);
                    }]);
                }])->where('t_code','in',$code_value)->select();
                break;
            case 'c_code':
                $data = model('AddressCity')->with(['father'=>function($query){
                    return $query->with(['father']);
                }])->where('c_code','in',$code_value)->select();
                break;
            case 'p_code':
                $data = model('AddressProvince')->with(['father'])->where('p_code','in',$code_value)->select();
                break;
            case 'city':
                $data = model('AddressCityOther')->with(['father'])->where('city','in',$code_value)->select();
                break;
            case 's_code':
                $data = model('AddressCountry')->where('s_code','in',$code_value)->select();
                break;
        }
        return $data;
    }

    //递归获取完整的数据
    protected function _loop($code_name,$input){
        $fill = $this->_fill($code_name,$input);
        if(isset($input['father']) && $input['father']){
            $father_code = $this->_fatherCodeName($code_name);
            if($father_code){
                $fill = $this->_merge($fill,$this->_loop($father_code,$input['father']));
            }
        }
        return $fill;
    }

    //传诶两层的数据，组合
    protected function _merge($bot,$top){
        $temp = array_merge($bot,$top);
        $temp['pre_city_title'] = $top['pre_city_title'].$bot['pre_city_title'];
        return $temp;
    }

    //给出数据和对应的location_name
    protected function _fill($type,$data){
        $res = [
            'pre_city_title'    => ''
        ];
        switch ($type) {
            case 't_code':
                //格式化本层的信息
                $res['pre_city_title'] = '';

                $res[$type]    = $data[$type];
                $res['t_name'] = $data['name'];
                break;
            case 'c_code':
                $res['pre_city_title'] = $data['name'];

                $res[$type]    = $data[$type];
                $res['c_name'] = $data['name'];

                $res['lat'] = $data['lat'].'';
                $res['lng'] = $data['lng'].'';
                break;
            case 'p_code':
                $res['pre_city_title'] = '';

                $res[$type]    = $data[$type];
                $res['p_name'] = $data['name'];
                break;
            case 'city':
                $res['pre_city_title'] = $data['name'];

                $res[$type]    = $data[$type];
                $res['city_name'] = $data['name'];

                $res['lat'] = $data['lat'].'';
                $res['lng'] = $data['lng'].'';
                break;
            case 's_code':
                $res['pre_city_title'] = $data['name'];

                $res[$type]    = $data[$type];
                $res['s_name'] = $data['name'];
                break;
        }
        return $res;
    }

    //获取父级code_name
    protected function _fatherCodeName($code_name){
        $arr = [
            't_code','c_code','p_code','s_code'
        ];
        $arr2 = ['city','s_code'];
        $t = $this->_next($code_name,$arr);

        if($t){
            return $t;
        }else{
            return $this->_next($code_name,$arr2);
        }
    }

    //获取数组下一个元素
    protected function _next($now,$arr){
        if(in_array($now,$arr)){
            $i = 0;
            foreach ($arr as $vo){
                if($i){
                    return $vo;
                }
                if($vo == $now)$i = 1;
            }
            return false;
        }else{
            return false;
        }
    }
}