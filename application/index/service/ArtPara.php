<?php
namespace app\index\service;
class ArtPara extends \app\common\service\Base
{

    //某文章的特定段落
    public function paraList($type,$code_name,$code,$page,$pagesize){
        //1.查询
        $model = model('ArtPara');
        if($type){
            $model->where('para_type',$type);
        }
        if($code_name){
            $model->where('location_name',$code_name)->where('location_value',$code);
        }

        $data = $model->page($page,$pagesize)->order('order_weight desc')->order('(like_count - 1)/pow('.floor(THINK_START_TIME).'-c_time,1.8) desc')->select();
        return $data;
    }

    public function save($data = []){
        if(!$data){
            return $this->error(['desc'=>'没有保存任何信息！']);
        }
        if( isset($data['lng']) && isset($data['lat']) && $data['lng'] && $data['lat'] && (!isset($data['location_name']) || !isset($data['location_name']) || !isset($data['city_title'])) ){
            $data = $this->getLocation($data,true);
        }
        if(isset($data['main_type']) && 104 == $data['main_type']){
            $main_type = $data['main_type'];
        }else{
            $main_type = 103;
        }
        if(isset($data['id']) && $data['id']) {
            $the_id = $data['id'];
            unset($data['id']);
            if(104 == $main_type){
                $record = model('ServicesPara')->where('id', $the_id)->find();
            }else{
                $record = model('ArtPara')->where('id', $the_id)->find();
            }
            if(!$record){
                return $this->error(['desc'=>'保存失败']);
            }
            $old_order_weight = $record['order_weight'];
        }

        if(103 == $main_type && isset($data['type']) && (103 == $data['type']) && isset($data['place_id']) && isset($data['main_id'])){
            $this->addPlaceArtRelation($data['main_id'],$data['place_id']);
        }

        if(isset($the_id) && $the_id){
            //更新
            $save_data = [];
            if(isset($data['main_id']))$save_data['main_id'] = $data['main_id'];
            if(isset($data['place_id']))$save_data['place_id'] = $data['place_id'];
            if(isset($data['type']))$save_data['para_type'] = $data['type'];
            if(isset($data['content']))$save_data['content'] = $data['content'];
            if(isset($data['img_id']))$save_data['img_id'] = $data['img_id'];
            if(isset($data['video_id']))$save_data['video_id'] = $data['video_id'];
            if(isset($data['lat']))$save_data['lat'] = $data['lat'];
            if(isset($data['lng']))$save_data['lng'] = $data['lng'];
            if(isset($data['location_name']))$save_data['location_name'] = $data['location_name'];
            if(isset($data['location_value']))$save_data['location_value'] = $data['location_value'];
            if(isset($data['location_str']))$save_data['location_str'] = $data['location_str'];

            if(isset($data['t_code']))$save_data['t_code'] = $data['t_code'];
            if(isset($data['c_code']))$save_data['c_code'] = $data['c_code'];
            if(isset($data['p_code']))$save_data['p_code'] = $data['p_code'];
            if(isset($data['s_code']))$save_data['s_code'] = $data['s_code'];
            if(isset($data['city']))$save_data['city'] = $data['city'];
            if(isset($data['city_title']))$save_data['city_title'] = $data['city_title'];

            if(isset($data['address_title']))$save_data['address_title'] = $data['address_title'];

            if($save_data){
                $save_data['m_time']   = floor(THINK_START_TIME);
                $save_data['status']   = '101';
                $save_data['del_time'] = '0';

                if(104 == $main_type){
                    $res = model('ServicesPara');
                }else{
                    $res = model('ArtPara');
                }
                $res = $res->where('id', $the_id)->update($save_data);
                if($res){
                    if(isset($data['order_weight']) && ($data['order_weight'] != $old_order_weight)){
                        //1.排名前进 2.排名后退
                        if(104 == $main_type){
                            $order_update = model('ServicesPara');
                        }else{
                            $order_update = model('ArtPara');
                        }
                        $order_update =  $order_update->where('main_id',$record['main_id'])->where('id','<>',$the_id);
                        if($data['order_weight'] > $old_order_weight){
                            //后退
                            $order_update->where('order_weight','>',$old_order_weight)->where('order_weight','<=',$data['order_weight'])->setDec('order_weight');
                        }else{
                            //前进
                            $order_update->where('order_weight','<',$old_order_weight)->where('order_weight','>=',$data['order_weight'])->setInc('order_weight');
                        }
                    }

                    return $this->success(['id'=>$the_id]);
                }else{
                    return $this->error(['desc'=>'文章段落保存失败，请稍后再试！']);
                }
            }
            return $this->success(['id'=>$the_id]);
        }else{
            //新增
            if(104 == $main_type){
                $res = model('ServicesPara');
            }else{
                $res = model('ArtPara');
            }
            $res->data([
                'main_id'       => isset($data['main_id'])?$data['main_id']:'0',
                'place_id'      => isset($data['place_id'])?$data['place_id']:'0',
                'para_type'     => $data['type'],
                'content'       => isset($data['content'])?$data['content']:'',
                'order_weight'  => isset($data['order_weight'])?$data['order_weight']:'',
                'status'        => '101',
                'del_time'      => '0',
                'img_id'        => isset($data['img_id'])?$data['img_id']:'0',
                'video_id'      => isset($data['video_id'])?$data['video_id']:'0',
                'c_time'        => floor(THINK_START_TIME),
                'm_time'        => floor(THINK_START_TIME),
                'dynamic_id'    => isset($data['dynamic_id'])?$data['dynamic_id']:'0',
                'lat'           => isset($data['lat'])?$data['lat']:'0',
                'lng'           => isset($data['lng'])?$data['lng']:'0',
                'location_name' => isset($data['location_name'])?$data['location_name']:'',
                'location_value'=> isset($data['location_value'])?$data['location_value']:'',
                'location_str'  => isset($data['location_str'])?$data['location_str']:'',
                't_code'        => isset($data['t_code'])?$data['t_code']:'',
                'c_code'        => isset($data['c_code'])?$data['c_code']:'',
                'p_code'        => isset($data['p_code'])?$data['p_code']:'',
                's_code'        => isset($data['s_code'])?$data['s_code']:'',
                'city'          => isset($data['city'])?$data['city']:'',
                'city_title'    => isset($data['city_title'])?$data['city_title']:'',
                'address_title' => isset($data['address_title'])?$data['address_title']:'',
            ]);
            if($res->save()){

                //修改在后面的全部段落的排序
                if(104 == $main_type){
                    $u = model('ServicesPara');
                }else{
                    $u = model('ArtPara');
                }
                $u->where('id','<>',$res->id)->where('main_id',isset($data['main_id'])?$data['main_id']:0)->where('order_weight','>=',$res->order_weight)->setInc('order_weight');
//
                $this->copyComment($res->main_id,$res->dynamic_id);

                return $this->success(['id'=>$res->id]);
            }else{
                return $this->error(['desc'=>'文章段落保存失败，请稍后再试！']);
            }
        }
    }

