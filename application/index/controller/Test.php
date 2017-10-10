<?php
namespace app\index\controller;
use app\index\model as model;
use think\Request;

use \Yunpian\Sdk\YunpianClient;

class Test extends \app\common\controller\Base
{

    public function code(){


        $vcode = new \transfer\Vcode();
        $result = $vcode->send( $this->param('phone',18380335706),$this->param('pre','+86'),$this->param('code_type',103));
        debug($result);

    }

    public function t(){
        $v_code = '118060';
        $vcode = new \transfer\Vcode();
        $result_vcode = $vcode->contrast( $this->param('phone',18380335706),$this->param('pre','+86'),103,$this->param('vcode',$v_code));
        debug($result_vcode);
    }

    //获取全部的value和key
    protected function allKeyValue($data){
        $key = [
            't_code'    => [],
            'c_code'    => [],
            'p_code'    => [],
            's_code'    => [],
            'city'      => [],
        ];
        foreach ($data as $k1 => $v1){
            foreach ($key as $k => $v){
                if(isset($v1[$k])){
                    $key[$k][] = $v1[$k];
                }
            }
        }
        return $key;
    }

    //拼接云片短信的模板内容 //'#code#=123456'
    protected function formatContentYunpian($content){
        if(!$content){
            return '';
        }
        $temp = [];
        foreach ($content as $k => $v){
            $temp[] = '#'.trim($k).'#='.$v;
        }
        return implode('&',$temp);
    }


    public function yunpian(){
        return $this->stop();
        $clnt  = YunpianClient::create('cb8276e5fc94ed9394e857723e50412d');
        $param = [
            YunpianClient::MOBILE    => '+855078896679',
            YunpianClient::TPL_ID    => '1973234',
            YunpianClient::TPL_VALUE => '#code#=123456'
        ];
        $res = $clnt->sms()->tpl_single_send($param);

        debug($res);

    }

    //第一步，设置市级城市的code
    public function city(){
        return $this->stop();
        $file = 'BaiduMap_cityCode.txt';
        $data = file_get_contents($file);
        $arr = explode('>>>',$data);
        $arr = array_map(function ($vo){
            return explode(' ',trim($vo));
        },$arr);

        foreach ($arr as $vo){
            if(isset($vo[1])&&$vo[1]){

                $db = model('address_city')->where('name','like','%'.$vo[1].'%')->find();
                if($db){

                    $db->city = $vo[0];
                    $db->save();
                }

            }
        }

        return success([]);
    }

    protected function rr($data){
        return $this->stop();
        $url = 'http://api.map.baidu.com/geocoder/v2/';
        $url .= '?'.http_build_query([
                'address'       => $data,
                'output'        => 'json',
                'ak'            => 'sNgblvoj7soi2Z4zgcUGfkapCXxh2zVD'
            ]);
        return http_curl($url);
    }

    public function aaa(){
        return $this->stop();
        //105.71953	29.683909	105.725955	29.69023
        $param = [
//            'lat'   => '29.683909',
//            'lng'   => '105.71953',
            'lat'   => '30.983909',
            'lng'   => '104.51953',
        ];
        $res = model('Geo','service')->sl($param)->gl();
        if($this->isError($res)){
            return error($res);
        }else{
            return success(current($res['data']));
        }

    }

    public function saveartplace(){
        return $this->stop();
        foreach (model('art_para')->where('para_type',103)->where('place_id','<>','0')->select() as $vo){
            $this->saveOne( $vo['main_id'], $vo['place_id']);
        }

        return success([]);
    }

    public function saveartplace2(){
        return $this->stop();
        foreach (model('line_item')->where('place_id','<>','0')->select() as $vo){
            $this->saveOne( $vo['main_id'], $vo['place_id']);
        }
        return success([]);
    }


    protected function saveOne($art_id,$place_id){
        return $this->stop();
        if($art_id && $place_id){
            $sql = 'insert ignore into `place_art_relation`(`art_id`,`place_id`,`del_time`) values ('.$art_id.','.$place_id.',0)';
            if(\Think\Db::execute($sql)){
                return true;
            }
        }
        return false;
    }


