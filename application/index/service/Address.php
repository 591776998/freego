<?php
namespace app\index\service;
use think\Db;
class Address extends \app\common\service\Base
{
    protected $default_img_t = '';
    protected $default_img_c = '';
    protected $default_img_p = '';
    protected $default_img_s = '';
    protected $default_img_i = '';

    //搜索
    public function search($key_word, $page, $page_size){
        $key_word = trim('\'',$key_word);
        $address_table = [
            'c_code'  => 'address_city',
            'p_code'  => 'address_province',
            's_code'  => 'address_country',
            'i_code'  => 'address_i',
        ];
        $model = model('addressTown')->field("id,'t_code' as code_name,`t_code` as code_value,name,img_id")->where('name','like','%'.$key_word.'%');
        foreach ($address_table as $k => $v){
            $model->union(model($v)->field("id,'$k' as code_name,`$k` as code_value,name,img_id")->where('name','like','%'.$key_word.'%')->select(false));
//            $model->union( "SELECT id,'$k' as code_name,`$k` as code_value,name,img_id FROM $v WHERE `name` like '%$key_word%'");
        }
        $data = Db::query("SELECT id,code_name,code_value,`name`,img_id FROM ".$model->buildSql()." as `temp_table` LIMIT ".$this->getLimit($page,$page_size));
        if($data){
            $img_arr = model('Image','service')->getUrls(array_column($data,'img_id'));
            foreach ($data as $k => $v){
                foreach ($img_arr as $key => $val){
                    if($v['img_id'] == $val['id']){
                        $data[$k]['img_url'] = $val['url'];
                        $data[$k]['img'] = $val;
                    }
                }
                if(!isset($data[$k]['img'])){
                    $data[$k]['img_url'] = '';
                    $data[$k]['img'] = (object)null;
                }
            }
        }
        return $data;
    }

    protected function getLimit($page, $page_size){
        return (($page-1)*$page_size).','.$page_size;
    }

    public function index($type = 101,$code_name = 'all',$code_value = '',$location_name = '', $location_value = '', $depth = 3){
        //type=101精彩影像
        return $this->success($this->getSub($code_name,$code_value,$depth,$type));
    }

    //
    public function info( $code_name = '', $code_value = ''){
        switch ($code_name){
            case 'i_code':
            case 's_code':
            case 'p_code':
            case 'c_code':
            case 't_code':
                $data = model('Address')->getLocation( $code_name, $code_value);
                if($data){
                    return [
                        'location_name'     => $code_name,
                        'location_value'    => $code_value,
                        'name'              => isset($data['name'])?$data['name']:'',
                        'img_url'           => isset($data['img_url'])?$data['img_url']:'',

                    ];
                }
                break;
        }
        //这里返回一个默认地点
        $code_name  = 'c_code';
        $code_value = '520100';
        $data = model('Address')->getLocation( $code_name, $code_value);
        if($data){
            return [
                'location_name'     => $code_name,
                'location_value'    => $code_value,
                'name'              => isset($data['name'])?$data['name']:'',
                'img_url'           => isset($data['img_url'])?$data['img_url']:'',
            ];
        }
        return [];
    }

    // 返回地点全名
    public function getFullName($code_name = '', $code_value = ''){
        if($code_name && $code_value){
            switch ($code_name){
                case 'i_code':
                    $data = model('Address')->getLocation($code_name, $code_value );
                    if($data){
                        return $data['name'];
                    }
                    break;
                case 's_code':
                    $data = model('AddressCountry')->where($code_name,$code_value)->find();
                    if($data){
                        return $data['name'];
                    }
                    break;
                case 'p_code':
                    $data = model('AddressProvince')
                        ->alias('p')
                        ->field('CONCAT(s.name,p.name) as name')
                        ->join('address_country s','p.s_code = s.s_code','LEFT')
                        ->where('p.'.$code_name,$code_value)
                        ->find();
                    if($data){
                        return $data['name'];
                    }
                    break;
                case 'c_code':
                    $data = model('AddressCity')
                        ->alias('c')
                        ->field('CONCAT(s.name,p.name,c.name) as name')
                        ->join('address_province p','p.p_code = c.p_code','LEFT')
                        ->join('address_country s','p.s_code = s.s_code','LEFT')
                        ->where('c.'.$code_name,$code_value)
                        ->find();
                    if($data){
                        return $data['name'];
                    }
                    break;
                case 't_code':
                    $data = model('AddressTown')
                        ->alias('t')
                        ->field('CONCAT(s.name,p.name,c.name,t.name) as name')
                        ->join('address_city c','c.c_code = t.c_code','LEFT')
                        ->join('address_province p','p.p_code = c.p_code','LEFT')
                        ->join('address_country s','p.s_code = s.s_code','LEFT')
                        ->where('t.'.$code_name,$code_value)
                        ->find();
                    if($data){
                        return $data['name'];
                    }
                    break;
                case 'city':
                    $data = model('AddressCityOther')
                        ->alias('co')
                        ->field('CONCAT(s.name,co.name) as name')
                        ->join('address_country s','co.s_code = s.s_code','LEFT')
                        ->where('co.city',$code_value)
                        ->find();
                    if($data){
                        return $data['name'];
                    }
                    break;
                default:
                    return '';
            }
        }
        return '';
    }

