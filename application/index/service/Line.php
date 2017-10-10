<?php
namespace app\index\service;
use think\Db;
class Line extends \app\common\service\Base
{

    //返回路线数据
    public function read( $id,$show_top = 102){
        $model = new \app\index\model\Line();
        if(101 == $show_top){
            $line_map = model('lineMap')->where('line_id',$id)->find();
            if($line_map){
                $line_map = $line_map['data'];
            }else{
                $line_map = [];
            }
        }else{
            $model->with(['point']);
        }
        $data = $model->find($id);
        if(!$data){
            return $this->error(['desc'=>'该路线不存在'],'该路线不存在','404');
        }
        if(101 == $show_top){
            $data['point'] = isset($line_map)?$line_map:[];
        }
        return $this->success($data);
    }

    //多级的地点
    public function map($id){
        $model = new \app\index\model\Line();
        $data = $model->with(['item'])->find($id);
        if(!$data){
            return $this->error(['desc'=>'该路线不存在'],'该路线不存在','404');
        }
        return $this->success($data);
    }

    //新建路线
    public function save( $user_id, $title = '', $line = [], $coordtype = 'gcj02ll'){
        ksort( $line,1);
        $line =  array_values($line);
        foreach ($line as $l){
            if(!isset($l['lat']) || !isset($l['lng']) ||(!$l['lat'] && !$l['lng'])){
                return $this->error(['desc'=>'错误的经纬度！']);
            }
        }

        $line_data = [
            'user_id'       => $user_id,
            'status'        => '101',
            'c_time'        => THINK_START_TIME,
            'del_time'      => '0',
        ];

        $model = new \app\index\model\Line();
        if(is_array($title)){
            $line_data = array_merge($line_data,$title);
        }else{
            $line_data['title'] = $title;
        }
        if($model->allowField(true)->save($line_data)){
            $line_id = $model->id;

            $save_data = $this->updateItem($user_id,$line_id, $line, [],$coordtype);
            return $this->success(['id'=>$line_id]);
        }else{
            return $this->error(['desc'=>'新建路线失败']);
        }
    }

    //更新
    public function update($user_id, $line_id = '', $line = [], $coordtype = 'gcj02ll'){
        if(!$line || !is_array($line)){
//            return $this->error(['desc'=>'请选择至少一个地点']);
        }

        ksort( $line,1);
        $line =  array_values($line);

        foreach ($line as $l){
            if(!isset($l['lat']) || !isset($l['lng']) ||(!$l['lat'] && !$l['lng'])){
                return error(['desc'=>'错误的经纬度！']);
            }
        }

        $model = new \app\index\model\Line();
        $model = $model->where('id',$line_id)->find();
        if($model){
            //1.查询出全部的节点，如果相同，就不变，不同就更新，不存在就新建
            $old        = model('LineItem')->where('line_id',$line_id)->order('id asc')->select();
            $save_data  = $this->updateItem($user_id,$line_id, $line, $old,$coordtype);
            return $this->success(['id'=>$line_id]);
        }else{
            return $this->error(['desc'=>'更新路线失败']);
        }
    }

