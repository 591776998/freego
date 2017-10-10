<?php
namespace app\docking\controller;
class Address extends \app\common\controller\DockingBase
{
    //返回地址列表
    public function index(){
        //1.接收3个参数 1，location_name 2.location_value 3.depth
        $location_name = $this->param('location_name','top');
        $location_value = $this->param('location_value','');
        $depth = $this->param('depth',1);

        //1.获取下级
        return success($this->getSub($location_name,$location_value,$depth));
    }

    //根据输入参数获取下级

    public function getSub($type,$code,$depth){
        if(1 > $depth){
            return [];
        }
        $need_cache = config('cache_address_data');
        if($need_cache){
            $cache = model('Cache','service');
            $key = 'address_docking_'.$type.'_'.$code.'_'.$depth;
            $data = $cache->file($key);
        }else{
            $data = [];
        }
        if(!$data){
            switch ($type){
                case 't_code':
                    //县级
                    $data =[];
                    break;
                case 'c_code':
                    //县区
                    foreach (model('AddressTown')->with('img')->where('c_code',$code)->select() as $v){
                        $data[] = [
                            'location_name' => 't_code',
                            'location_value'=> $v['t_code'],
                            'name'      => $v['name'],
                            'sub'       => []
                        ];
                    }
                    break;
                case 'p_code':
                    //城市
                    foreach (model('AddressCity')->with('img')->where('p_code',$code)->select() as $v){
                        $data[] = [
                            'location_name' => 'c_code',
                            'location_value'=> $v['c_code'],
                            'name'      => $v['name'],
                            'sub'       => ((1 < $depth)?$this->getSub('c_code',$v['c_code'],$depth - 1):[])
                        ];
                    }
                    break;
                case 's_code':
                    //省的列表
                    if(1 == $code){
                        //国内省的列表
                        foreach (model('AddressProvince')->with('img')->where('s_code',$code)->select() as $v){
                            $data[] = [
                                'location_name' => 'p_code',
                                'location_value'=> $v['p_code'],
                                'name'      => $v['name'],
                                'sub'       => ((1 < $depth)?$this->getSub('p_code',$v['p_code'],$depth - 1):[])
                            ];
                        }
                    }else{
                        //国外城市的列表
                        foreach (model('AddressCityOther')->with('img')->where('s_code',$code)->select() as $v){
                            $data[] = [
                                'location_name' => 'city',
                                'location_value'=> $v['city'],
                                'name'      => $v['main_name'],
                                'sub'       => [],
                            ];
                        }
                    }
                    break;
                case 'i_code':
                    //国家列表
                    foreach (model('AddressCountry')->with('img')->where('i_code',$code)->select() as $v){
                        $data[] = [
                            'location_name' => 's_code',
                            'location_value'=> $v['s_code'],
                            'name'      => $v['name'],
                            'sub'       => ((1 < $depth)?$this->getSub('s_code',$v['s_code'],$depth - 1):[])
                        ];
                    }
                    break;
                case 'city':
                    //国外城市列表
                    foreach (model('AddressCityOther')->with('img')->where('city',$code)->select() as $v){
                        $data[] = [
                            'location_name' => 'city',
                            'location_value'=> $v['city'],
                            'name'      => $v['name'],
                            'sub'       => []//((2 < $depth)?$this->getSub('city',$v['city'],$depth - 1):[])
                        ];
                    }
                    break;
                case 'top':
                    //大洲列表
                    foreach (model('addressI')->with('img')->select() as $v){
                        $data[] = [
                            'location_name' => 'i_code',
                            'location_value'=> $v['i_code'],
                            'name'      => $v['name'],
                            'sub'       => ((1 < $depth)?$this->getSub('i_code',$v['i_code'],$depth - 1):[])
                        ];
                    }

                    break;
                default:
                    //其他的
                    return [];
            }

            if($need_cache) {
                $cache->file($key, $data, 86400);
            }
        }
        return $data;
    }

}