    public function conv(){
        return $this->stop();
        chunk(function ($vo){
            $vo = array_map(function ($v){
                $lat = $v['lat'];
                $lng = $v['lng'];
                if($lat && $lng){
                    $res = bd09togcj02($lng, $lat);

                    $temp['bd_lat'] = $lat;
                    $temp['bd_lng'] = $lng;
                    $temp['lat'] = $res[1];
                    $temp['lng'] = $res[0];

                    model('place')->where('id',$v['id'])->update($temp);
                }
                return $v;
            },$vo);


//            $res = model('place')->saveAll($vo);
            imma('
                go;
            ');
            return '';
        },model('place')->select(),100,1);
//        return success([]);
    }

    public function save(){
        return $this->stop();
//        $str = 'J7LbkTa/tPhI2025beX61fNdsBZa08m392E3LHI9N7IvpNIpFLQuBhoh5wNLC7ZqI6ot1HDBjC7PCzUgeG5jKXgv9kN4uyhrGqj+zS1mIMPWHrWNosta29dv64iq44JCArWyOxoq7nTSZId13n6XukzTOE5mGz9wqFxoM2ae8Uc=';
        $str = $this->param('str','');
        return $this->de($str);
    }

    public function de($str){
        return $this->stop();
        $res = json_decode(aesDecode($str),true);

        debug($res);

    }

    public function en(){
        return $this->stop();
        $data = [
            'action'        => 'login',
            'stu_no'        => '201721040119',
            'password'      => '186195',
            'schoolCode'    => '001',
            'system'        => 'android',
            'device_uuid'   => '03d476861afd384510f2cb80ccfa8511',
            'version'       => '3.9',
            'device_name'   => 'postman'
        ];

        $res = aesEncode(json_encode($data));

        echo  $res;

    }

    //更新用户的足迹信息
    public function fullFooterPrinter(){
        return $this->stop();
        //1.取出全部的足迹信息，依次尝试添加到用户的足迹去
        $data = model('art_para')->with('art')->where(function ($query){
            return $query->where('para_type','103')->whereOr('para_type','107');
        })->where('location_name','<>','')->where('location_value','<>','');

        chunk(function ($vo){
            foreach ($vo as $v){
                if($v['art']){
                    $res = model('UserFootprint','service')->add(isset($v['art']['user_id'])?$v['art']['user_id']:'0',$v);
                    debug($res);
                    imma(' -- add --');
                }
            }
        },$data->select(),100);

        return success([]);
    }


    //补完位置段落的数据
    public function fullPara(){
        return $this->stop();

        $data = model('art_para')->where(function ($query){
            return $query->where('para_type','103')->whereOr('para_type','107');
        })->where('location_name','<>','')->where('t_code','')->where('c_code','')->where('p_code','')->where('s_code','')->where('city','')->select();

        chunk(function ($vo){
            $geo = model('geo','service');
            foreach ($vo as $v){
                $geo->sl([
                    'location_name' => $v['location_name'],
                    'location_value'=> $v['location_value']
                ]);
            }
            $res = $geo->gl();
            if(!$this->isError($res)){
                $res = $res['data'];
                $vo = array_map(function ($v)use($res){
                    foreach ($res as $item){
                        if(($v['location_name']  == $item['location_name'])&&($v['location_value'] == $item['location_value'])){
                            $v['location_name'] = isset($item['location_name'])?$item['location_name']:'';
                            $v['location_value'] = isset($item['location_value'])?$item['location_value']:'';
                            $v['t_code'] = isset($item['t_code'])?$item['t_code']:'';
                            $v['c_code'] = isset($item['c_code'])?$item['c_code']:'';
                            $v['p_code'] = isset($item['p_code'])?$item['p_code']:'';
                            $v['s_code'] = isset($item['s_code'])?$item['s_code']:'';
                            $v['city']   = isset($item['city'])?$item['city']:'';
                            break;
                        }
                    }
                    return $v;
                },$vo);
                if($vo){
                    foreach ($vo as $i){
                        imma(' -- update -- ');
                        debug($i);
                        debug($i->where('id',$i['id'])->data($i->toArray())->update());
                    }
                }
            }
        },$data,30,2);
        return success(['desc'=>'结束']);
    }

    //
    public function getUser(){
        return $this->stop();
        $data = model('User','service')->fullModel(1,0);
        return success($data);
    }

    //同步本地和线上的云信token
    public function u(){
        return $this->stop();
        foreach ($this->allUser() as $vo){
            $data = model('user','service')->fullModel($vo['id'],0);

            debug($data,'$data');

            $this->syncToken($data);

        }
        return success([]);
    }

    //更新线上的token,保持和本地一致
    protected function syncToken($data){
        return $this->stop();
        if($data && isset($data['uim_user']) && $data['uim_user']['token']){

            return model('netease','service')->request('update',['accid'=>$data['id'],'token'=>$data['uim_user']['token']]);
        }else{
            return false;
        }
    }

    //1.遍历本地用户，去查询云信服务器有没有这些用户，然后没有的，统统创建
    public function flushUim($id){
        return $this->stop();
        return model('user','service')->fullModel($id,0);
    }

    public function allUser(){
        return $this->stop();
        return model('User')->select();
    }

    //查询云信服务器是否有这些用户
    public function hasUser($id_arr){
        return $this->stop();
        //getUinfos
        $param = [
            'accids'    => json_encode($id_arr)
        ];
        $res = model('netease','service')->request('getUinfos',$param);
        debug($res);
        return $res;
    }



    //查询三网短信的发送历史
    public function getSmsHistory(){
        return $this->stop();
        $get = [
            'mobile'    => $this->param('phone',''),
        ];

        $host = "http://ali-sms.showapi.com";
        $path = "/hisRecord";
        $headers = ['Authorization:APPCODE ' .config('sms.code')];
        $url = $host.$path;
        $res = http_curl($url, $get, [], $headers);
        if($res){
            if($res['data']){
                $res_data = json_decode($res['data'],true);
                if($res_data){
                    debug($res_data);
                }
            }
        }
        return success([]);
    }

    //设置没有城市前缀的所有的地点的前缀
    public function locationsave(){
        return $this->stop();
        set_time_limit(1000);
        $temp = [];
        foreach (model('user_footprint')->select() as $v){
            $temp[] = [
                'id'            => $v['id'],
                'location_name' => $v['location_name'],
                'location_value'=> $v['location_value']
            ];
        }
        if($temp){
            $data = chunk(function($data){
                $temp = array_map(function($vo){
                    return [
                        'location_name'   => $vo['location_name'],
                        'location_value'  => $vo['location_value']
                    ];
                },$data);
                $lo = model('Geo','service')->sl($temp)->gl();
                imma('go');

                if(!$this->isError($lo)){
                    $save = [];
                    foreach ($data as $vo){
                        foreach ($lo['data'] as $v){
                            if($v['location_name'] == $vo['location_name'] && $v['location_value'] == $vo['location_value']){

                                $save[] = [
                                    'id'             => $vo['id'],
                                    'location_name'  => $v['location_name'],
                                    'location_value' => $v['location_value'],
                                    't_code'         => $v['t_code'],
                                    'c_code'         => $v['c_code'],
                                    'p_code'         => $v['p_code'],
                                    's_code'         => $v['s_code'],
                                    'city'           => $v['city'],
                                ];
                            }
                        }
                    }
                    debug($save,'$save');
                    if($save){
                        model('user_footprint')->saveAll($save);
                    }else{
                        debug($lo,'$lo');
                    }
                }
                return $lo['data'];
            },$temp,50,5);
            debug($data);
        }
        return success([]);
    }

//
//    public function read(){
//
//        $model = model('Geo','service');
//
//        $lat = $this->param('lat');
//        $lng = $this->param('lng');
//
//        $model->sl([$lat,$lng]);
//
//        debug($model->gl());
//
//    }

    //创建短信模板
    public function newsms(){
        return $this->stop();
        //这个是
        $get = [
            'content'   => '尊敬的用户[user_name]您好,您订购的套餐[goods_name]已经可以预定了，将为您保留30分钟,请继续完成付款。',
            'notiPhone' => '18380335706',
            'title'     => '自由GO'
        ];

        $host = "http://ali-sms.showapi.com";
        $path = "/createTemplate";
        $headers = ['Authorization:APPCODE ' .config('sms.code')];
        $url = $host.$path;
        return success(http_curl($url, $get, [], $headers));


    }

    public function commentnum(){
        return $this->stop();
        $data = model('comment')->field('count(*) num,type,main_id')->group('main_id,type')->select();
        foreach ($data as $k => $v){
            if($v->main_id){

                if(103 == $v->type){
                    $service =  model('art');
                    $key = 'comments_count';
                }elseif (104 ==  $v->type){
                    $service =  model('services');
                    $key = 'comments_count';
                }elseif (106 ==  $v->type){
                    $service =  model('place');
                    $key = 'comments_count';
                }elseif (111 ==  $v->type){
                    $service =  model('user');
                    $key = 'comment_passenger_count';
                }elseif (112 ==  $v->type){
                    $service =  model('user');
                    $key = 'comment_driver_count';
                }
                $service->where('id',$v->main_id)->update([$key=>$v['num']]);
            }

        }

        return success([]);

    }

    public function imgUpdate(){
        return $this->stop();
        //遍历图片，重新保存宽高
        $data = model('image')->where(function ($query){
            return $query->where(function ($query){
                return $query->where('width','')->where('height','');
            })->whereOr(function ($query){
                return $query->where('width','0')->where('height','0');
            });
        })->select();
        foreach ($data as $v){
            try{
                $image = @\think\Image::open($this->getPath($v->getData('url')));
                $v->width = $image->width();
                $v->height = $image->height();
                $v->save();
            }catch (\Exception $e){

                $v = '错误的文件: '.$this->getPath($v->getData('url')).' >>> ';
            }
            imma($v);
        }
        return success([]);
    }

    //获取图片路径
    public function getPath($p){
        return $this->stop();
        //截取掉后面的参数
        $full = ROOT_PATH.'public/uploads/'.$p;
        $strlen=strlen($full);
        $tp=strpos($full,"?");
        if($tp){
            $str=substr($full,-$strlen,$tp);
        }else{
            $str = $full;
        }

        return strtr($str,'/',DS);
    }


    //添加论坛
    public function addForum(){
        return $this->stop();
        //1.取出省 添加到对应的论坛
        $p = model('addressCity')->with(['father'])->select();
        $add_data = [];
        $c_time = floor(THINK_START_TIME);
        foreach ($p as $k => $v){

            $add_data[] = [
                'title'         => $v['name'].'分会论坛',
                'c_time'        => $c_time,
                'p_id'          => $v['father']['id']+4,
                'is_forum'      => 101,
                'location_name' => 'c_code',
                'location_value'=> $v['c_code'],
                't_code'        => '',
                'c_code'        => $v['c_code'],
                'p_code'        => $v['p_code'],
                'city'          => '',
                's_code'        => 1

            ];
        }
        return success(model('forum')->saveAll($add_data));
    }


    public function artFootPrint(){
        return $this->stop();
        $max_id = 885;
        for($i = 1;$i <= $max_id;$i++){

            $line_data = model('artPara')->where('main_id',$i)->where('para_type','in',[103,107])->order('order_weight asc')->select();


            //加一步修改数据
            $city_num = 0;
            $place_num = 0;

            foreach ($line_data as $k => $v){
                if(103 == $v['para_type']){
                    $place_num += 1;
                }elseif (107 == $v['para_type']){
                    $city_num += 1;
                }
            }
            $footprint = $city_num.'个城市、'.$place_num.'个地点';
            model('art')->where('id',$i)->update(['footprint'=>$footprint]);

        }
        return success([]);
    }

    //车型和车系数据
    public function carData(){
        return $this->stop();
        $data = model('tttt')->select();
        foreach ($data as $k => $v){
            $t = model('car_type')->where('title',trim($v['B']))->where('car_brand_id',trim($v['A']))->find();
            if(!$t){
                $t = model('car_type');
                $t->data([
                    'title'         => trim($v['B']),
                    'car_brand_id'  => trim($v['A'])
                ])->isUpdate(false)->save();
            }
            if(!$t){
                debug($v,'save_type_error');
            }else{
                model('car_model')->data([
                    'car_type_id'       => $t->id,
                    'title'             => trim($v['C'])
                ])->isUpdate(false)->save();
            }
        }
        return success([]);
    }


    protected function temp($art_id,$line_id){
        return $this->stop();
        if($art_id && $line_id){
            foreach (model('line_item')->where('line_id',$line_id)->where('location_name','<>','')->where('location_value','<>','')->select() as $t){
                model('art','service')->saveLocation($art_id,$t['location_name'],$t['location_value']);
            }
        }
        imma('完成1个');
    }

    //更新路线的位置信息
    protected function lineLocation(){
        return $this->stop();
        foreach (model('lineItem')->where('lat','<>','0')->where('lng','<>','0')->select() as $data){
            $lo = new \app\common\service\Location();
            $lo_info = $lo->read($data['lat'],$data['lng'],false);
            if($lo_info){
                $data['location_name']  = $lo_info['location_name'];
                $data['location_value'] = $lo_info['location_value'];
                $data['t_code']  = $lo_info['t_code'];
                $data['c_code'] = $lo_info['c_code'];
                $data['p_code']  = $lo_info['p_code'];
                $data['s_code'] = $lo_info['s_code'];
                $data['city']  = $lo_info['city'];
                $data->save();
                imma('完成1个');
            }
        }
        return '结束';
    }

    //更新地点的位置信息
    protected function placeLocation(){
        return $this->stop();
        foreach (model('place')->where('lat','<>','0')->where('lng','<>','0')->select() as $data){
            $lo = new \app\common\service\Location();
            $lo_info = $lo->read($data['lat'],$data['lng'],false);
            if($lo_info){
                $data['location_name']  = $lo_info['location_name'];
                $data['location_value'] = $lo_info['location_value'];
                $data['t_code']  = $lo_info['t_code'];
                $data['c_code'] = $lo_info['c_code'];
                $data['p_code']  = $lo_info['p_code'];
                $data['s_code'] = $lo_info['s_code'];
                $data['city']  = $lo_info['city'];
                $data->save();
                imma('完成1个');
            }
        }
        return '结束';
    }

    protected function stop(){
        return '拒绝访问';
    }

}