    //传入新旧的节点，更新或新增节点，并保存 linemap
    public function updateItem($user_id, $line_id, $new = [], $old = [],$coordtype = 'gcj02ll'){
        $new = array_map(function ($vo){
            if(isset($vo['city_title']) && (!isset($vo['title']) || !$vo['title']))$vo['title'] = $vo['city_title'];
            return $vo;
        },$new);
        //节点内，只存下级数据，二级关系存在linemap里面
        $new = model('address','service')->getLocationArr($new,$coordtype);
        if($this->isError($new)){
            return $this->error($new);
        }else{
            $new = $new['data'];
        }
        model('MainNav','service')->newMainArr($user_id, $new);
        if($old){
            $save_data = $this->mergeLocation($this->getBotCity($new,$line_id),$old,['lng','lat','location_name','location_value','title']);
            if($save_data){
                if(!model('LineItem')->saveAll($save_data)){
                    return $this->error( ['desc'=>'保存信息失败']);
                }
            }
        }else{
            $save_data = $this->getBotCity($new,$line_id);
            if($save_data){
                if(!model('LineItem')->allowField(true)->isUpdate(false)->saveAll($save_data)){
                    return $this->error( ['desc'=>'保存信息失败']);
                }
            }
        }

        //保存二级城市关系数据 todo
        $line_map_data = model('address','service')->formatLineNav($new);
        if($this->isError($line_map_data)){
            return $this->error($line_map_data);
        }

        $line_map_data = $line_map_data['data'];

        //保存linemap
        $line_map = model('lineMap')->where('line_id',$line_id)->find();
        if($line_map){
            $line_map->data = $line_map_data;
            $line_map->save();
        }else{
            model('lineMap')->save(['line_id'=>$line_id,'data'=>$line_map_data]);
        }
        return $this->success(['id'=>$line_id]);
    }

    //传入新旧地点，返回合并结果
    protected function mergeLocation($new,$old,$condition = []){
        $old_count = count($old);
        $new_count = count($new);
        $max = ($old_count > $new_count)?$old_count:$new_count;
        $update_data = [];
        for ( $i = 0; $i < $max; $i++){
            if(isset($old[$i]) && isset($new[$i])){
                //判断同位置的路线是否被修改
                if(!$this->isEqual($old,$new,$condition)){
                    $update_data[]  = [
                        'id'             => $old[$i]['id'],
                        'lng'            => $new[$i]['lng'],
                        'lat'            => $new[$i]['lat'],
                        'location_name'  => $new[$i]['location_name'],
                        'location_value' => $new[$i]['location_value'],

                        't_code' => $new[$i]['t_code'],
                        'c_code' => $new[$i]['c_code'],
                        'p_code' => $new[$i]['p_code'],
                        's_code' => $new[$i]['s_code'],
                        'city'   => $new[$i]['city'],

                        'title'          => $new[$i]['title'],
                        'c_time'         => floor(THINK_START_TIME),
                        'del_time'       => '0',
                        'status'         => '101',
                    ];
                }
            }elseif (!isset($old[$i])){
                //新增
                if(isset($new[$i])){
                    $update_data[]  = [
                        'line_id'        => $new[$i]['line_id'],//$line_id,
                        'lng'            => $new[$i]['lng'],
                        'lat'            => $new[$i]['lat'],
                        'location_name'  => $new[$i]['location_name'],
                        'location_value' => $new[$i]['location_value'],

                        't_code' => $new[$i]['t_code'],
                        'c_code' => $new[$i]['c_code'],
                        'p_code' => $new[$i]['p_code'],
                        's_code' => $new[$i]['s_code'],
                        'city'   => $new[$i]['city'],

                        'title'          => $new[$i]['title'],
                        'c_time'         => floor(THINK_START_TIME),
                        'del_time'       => '0',
                        'status'         => '101',
                    ];
                }
            }else{
                //删除
                $update_data[]  = [
                    'id'             => $old[$i]['id'],
                    'del_time'       => floor(THINK_START_TIME),
                    'status'         => '99',
                ];
            }
        }
        return $update_data;
    }

    //传入条件和数据，比较是否相同
    public function isEqual($a,$b,$condition = []){
        $is_assoc = is_assoc($condition);
        foreach ($condition as $k => $v){
            if($is_assoc){
                $k1 = $k;
                $k2 = $v;
            }else{
                $k1 = $k2 = $v;
            }
            if(!(isset($a[$k1]) && isset($b[$k2]) && ($a[$k1] == $b[$k2]))){
                return false;
            }
        }
        return true;
    }

