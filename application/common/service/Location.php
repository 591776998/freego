<?php
namespace app\common\service;
class Location extends \app\common\service\Base
{
    //位置服务层，
    //与location相关的 1.文章，2.地点，3.论坛，4.经纬度，5.用户，6.协会，7.

    //1.输入location,返回父级location信息
    public function read($code_name = '', $code_value = '',$is_location = true){
        if(!$is_location){
            //输入的是经纬度
            $address_service = new \app\index\service\Address();
            $temp = $address_service->getLocation($code_name,$code_value);
            if($this->isError($temp)){
                return [];
            }
            $code_name = $temp['data']['location_name'];
            $code_value = $temp['data']['location_value'];
        }
        $code_name  = trim($code_name);
        $code_value = trim($code_value);
        $res = [
            'location_name'     => $code_name,
            'location_value'    => $code_value,

            't_code'            => '',
            'c_code'            => '',
            'p_code'            => '',
            's_code'            => '',

            'city'              => '',
            'location_str'      => '',
            'city_title'        => '',
        ];
        switch ($code_name){
            case 'p_code':
                $data = model('AddressProvince')->with(['father'])
                    ->where($code_name,$code_value)
                    ->find();
                if($data){
                    $res['p_code']      = $data['p_code'];
                    $res['s_code']      = $data['s_code'];
                    $res['location_str']= $data['name'];
                    if(isset($data['father']['name'])){
                        $res['city_title'] = $data['father']['name'].$res['city_title'];
                    }
                }
                break;
            case 'c_code':
                $data = model('AddressCity')->with(['father'=>function($query){
                    return $query->with(['father']);
                }])
                    ->where($code_name,$code_value)
                    ->find();
                if($data){
                    $res['c_code']      = $data['c_code'];
                    $res['p_code']      = $data['p_code'];
                    $res['location_str']= $data['name'];
                    if(isset($data['father']['s_code'])){
                        $res['s_code']  = $data['father']['s_code'];
                    }

                    $res['city_title'] = $data['name'];
                    if(isset($data['father']['father']['name'])){
                        $res['city_title'] = $data['father']['father']['name'].$res['city_title'];
                    }
                }
                break;
            case 't_code':
                $data = model('AddressTown')->with(['father'=>function($query){
                    return $query->with(['father'=>function($query){
                        return $query->with(['father']);
                    }]);
                }])
                    ->where($code_name,$code_value)
                    ->find();
                if($data){
                    $res['t_code']      = $data['t_code'];
                    $res['c_code']      = $data['c_code'];
                    $res['location_str']= $data['name'];
                    if(isset($data['father']['p_code'])){
                        $res['p_code'] = $data['father']['p_code'];
                        if(isset($data['father']['father']['s_code'])){
                            $res['s_code'] = $data['father']['father']['s_code'];
                        }
                    }

                    if(isset($data['father']['name'])){
                        $res['city_title'] = $data['father']['name'];
                        if(isset($data['father']['father']['father']['name'])){
                            $res['city_title'] = $data['father']['father']['father']['name'].$res['city_title'];
                        }
                    }
                }
                break;
            case 'city':
                $data = model('AddressCityOther')->with(['father'])
                    ->where('city',$code_value)
                    ->find();
                if($data){
                    $res['city']        = $data['city'];
                    $res['s_code']      = $data['s_code'];
                    $res['location_str']= $data['name'];

                    $res['city_title'] = $data['name'];
                    if(isset($data['father']['name'])){
                        $res['city_title'] = $data['father']['name'].$res['city_title'];
                    }
                }
                break;
            case 's_code':
                $res['s_code']        = $code_value;
                break;
        }
        return $res;
    }

    //输入一个点，返回格式化好的信息，或者指定的信息
}