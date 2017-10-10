<?php
namespace app\common\model;
use app\common\model;


class Address extends model\Base
{

    //返回第一级的数组
    public function country($type = 101,$name = '',$value = ''){
        $data = array_map(function ($v){
            return [
                'code_name'     => 'i_code',
                'code_value'    => $v['i_code'],
                'name'          => $v['name'],
                'img_url'       => isset($v['img_url'])?$v['img_url']:'',
            ];
        },model('AddressI')->select());
        array_unshift($data,['code_name'=>'s_code','code_value'=>'1','name'=>'中国','img_url'=>'']);
        if(101 == $type){
            array_unshift($data,['code_name'=>'order','code_value'=>'is_hot','name'=>'热门','img_url'=>'']);
        }elseif(102 == $type){
            //102表示把某地点放到第一个
            if($name && $value){
                $location = $this->getLocation($name,$value);
                if($location){
                    array_unshift($data,['code_name'=>$name,'code_value'=>$value,'name'=>$location['name'],'img_url'=>$location['img_url']]);
                }
            }
        }else{

        }
        return $data;
    }

    //获取某地点
    public function getLocation($name = '',$value = ''){
        switch ($name){
            case 'i_code':
                return model('AddressI')->with('img')->where($name,$value)->find();
            break;
            case 's_code':
                return model('AddressCountry')->with('img')->where($name,$value)->find();
                break;
            case 'p_code':
                return model('AddressProvince')->with('img')->where($name,$value)->find();
                break;
            case 'c_code':
                return model('AddressCity')->with('img')->where($name,$value)->find();
                break;
            case 't_code':
                return model('AddressTown')->with('img')->where($name,$value)->find();
                break;
            case 'city':
                return model("AddressCityOther")->with('img')->where('city',$value)->find();
                break;
        }
        return [];
    }
}