    //传入下级城市，返回上级城市数组
    public function getTopCity($line_id, $bot = [], $tCode2cCode = []){
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
        return array_map(function($item)use($line_id){
            $temp = [];
            $temp['title']          = isset($item['name'])?$item['name']:'';
            $temp['lng']            = isset($item['lng'])?$item['lng']:'';
            $temp['lat']            = isset($item['lat'])?$item['lat']:'';

            $temp['t_code']         = isset($item['t_code'])?$item['t_code']:'';
            $temp['c_code']         = isset($item['c_code'])?$item['c_code']:'';
            $temp['p_code']         = isset($item['p_code'])?$item['p_code']:'';
            $temp['s_code']         = isset($item['s_code'])?$item['s_code']:'';
            $temp['city']           = isset($item['city'])?$item['city']:'';

            $temp['location_name']  = isset($item['location_name'])?$item['location_name']:'c_code';
            $temp['location_value'] = isset($item['c_code'])?$item['c_code']:'';
            $temp['line_id']        = $line_id;
            $temp['status']         = '101';
            $temp['c_time']         = floor(THINK_START_TIME);
            $temp['del_time']       = '0';
            $temp['order_weight']   = '100';
            return $temp;

        },$top);
    }

    //传入城市，和 $tCode2cCode， 返回下级城市数组
    protected function getBotCity($data,$line_id){
        return array_map(function($item)use($line_id){
            $temp = [];
            $temp['line_id']        = $line_id;
            $temp['title']          = isset($item['title'])?$item['title']:'';
            $temp['place_id']       = isset($item['place_id'])?$item['place_id']:'0';
            $temp['lng']            = isset($item['lng'])?$item['lng']:'';
            $temp['lat']            = isset($item['lat'])?$item['lat']:'';
            $temp['location_name']  = isset($item['location_name'])?$item['location_name']:'';
            $temp['location_value'] = isset($item['location_value'])?$item['location_value']:'';
            $temp['t_code']         = isset($item['t_code'])?$item['t_code']:'';
            $temp['c_code']         = isset($item['c_code'])?$item['c_code']:'';
            $temp['p_code']         = isset($item['p_code'])?$item['p_code']:'';
            $temp['s_code']         = isset($item['s_code'])?$item['s_code']:'';
            $temp['city']           = isset($item['city'])?$item['city']:'';
            $temp['status']         = '101';
            $temp['order_weight']   = '100';
            $temp['c_time']         = floor(THINK_START_TIME);
            $temp['del_time']       = '0';
            return $temp;
        },$data);
    }


    //传入t_code获取t_code和上级城市对应的数组
    public function getCodeRelation($t_code = []){
        return array_column(array_map(function($item){
            return $item->toArray();
        },model('addressTown')->field('t_code,c_code')->where('t_code','in',$t_code)->select()),'c_code','t_code');
    }

    //传入经纬度数组批量进行逆地址编码
    protected function getLocationArr($line,$coordtype){
        //1.取出全部经纬度
    }

    //复制一条路线
    public function copyLine($id){
        $data = model('Line')->where('id',$id)->find();
        if($data){
            $line = model('Line')->isUpdate(false)->data([
                'main_id'   => $data['main_id'],
                'title'     => $data['title'],
                'user_id'   => $data['user_id'],
                'status'    => $data['status'],
                'del_time'  => $data['del_time'],
                'c_time'    => $data['c_time'],
            ]);
            if($line->save()){
                $new_line_id = $line->id;
                $sql = 'insert into line_item(line_id,title,place_id,lng,lat,t_code,c_code,p_code,s_code,city,location_name,location_value,status,order_weight,c_time,del_time) 
                              select '.$new_line_id.',title,place_id,lng,lat,t_code,c_code,p_code,s_code,city,location_name,location_value,status,order_weight,c_time,del_time from line_item where line_id='.$id.' and del_time = 0';
                $sub = Db::query($sql);
                return $this->success(['id'=>$new_line_id]);
            }else{
                return $this->error(['desc'=>'复制路线错误！']);
            }
        }else{
            return $this->error(['desc'=>'该路线不存在！']);
        }

    }



}