    //传入location返回所有的父级
    public function getFather($code_name,$code_value){
        if($code_name && $code_value){
            switch ($code_name){
                case 'c_code':
                    return model('AddressCity')->with(['father'=>function($query){
                        return $query->with(['father']);
                    }])
                        ->where($code_name,$code_value)
                        ->find();
                    break;
                case 't_code':
                    return model('AddressTown')->with(['father'=>function($query){
                        return $query->with(['father'=>function($query){
                            return $query->with(['father']);
                        }]);
                    }])
                        ->where($code_name,$code_value)
                        ->find();
                    break;
                case 'city':
                    return model('AddressCityOther')->with(['father'])
                        ->where($code_name,$code_value)
                        ->find();
                    break;
                default:
                    return [];
            }
        }
        return [];
    }

    //第二版反地理编码
    public function getLocationV2($lat,$lng,$type = 'gcj02ll'){
        //1.使用反地理编码接口，获取位置信息
        $location = $this->getLocation($lat,$lng,$type);
        if($this->isError($location)){
            return $location;
        }

        //1.从本地获取更多信息
        $location_name  = $location['data']['location_name'];
        $location_value = $location['data']['location_value'];
        $all = [];
        if('t_code' == $location_name){
            $all = model('address_town')
                ->alias('t')
                ->field(['t.t_code'=>'t_code','c.c_code'=>'c_code','p.p_code'=>'p_code','s.s_code'=>'s_code','t.name'=>'t_name','c.name'=>'c_name','p.name'=>'p_name','s.name'=>'s_name'])
                ->join('address_city c','c.c_code = t.c_code')
                ->join('address_province p','p.p_code = c.p_code')
                ->join('address_country s','s.s_code = p.s_code')
                ->where('t.t_code',$location_value)
                ->find();
        }elseif ('city' == $location_name){
            $all = model('address_city_other')
                ->alias('o')
                ->field(['s.s_code'=>'s_code','s.name'=>'s_name'])
                ->join('address_country s','s.s_code = o.s_code')
                ->where('o.city',$location_value)
                ->find();
        }
        if($all)$all->toArray();
        $all['title']           = $location['data']['title'];
        $all['city_title']      = $location['data']['city_title'];
        $all['full_title']      = $location['data']['full_title'];
        $all['desc_title']      = $location['data']['desc_title'];

        $all['location_name']   = $location_name;
        $all['location_value']  = $location_value;
        return $this->success($all);
    }

