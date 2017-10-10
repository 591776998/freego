<?php
namespace app\index\controller;
class Orderweight extends \app\common\controller\Base
{

    public function update(){
        if(!$this->check('id',['number','r','min:0'])){
            return error($this->checkError());
        }
        $art_id  = $this->param('id',0);
        $data = $this->param('data');
        if($data){
            if(is_string($data)){
                $data = json_decode($data,true);
            }elseif(is_array($data)){

            }else{
                return error(['desc'=>'错误的格式']);
            }
            $o_data = model('artPara')->where('main_id',$art_id)->select();

            $o = [];
            $location_data = [];
            foreach ($o_data as $k => $v){
                $o[] = $v['id'];
                if((103 == $v['para_type']) || (107 == $v['para_type'])){
                    $location_data[] = $v;
                }
            }

            $o = array_map(function($d){
                return $d['id'];
            },$o_data);

            $s = [];
            foreach ($data as $k => $v){
                if(isset($v['id']) && $v['id'] && isset($v['order_weight'])){
                    if(in_array($v['id'],$o)){
                        $s[] = [
                            'id'            => $v['id'],
                            'order_weight'  => $v['order_weight']
                        ];
                    }else{
                        $s[] = [
                            'id'            => $v['id'],
                            'order_weight'  => $v['order_weight'],
                            'del_time'      => '0'
                        ];
                    }
                }
            }
            $n_id_arr = array_column($data,'id');
            foreach ($o as $v){
                if(!in_array($v,$n_id_arr)){
                    $s[] = [
                        'id'        => $v,
                        'del_time'  => floor(THINK_START_TIME)
                    ];
                }
            }
            if($s){
//                model('artPara')->where('main_id',$this->param('id',0))->saveAll($s);
                model('artPara')->saveAll($s);
            }


            $locationArtRelation = model('locationArtRelation')->where('art_id',$art_id)->select();
            $locationArtRelation_md5 = array_map(function($l){
                return md5($l['location_name'].$l['location_value']);
            },$locationArtRelation);
            $add_data = [];

            $sub_sql = model('art')->field('line_id')->where('id',$art_id)->select(false);
            $line = model('lineItem')->where('line_id','exp','=('.$sub_sql.')')->select();
            if($line){
                foreach ($line as $k => $v){
                    if(!in_array(md5($v['location_name'].$v['location_value']),$locationArtRelation_md5)){
                        $add_data[] = [
                            'art_id'         => $art_id,
                            'location_name'  => $v['location_name'],
                            'location_value' => $v['location_value'],
                            't_code'         => $v['t_code'],
                            'c_code'         => $v['c_code'],
                            'p_code'         => $v['p_code'],
                            's_code'         => $v['s_code'],
                            'city'           => $v['city'],
                            'del_time'       => '0'
                        ];
                    }
                }
            }

            // todo 这个地方应该删除掉不存在的了，为了简化，现在只做增加，没做删除
            if($location_data){
                //获取未添加的
                foreach ($location_data as $k => $v){
                    if(!in_array(md5($v['location_name'].$v['location_value']),$locationArtRelation_md5)){
                        $add_data[] = [
                            'art_id'         => $art_id,
                            'location_name'  => $v['location_name'],
                            'location_value' => $v['location_value'],
                            't_code'         => $v['t_code'],
                            'c_code'         => $v['c_code'],
                            'p_code'         => $v['p_code'],
                            's_code'         => $v['s_code'],
                            'city'           => $v['city'],
                            'del_time'       => '0'
                        ];
                    }
                }
            }
            //添加
            if($add_data){
                model('locationArtRelation')->saveAll($add_data);
            }

            return success((object)null);
        }else{
            return success((object)null);
        }
    }


}