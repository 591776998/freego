<?php
namespace app\cron\controller;
use think\Controller;
use think\db;

class Index extends Controller{

    protected $temp_value;

    //获取每个接口的执行频率
    protected function getTimes(){



    }

    public function test(){
        return 'test';
    }

    //半小时一次，从庄哥服务器 同步 分会信息到本地数据库
    public function branch(){
        $this->temp_value = model('branch')->select();
        $next_id = 0;
        do{
            $next_id = $this->saveBranch($next_id);
        }while($next_id);
        return '结束';
    }

    //传入一个开始的分会ID，对返回数据处理
    protected function saveBranch($id){
        $url = '/index/branch/index';
        $curl = new \transfer\Curl();
        $curl->baseUrl('http://fr.yoyopeilian.com');
        if($curl->url($url)->post(['next_id'=>$id])->http()){
            $data = $curl->toArray();
            if($data){
                //判断逻辑是否正确
                if(200 == $data['code']){
                    $new = $data['data'];
//                    imma('success:');
//                    imma($data);
                    if($new){
                        $new_id = array_column($new,'branch_id');
                        //求 b_id 在 new_id 的差集
//                        $b_id = array_column($this->temp_value,'branch_id');
                        $b_id = array_map(function ($vo){
                            return $vo['branch_id'];
                        },$this->temp_value);

                        //新的ID和旧的ID的差集
                        $add_id = array_diff($new_id,$b_id);

                        imma('$add_id');
                        imma($add_id);

                        $temp_update = [];

                        $temp_main = [];
                        $temp_login = [];
                        if($add_id){
                            foreach ($new as $k => $v){
                                if(in_array($v['branch_id'],$add_id)){
                                    if(!isset($v['location_name'])||!$v['location_name']){
                                        continue;
                                    }
                                    $lo = model('location','service')->read($v['location_name'], $v['location_value'], true);
                                    $v['t_code']    = $lo['t_code'];
                                    $v['c_code']    = $lo['c_code'];
                                    $v['p_code']    = $lo['p_code'];
                                    $v['s_code']    = $lo['s_code'];
                                    $v['city']      = $lo['city'];

                                    $temp_main[] = [
                                        'branch_id'         => $v['branch_id'],
                                        'p_id'              => isset($v['pid'])?$v['pid']:'',
                                        'name'              => isset($v['branch_name'])?$v['branch_name']:'',
                                        'code'              => isset($v['branch_code'])?$v['branch_code']:'',
                                        'del_time'          => '0',
                                        'location_name'     => isset($v['location_name'])?$v['location_name']:'',
                                        'location_value'    => isset($v['location_value'])?$v['location_value']:'',
                                        't_code'            => isset($v['t_code'])?$v['t_code']:'',
                                        'c_code'            => isset($v['c_code'])?$v['c_code']:'',
                                        'p_code'            => isset($v['p_code'])?$v['p_code']:'',
                                        's_code'            => isset($v['s_code'])?$v['s_code']:'',
                                        'city'              => isset($v['city'])?$v['city']:'',
                                        'login_phone'       => isset($v['sjhm'])?$v['sjhm']:'',
                                    ];
                                    $temp_login[] = [
                                        'branch_id' => isset($v['branch_id'])?$v['branch_id']:'',
                                        'phone'     => isset($v['sjhm'])?$v['sjhm']:'',
                                        'pwd'       => isset($v['dlmm'])?$v['dlmm']:'',
                                        'role'      => '101',
                                        'name'      => '负责人',

                                    ];
                                }else if(in_array($v['branch_id'],$b_id)){
                                    $old = $this->getDataByField(['branch_id'=>$v['branch_id']]);
                                    if($old){
                                        if(($old['location_name'] != $v['location_name']) || ($old['location_value'] != $v['location_value']) || ($old['p_id'] != $v['pid'])){
                                            $old = $old->toArray();
                                            $old['location_name'] = $v['location_name'];
                                            $old['location_value'] = $v['location_value'];
                                            $old['p_id'] = $v['pid'];
                                            $temp_update[] = $old;
                                        }
                                    }
                                }
                            }
                        }
                        imma($temp_main);

                        if($temp_main){
                            model('branch')->saveAll($temp_main);
                        }
                        if($temp_login){
                            model('branchLogin')->saveAll($temp_login);
                        }
                        if($temp_update){
//                            debug($temp_update);
                            model('branch')->saveAll($temp_update);
                        }
                        return max($new_id);
                    }else{
                        return 0;
                    }
                }else {
                    return 0;
                }
            }else{
                return 0;
            }
        }else{
            return 0;
        }
    }

    //
    public function getDataByField($field = []){
        foreach ($this->temp_value as $v){
            foreach ($field as $key => $val){
                if(isset($v[$key]) && ($v[$key] == $val)){
                    return $v;
                }
            }
        }
        return false;
    }

    //
    public function paserJson(){
        $data = file_get_contents('1.json');
        return json_decode($data,true);
    }

    //
    public function savedata(){
        set_time_limit(0);
        $data = $this->paserJson();
        foreach ($data as $k => $v){
//            debug($v);
            if(!$this->dealC($v)){
                return false;
            }

        }
        return success();
    }

    //传入1个国家，进行处理
    public function dealC($data){
        //1.查询数据有没有改数据
        if(!model('address_country')->where('name',trim($data['Name']))->find()){


            $res = $this->baidu1($data['Name']);
            if('智利' == $data['Name']){

            }
            if($res){
                if(isset($res['status']) && 0 == $res['status']){
                    $name = $data["Name"];
                    $lat = $res['result']['location']['lat'];
                    $lng = $res['result']['location']['lng'];
                    $sql = "INSERT INTO `address_country` (`name` , `lat` , `lng`) VALUES ('$name', $lat , $lng)";
                    return Db::execute($sql);
                }
//                model('address_country')->where('name',trim($data['Name']))->find();
            }
        }
        return true;
    }

    //处理城市
    public function dealCity($data){
        //address_city_other
    }


    //1.传入国家或地点名，请求百度接口
    public function baidu1($name = ''){

        $get_data = array(
            'address'       => $name,
            'output'        => 'json',
            'ak'            => config('baidu_map_key'),
        );
        $url = 'http://api.map.baidu.com/geocoder/v2/';

        return json_decode(http_curl($url,$get_data),true);
    }
}