    //根据经纬度获取location_name 和 code ，反地理编码
    public function getLocation($lat,$lng,$type = 'gcj02ll'){
        $coordtype = $type;
        if(!$lat &&!$lng){
            return $this->error(['desc'=>'未获取到经纬度！']);
        }
        $get_data = array(
            'coordtype'     => $coordtype,
            'location'      => ($lat?$lat:'30.541154').','.($lng?$lng:'104.075276'),
            'output'        => 'json',
            'pois'          => '0',
            'ak'            => config('baidu_map_key')
        );
        $url = 'http://api.map.baidu.com/geocoder/v2/';
        $result = http_curl($url,$get_data);
        $result_data = json_decode($result,true);

        if(isset($result_data['status']) && 0 === $result_data['status']){
            if(0 == $result_data['result']['addressComponent']['country_code']){
                $address_desc = '';
                $address_desc .= isset($result_data['result']['addressComponent']['country'])?$result_data['result']['addressComponent']['country']:'';
                $address_desc .= isset($result_data['result']['addressComponent']['city'])?$result_data['result']['addressComponent']['city']:'';
                $address_desc .= isset($result_data['result']['sematic_description'])?$result_data['result']['sematic_description']:'';
                $full_title = (isset($result_data['result']['addressComponent']['city'])?$result_data['result']['addressComponent']['city']:'').(isset($result_data['result']['addressComponent']['district'])?$result_data['result']['addressComponent']['district']:'').(isset($result_data['result']['business'])?$result_data['result']['business']:'');
                $data = [
                    'location_name'     => 't_code',
                    'location_value'    => isset($result_data['result']['addressComponent']['adcode'])?$result_data['result']['addressComponent']['adcode']:'',
                    'title'             => isset($result_data['result']['business'])?$result_data['result']['business']:'',
                    'full_title'        => $full_title,
                    'desc_title'        => $address_desc,
                    'city_title'        => isset($result_data['result']['addressComponent']['city'])?$result_data['result']['addressComponent']['city']:'',
                ];
            }else{
                //country_code
                if((-1 == $result_data['result']['addressComponent']['country_code']) && (0 == $result_data['result']['cityCode'])){
                    return $this->error(['desc'=>'暂未收录该地点']);
                }
                $s_code = isset($result_data['result']['addressComponent']['country_code'])?$result_data['result']['addressComponent']['country_code']:'';
                $s_name = isset($result_data['result']['addressComponent']['country'])?$result_data['result']['addressComponent']['country']:'';
                $city = isset($result_data['result']['cityCode'])?$result_data['result']['cityCode']:'';
                $city_name = isset($result_data['result']['addressComponent']['city'])?$result_data['result']['addressComponent']['city']:'';
                $province_name = isset($result_data['result']['addressComponent']['province'])?$result_data['result']['addressComponent']['province']:'';
                //判断该城市和国家是否存在，不存在就入库，
                if($s_code) {
                    $this->addCountry([
                        's_code' => $s_code,
                        'name'   => $s_name
                    ]);
                }
                if($city){
                    $this->addCity([
                        'city'      => $city,
                        'name'      => $city_name,
                        'province'  => $province_name,
                        's_code'    => $s_code,
                    ]);
                }
                $formatted_address = (isset($result_data['result']['formatted_address'])?$result_data['result']['formatted_address']:'');
                $data = [
                    'location_name'     => 'city',
                    'location_value'    => $city,
                    'title'             => $city_name,
                    'full_title'        => $formatted_address,
                    'desc_title'        => $formatted_address,
                    'city_title'        => isset($result_data['result']['addressComponent']['city'])?$result_data['result']['addressComponent']['city']:'',
                ];
            }
            return $this->success($data);
        }else{
            return $this->error(['desc'=>'获取定位信息失败']);
        }
    }

