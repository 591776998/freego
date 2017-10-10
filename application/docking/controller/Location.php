<?php
namespace app\docking\controller;
class Location extends \app\common\controller\DockingBase
{
    //输入经纬度，返回对应的国家的code
    public function index()
    {
        $lat = $this->param('lat',0);
        $lng = $this->param('lng',0);
        $type = $this->param('type','gcj02ll');

        $address = new \app\index\service\Address();
        $res = $address->getLocationV2($lat,$lng,$type);
        if($this->isError($res)){
            return error($res);
        }
        if(!$res['data']['location_value']){
            return error(['desc'=>'错误的经纬度信息']);
        }
        return success($res);
    }

    //进行地理编码
    public function geo(){
        $key_word = $this->param('key_word','');

        $get_data = array(
            'address'       => $key_word,
            'output'        => 'json',
            'ak'            => config('baidu_map_key')
        );
        $url = 'http://api.map.baidu.com/geocoder/v2/';
        $result = http_curl($url,$get_data);
        $result_data = json_decode($result,true);
        if(isset($result_data['status']) && 0 === $result_data['status']) {
            return success($result_data['result']);
        }else{
            return error(['desc'=>'解析失败']);
        }
    }

    public function save(){
        $c_name = $this->param('c_name','');
        $city_name = $this->param('city_name','');
        $lat = $this->param('lat',0);
        $lng = $this->param('lng',0);
        $coordtype = $this->param('coordtype','gcj02ll');

        $get_data = array(
            'coordtype'     => $coordtype,
            'location'      => ($lat?$lat:'30.541154').','.($lng?$lng:'104.075276'),
            'output'        => 'json',
            'pois'          => '0',
            'ak'            => 'sNgblvoj7soi2Z4zgcUGfkapCXxh2zVD'
        );
        $url = 'http://api.map.baidu.com/geocoder/v2/';
        $result = http_curl($url,$get_data);
        $result_data = json_decode($result,true);
        if(isset($result_data['status']) && 0 === $result_data['status']){

            $s_code = isset($result_data['result']['addressComponent']['country_code'])?$result_data['result']['addressComponent']['country_code']:'';
//            $s_name = isset($result_data['result']['addressComponent']['country'])?$result_data['result']['addressComponent']['country']:'';

            $city = isset($result_data['result']['cityCode'])?$result_data['result']['cityCode']:'';
            $city_name1 = isset($result_data['result']['addressComponent']['city'])?$result_data['result']['addressComponent']['city']:'';
            $province_name = isset($result_data['result']['addressComponent']['province'])?$result_data['result']['addressComponent']['province']:'';

            if($s_code) {
                $this->addCountry([
                    's_code' => $s_code,
                    'name'   => $c_name
                ]);
            }
            if($city){
                $this->addCity([
                    'city'      => $city,
                    'name'      => $city_name1,
                    'province'  => $city_name,
                    's_code'    => $s_code,
                ]);
            }
        }
        return success([]);
    }

    //保存不存在的城市到数据库
    protected function addCity($data){
        if($data['city'] && !model('addressCityOther')->where('city',$data['city'])->find()){
            model('addressCityOther')->data([
                's_code'    => $data['s_code'],
                'city'      => $data['city'],
                'name'      => trim(strtolower($data['name'])),
                'main_name' => trim(strtolower($data['province'])),
                'del_time'  => '0',
                'img_id'    => '0',
            ])->isUpdate(false)->save();
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
}