    //引入评论 文章id 段落ID 所属动态ID  类型
    protected function copyComment($art_id,$dynamic_id){
        if($dynamic_id && $art_id){
                $field1 = [
                        'id',
                        'main_id',
                        'type',
                        'reply_id',
                        'reply_user_id',
                        'user_id',
                        'rub_id',
                        'content',
                        'star',
                        'like_count',
                        'status',
                        'del_time',
                        'come_id',
                        'come_type'
                ];
                $field2 = [
                    'id',
                    $art_id,
                    'type',
                    'reply_id',
                    'reply_user_id',
                    'user_id',
                    'rub_id',
                    'content',
                    'star',
                    'like_count',
                    'status',
                    'del_time',
                    'main_id',
                    'type'
                ];
                //引入
                $field1_str = implode(',',$field1);
                $field2_str = implode(',',$field2);

                $sql = "insert into `comment`($field1_str) select $field2_str from `comment` b where (`main_id`=$dynamic_id and `type`=103 and del_time=0) and ((select count(*) from `comment` where (id=b.id and main_id=$art_id and come_id=b.main_id and come_type=b.type ))=0)";
                $res = \Think\Db::execute($sql);
        }
        return '';
    }

    //删除评论
    protected function delComment($art_id,$para_id,$dynamic_id,$main_type = 103){
        if($dynamic_id){
            if(104 == $main_type){
                $artPara = model('servicesPara');
            }else{
                $artPara = model('artPara');
            }
            $count = $artPara->where('dynamic_id',$dynamic_id)->where('id','<>',$para_id)->count();
            if($count){
                //删掉
                model('comment')->where('main_id',$art_id)->where('type','103')->where('come_id',$dynamic_id)->where('come_type','103')->update(['del_time'=>floor(THINK_START_TIME)]);
            }
        }
    }

    //使用经纬度获取文章lacation信息
    protected function getLocation($data,$full_name = false){
        if(!isset($data['city_title']))$data['city_title']     = '';
        if(isset($data['lat']) && isset($data['lng']) && (!isset($data['location_name']) || !isset($data['location_value']) || !isset($data['city_title']))){
            $location_data = model('address','service')->getLocationV2($data['lat'],$data['lng'],isset($data['coordtype'])?$data['coordtype']:'gcj02ll');
            if(!$this->isError($location_data)){
                $data['location_name']  = $location_data['data']['location_name'];
                $data['location_value'] = $location_data['data']['location_value'];
                $data['city_title']     = $location_data['data']['city_title'];
            }else{
                $data['location_name']  = '';
                $data['location_value'] = '';
            }
        }
        if($full_name)$data['location_str'] =  isset($location_data['data']['title'])?$location_data['data']['title']:model('address','service')->getFullName($data['location_name'], $data['location_value']);
        return $data;
    }

    //添加一个地点和文章的关联记录
    protected function addPlaceArtRelation($art_id,$place_id){
        if($art_id && $place_id){
            $sql = 'insert ignore into `place_art_relation`(`art_id`,`place_id`,`del_time`) values ('.$art_id.','.$place_id.',0)';
            if(\Think\Db::execute($sql)){
                return true;
            }
        }
        return false;
    }
}