    //批量解析
    public function getLocationArr($arr,$coordtype = 'gcj02ll'){
        if(!$arr){
            return $this->success([]);
        }
        $url = 'http://api.map.baidu.com/geocoder/v2/';
        $arr = array_map(function($a)use($url,$coordtype){
            return [
                'url'   =>$url.'?'.http_build_query([
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
                $temp = [];
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
                            if(isset($a['title']) && $a['title']){
                                $temp['title']          = $a['title'];
                            }else{
                                $temp['title']          = $res['result']['formatted_address'];
                            }

                            $s_code = isset($res['result']['addressComponent']['country_code'])?$res['result']['addressComponent']['country_code']:'';
                            $s_name = isset($res['result']['addressComponent']['country'])?$res['result']['addressComponent']['country']:'';
                            $city = isset($res['result']['cityCode'])?$res['result']['cityCode']:'';
                            $city_name = isset($res['result']['addressComponent']['city'])?$res['result']['addressComponent']['city']:'';
                            $province_name = isset($res['result']['addressComponent']['province'])?$res['result']['addressComponent']['province']:'';
                            if($s_code) {
                                $this->addCountry([
                                    's_code' => $s_code,
                                    'name'   => $s_name
                                ]);
                            }
                            if($city){
                                $this->addCity([
                                    'city'      => $city,
                                    'name'      => $city_name,
                                    'province'  => $province_name,
                                    's_code'    => $s_code,
                                ]);
                            }
                            $f = model('location','service')->read($temp['location_name'], $temp['location_value']);
                            $temp['t_code']     = $f['t_code'];
                            $temp['c_code']     = $f['c_code'];
                            $temp['p_code']     = $f['p_code'];
                            $temp['s_code']     = $f['s_code'];
                            $temp['city']       = $f['city'];
                            $temp['lng']        = $a['lng'];
                            $temp['lat']        = $a['lat'];
                            $temp['city_title'] = $city_name;
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

    /******************************  私有 *******************************************/
    //传入type和code获取下级全部的数组
    public function getSub($type,$code,$depth,$t = 101){
        if(1 > $depth){
            return [];
        }
        $need_cache = config('cache_address_data');
        if($need_cache){
            $cache = model('Cache','service');
            $key = 'address_'.$type.'_'.$code.'_'.$depth;
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
                    //市级
                    foreach (model('AddressTown')->with('img')->where('c_code',$code)->select() as $v){
                        $data[] = [
                            'code_name' => 't_code',
                            'code_value'=> $v['t_code'],
                            'name'      => $v['name'],
                            'img_url'   => isset($v['img_url'])?$v['img_url']:$this->default_img_t,
                            'sub'       => []
                        ];
                    }
                    break;
                case 'p_code':
                    //省级
                    foreach (model('AddressCity')->with('img')->where('p_code',$code)->select() as $v){
                        $data[] = [
                            'code_name' => 'c_code',
                            'code_value'=> $v['c_code'],
                            'name'      => $v['name'],
                            'img_url'   => isset($v['img_url'])?$v['img_url']:$this->default_img_c,
                            'sub'       => ((1 < $depth)?$this->getSub('c_code',$v['c_code'],$depth - 1):[])
                        ];
                    }
                    break;
                case 's_code':
                    //国家
                    if(1 == $code){

                        foreach (model('AddressProvince')->with('img')->where('s_code',$code)->select() as $v){
                            $data[] = [
                                'code_name' => 'p_code',
                                'code_value'=> $v['p_code'],
                                'name'      => $v['name'],
                                'img_url'   => isset($v['img_url'])?$v['img_url']:$this->default_img_p,
                                'sub'       => ((1 < $depth)?$this->getSub('p_code',$v['p_code'],$depth - 1):[])
                            ];
                        }
                    }else{

                        foreach (model('AddressCityOther')->with('img')->where('s_code',$code)->select() as $v){
                            $data[] = [
                                'code_name' => 'city',
                                'code_value'=> $v['city'],
                                'name'      => $v['main_name'],
                                'img_url'   => isset($v['img_url'])?$v['img_url']:$this->default_img_p,
                                'sub'       => [],
                            ];
                        }
                    }
                    break;
                case 'i_code':
                    //大洲
                    foreach (model('AddressCountry')->with('img')->where('i_code',$code)->select() as $v){
                        $data[] = [
                            'code_name' => 's_code',
                            'code_value'=> $v['s_code'],
                            'name'      => $v['name'],
                            'img_url'   => isset($v['img_url'])?$v['img_url']:$this->default_img_s,
                            'sub'       => ((1 < $depth)?$this->getSub('s_code',$v['s_code'],$depth - 1):[])
                        ];
                    }
                    break;
                case 'city':
                    foreach (model('AddressCityOther')->with('img')->where('city',$code)->select() as $v){
                        $data[] = [
                            'code_name' => 'city',
                            'code_value'=> $v['city'],
                            'name'      => $v['name'],
                            'img_url'   => isset($v['img_url'])?$v['img_url']:$this->default_img_s,
                            'sub'       => []//((2 < $depth)?$this->getSub('city',$v['city'],$depth - 1):[])
                        ];
                    }
                    break;
                case 'all':
                    $data = array_map(function ($v)use($depth){
                        $v['sub'] = $this->getSub($v['code_name'],$v['code_value'],$depth-1);
                        return $v;
                    },model('address')->country($t,$type,$code));
                    break;
                case 'order':
                    if('is_hot' == $code){
                        $data = array_map(function ($vo)use($depth){
                            $res = model('address')->getLocation($vo['location_name'],$vo['location_value']);
                            return [
                                'code_name' => $vo['location_name'],
                                'code_value'=> isset($res[$vo['location_name']])?$res[$vo['location_name']]:'',
                                'name'      => $vo['name'],
                                'img_url'   => isset($v['img_url'])?$v['img_url']:$this->default_img_s,
                                'sub'       => $this->getSub($vo['location_name'],$vo['location_value'],$depth-1)
                            ];
                        },model('addressHot')->select());
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

    /********************** 路线二级 ***************************/
    //输入line_item的数据 格式化路线数据为可直接存入linemap的数组
    public function formatLineNav($data){
        //1.取出全部的经纬度
        if($data){
            $tCode2cCode = $this->getCodeRelation(array_map(function($item){return $item['location_value'];},$data));
            $top = $this->getTopCity($data, $tCode2cCode);
            $result = [];
            $temp = [];
            foreach ($data as $k => $v){
                $top_code = isset($tCode2cCode[$v['location_value']])?$tCode2cCode[$v['location_value']]:'';
                $top_city = $this->theTopCity($top_code,$top);
                if($top_city){
                    //上级有城市
                    if($temp){
                        //当前临时城市存在
                        if($this->eqCity($temp,$top_city)){
                            $temp['sub'][] = $v;
                        }else{
                            array_push($result,$temp);
                            $temp = $top_city;
                            $temp['sub'][] = $v;
                        }
                    }else{
                        //临时城市不存在
                        $temp = $top_city;
                        $temp['sub'][] = $v;
                    }
                }else{
                    if($temp){
                        //当前临时城市存在
                        array_push($result,$temp);
                    }
                    //上级无城市,把当前的下级城市当成上级城市处理，这是bug
                    $v['sub'] = [];
                    $temp = $v;
                }
            }
            if($temp){
                array_push($result,$temp);
            }
            return $this->success($result);
        }else{
            return $this->success([]);
        }
    }

    //格式化为段落的二级数组
    public function formatParaNav($data = []){
        if($data){
            $tCode2cCode = $this->getCodeRelation(array_map(function($item){return $item['location_value'];},$data));
            $top = $this->getTopCity($data, $tCode2cCode);
            $result = [];
            $temp = [];
            foreach ($data as $k => $v){
                $v = $this->paraToNav($v);
                $top_code = isset($tCode2cCode[$v['location_value']])?$tCode2cCode[$v['location_value']]:'';
                $top_city = $this->theTopCity($top_code,$top);
                if($top_city){
                    //上级有城市
                    if($temp){
                        //当前临时城市存在
                        if($this->eqCity($temp,$top_city)){
                            $temp['sub'][] = $v;
                        }else{
                            array_push($result,$temp);
                            $temp = $top_city;
                            $temp['sub'][] = $v;
                        }
                    }else{
                        //临时城市不存在
                        $temp = $top_city;
                        $temp['sub'][] = $v;
                    }
                }else{
                    if($temp){
                        //当前临时城市存在
                        array_push($result,$temp);
                    }
                    //上级无城市,把当前的下级城市当成上级城市处理，这是bug
                    $v['sub'] = [];
                    $temp = $v;
                }
            }
            if($temp){
                array_push($result,$temp);
            }
            return $this->success($result);
        }else{
            return $this->success([]);
        }
    }

    //
    protected function paraToNav($data){
        $data['nav_type'] = 102;

        if( isset($data['address_title'])&& $data['address_title']){
            $data['title'] = $data['address_title'];
        }else{
            $data['title'] = isset($data['content'])?$data['content']:'';
        }

        $data['sub']      = [];
        $data['para_id']  = isset($data['id'])?$data['id']:'0';
        return $data;
    }

    //获取上下级城市 的code对应关系
    protected function getCodeRelation($t_code = []){
        return array_column(array_map(function($item){
            return $item->toArray();
        },model('addressTown')->field('t_code,c_code')->where('t_code','in',$t_code)->select()),'c_code','t_code');
    }

    //获取所有上1级城市
    public function getTopCity($bot = [], $tCode2cCode = []){
        $top = model('addressCity')->where('c_code','in',array_values($tCode2cCode))->select();
        if(in_array('city',array_column($bot,'location_name'))){
            //如果下级城市有国外的
            $top[] = [
                'name'              => '国外',
                'lng'               => '0',
                'lat'               => '0',
                'location_name'     => 'abroad',
                'c_code'            => 'abroad',
            ];
        }
        return array_map(function($item){
            $temp                   = [];
            $code_name              = isset($item['location_name'])?$item['location_name']:'c_code';
            $temp['nav_type']       = '101';
            $temp['title']          = isset($item['name'])?$item['name']:'';
            $temp['lng']            = isset($item['lng'])?$item['lng']:'';
            $temp['lat']            = isset($item['lat'])?$item['lat']:'';
            $temp['location_name']  = $code_name;
            $temp['location_value'] = isset($item[$code_name])?$item[$code_name]:'';
            $temp['sub']            = [];
            return $temp;
        },$top);
    }

    //传入top_code返回对应的上1级城市
    protected function theTopCity($t_code,$top){
        foreach ($top as $k => $v){
            if(isset($v['location_value']) && $t_code == $v['location_value']){
                return $v;
            }
        }
        return false;
    }

    //判断两个城市是否相等
    protected function eqCity($a,$b){
        //1.4个参数都一致
        if((isset($a['location_name']) && isset($b['location_name'])) && $a['location_name'] == $b['location_name']){
        }elseif ((!isset($a['location_name']) && !isset($b['location_name']))){
        }else{
            return false;
        }
        if((isset($a['location_value']) && isset($b['location_value'])) && $a['location_value'] == $b['location_value']){
        }elseif ((!isset($a['location_value']) && !isset($b['location_value']))){
        }else{
            return false;
        }
        return true;
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