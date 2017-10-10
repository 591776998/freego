<?php
namespace app\seller\controller;
class Place extends \app\common\controller\SellerBase
{
    public function index(){
        //判断登录
        if(!$this->seller)return $this->errorLogin();
        //判断参数
        $data = model('place')->with(['img'])->where('seller_id',$this->seller['id']);
            if($this->has('key_word') && $this->param('key_word','')){
                $data->where('title|boss|phone','like','%'.$this->param('key_word').'%');
            }
        $data = $data->page($this->param('page',1),$this->param('page_size',10))
            ->order('id desc')
            ->select();
        return success($data);
    }

    public function createNew(){
        if(!$this->seller){
            return $this->errorLogin();
        }

        //如果当前的商家已经有了地点，那直接跳转过去，否则，就新建一个，然后跳转过去
        $place = model('place')->where('seller',$this->seller['id'])->find();
        if(!$place){
            //根据创建成功的资质的信息，新建一个地点
            $info = model('sellerInfo')->where('seller_id',$this->seller['id'])->where('status',101)->find();
            if(!$info){
                return $this->errorPage('未找到您的资质信息，请确认您已经提交资质信息并通过审核');
            }

            $location_name = '';
            $location_value = '';
            if($info['t_code']){
                $location_name = 't_code';
                $location_value = $info['t_code'];
            }elseif($info['city']){
                $location_name = 'city';
                $location_value = $info['city'];
            }elseif ($info['c_code']){
                $location_name = 'c_code';
                $location_value = $info['c_code'];
            }elseif ($info['p_code']){
                $location_name = 'p_code';
                $location_value = $info['p_code'];
            }elseif ($info['s_code']){
                $location_name = 's_code';
                $location_value = $info['s_code'];
            }

            $save_data = [
                'seller_id' => $this->seller['id'],
                'title'     => $info['place_title'],
                'run_s_time'=> '28800',
                'run_e_time'=> '64800',
                'run_time'  => '8:00-18:00',
                'phone'     => $info['place_phone'],
                'address'   => $info['address'],
                'boss'      => $info['boos_name'],
                't_code'    => $info['t_code'],
                'c_code'    => $info['c_code'],
                'p_code'    => $info['p_code'],
                's_code'    => $info['s_code'],
                'city'      => $info['city'],
                'location_name'=>$location_name,
                'location_value'=>$location_value,
                'discount'      => 10000,
                'percentage'    => 0
            ];

            if(!$place = model('place')->data($save_data)->isUpdate(false)->save()){
                return error(['desc'=>'新建地点失败，请稍后再试']);
            }
        }

        $this->redirect('editor', ['id' => $place['id']]);
    }

    //创建
    public function create(){
        $all_tag = model('artTag')->where('type',104)->select();
        $this->assign('tag',$all_tag);
        return $this->fetch();
    }

    public function editor(){
        $data = model('place')->with(['tag','album','park','services'=>function($query){
            $query->where('status','<>','99');
        },'img'])->where('id',$this->param('id',0))->find();
        if(!$data){
            return $this->errorPage('该地点不存在或已被删除，请联系管理员');
        }

        $all_tag = model('artTag')->where('type',104)->select();

        if(!isset($data['img_url']))$data['img_url'] = '';

        $this->assign('data',$data);
        $this->assign('tag',$all_tag);
        return $this->fetch();
    }

    //新建
    public function save(){
        $file = $this->request->file();

        $data = $this->conv($this->param);

        if(isset($file['img']) && $file['img']){
            //图片类型 0未分类  101游记 102活动 103提问 104动态 105头像 106用户主页图片 107地点封面图 108服务封面图 109套餐封面图 110动态视频封面图 111群组封面图 112评论图片 113地点相册
            $img_res = model('image','service')->upload($file['img'],107);
            $data['img_id'] = $img_res['id'];
        }else{
            return error(['desc'=>'请选择封面图片']);
        }

        if(isset($data['run_s_time']))$data['run_s_time'] = timeToSecond($data['run_s_time']);
        if(isset($data['run_e_time']))$data['run_e_time'] = timeToSecond($data['run_e_time']);

        $location_data = $this->formatLocation($data);
        if(isset($location_data['title']))unset($location_data['title']);
        $data = array_merge($data,$location_data);

        if(isset($data['discount'])){
            if($data['discount'] && !is_numeric($data['discount'])){
                return error(['desc'=>'折扣应该是0到10的数字']);
            }else if(is_numeric($data['discount'])){
                if(0 > $data['discount']){
                    return error(['desc'=>'折扣不能小于0']);
                }elseif (10 < $data['discount']){
                    return error(['desc'=>'折扣不能大于10']);
                }
                $data['discount'] = 1000 * $data['discount'];
            }else{
                unset($data['discount']);
            }
        }
        if(isset($data['percentage'])){
            if($data['percentage'] && !is_numeric($data['percentage'])){
                return error(['desc'=>'提成应该是0到100的数字']);
            }else if(is_numeric($data['percentage'])){
                if(0 > $data['percentage']){
                    return error(['desc'=>'提成不能小于0%']);
                }elseif (100 < $data['percentage']){
                    return error(['desc'=>'提成不能大于100%']);
                }
                $data['percentage'] = 100 * $data['percentage'];
            }else{
                unset($data['percentage']);
            }
        }
        $data['notice']      = isset($data['notice'])?$data['notice']:'102';

        $res = model('place')->data($data);
        if($res->allowField(true)->save()){
            //保存图片和标签
            if(isset($file['album']) && $file['album']){
                //图片类型 0未分类  101游记 102活动 103提问 104动态 105头像 106用户主页图片 107地点封面图 108服务封面图 109套餐封面图 110动态视频封面图 111群组封面图 112评论图片 113地点相册
                $img_res = model('image','service')->upload($file['album'],113);
                $this->saveAlbum($res->id,array_map(function($item){return $item['id'];},$img_res));
            }else if($this->has('album_id')){
                $this->saveAlbum($res->id,$this->param['album_id']);
            }

            //保存标签
            if($this->has('tag')){
                //保存相册图片
                $tag = [];
                foreach ($this->param['tag'] as $tag_id){
                    $album[] = [
                        'place_id'  => $res->id,
                        'tag_id'    => $tag_id
                    ];
                }
                if($tag){
                    model('PlaceTagRelation')->saveAll($tag);
                }
            }

            //保存停车地点
            if($this->has('park')){
                $park = [];
                foreach ($this->param['park'] as $park_data){
                    $park[] = $this->conv([
                        'place_id'  => $res->id,
                        'title'     => $park_data['title'],
                        'bd_lng'       => $park_data['bd_lng'],
                        'bd_lat'       => $park_data['bd_lat'],
                        'touring'   => isset($park_data['touring'])?$park_data['touring']:'102'
                    ]);
                }
                if($park){
                    model('PlacePark')->saveAll($park);
                }
            }
            return success(model('place')->where('id',$res->id)->find());
        }else{
            return error(['desc'=>'创建失败']);
        }
    }


    //更新
    public function update(){
        if(!$this->check('id',['number','r','min:0'])){
            return error(['desc'=>'错误的ID']);
        }
        $file = $this->request->file();

        $data = $this->conv($this->param);

        if(isset($data['discount'])){
            if($data['discount'] && !is_numeric($data['discount'])){
                return error(['desc'=>'折扣应该是0到10的数字']);
            }else if(is_numeric($data['discount'])){
                if(0 > $data['discount']){
                    return error(['desc'=>'折扣不能小于0']);
                }elseif (10 < $data['discount']){
                    return error(['desc'=>'折扣不能大于10']);
                }
                $data['discount'] = 1000 * $data['discount'];
            }else{
                unset($data['discount']);
            }
        }
        if(isset($data['percentage'])){
            if($data['percentage'] && !is_numeric($data['percentage'])){
                return error(['desc'=>'提成应该是0到100的数字']);
            }else if(is_numeric($data['percentage'])){
                if(0 > $data['percentage']){
                    return error(['desc'=>'提成不能小于0%']);
                }elseif (100 < $data['percentage']){
                    return error(['desc'=>'提成不能大于100%']);
                }
                $data['percentage'] = 100 * $data['percentage'];
            }else{
                unset($data['percentage']);
            }
        }

        if(isset($file['img']) && $file['img']){
            //图片类型 0未分类  101游记 102活动 103提问 104动态 105头像 106用户主页图片 107地点封面图 108服务封面图 109套餐封面图 110动态视频封面图 111群组封面图 112评论图片 113地点相册
            $img_res = model('image','service')->upload($file['img'],107);
            $data['img_id'] = $img_res['id'];
        }
        $id = $this->param('id',0);

        if(isset($data['run_s_time']))$data['run_s_time'] = timeToSecond($data['run_s_time']);
        if(isset($data['run_e_time']))$data['run_e_time'] = timeToSecond($data['run_e_time']);

        $res = model('place')->with(['tag','album','park','img'])->where('id',$id)->find();

        if(!$res){
            return error(['desc'=>'没找到该地点']);
        }

        //更新标签
        $del_time = floor(THINK_START_TIME);
        $save_tag_data = [];
        $new_tag = isset($data['tag'])?$data['tag']:[];
        $old_tag_arr = $res->tag;
        $old_tag = array_map(function($v){return $v['id'];},$old_tag_arr);//array_column($old_tag_arr,'id');
        //1.add
        $add1 = array_diff($new_tag,$old_tag);
        if($add1){
            foreach ($add1 as $v){
                $save_tag_data[] = [
                    'place_id'  => $id,
                    'tag_id'    => $v,
                    'del_time'  => 0
                ];
            }
        }
        //删除
        foreach ($old_tag_arr as $o){
            if(!in_array($o['id'],$new_tag)){
                $save_tag_data[] = [
                    'id'        => $o->pivot->id,
                    'del_time'  => $del_time
                ];
            }
        }
        if($save_tag_data){
            model('PlaceTagRelation')->allowField(true)->saveAll($save_tag_data);
        }

        //更新相册
        $save_album_data = [];
        $new_album = isset($data['album_id'])?$data['album_id']:[];
        foreach ($new_album as $k => $v){
            if(!$v)unset($new_album[$k]);
        }
        $old_album_arr = $res->album;
        $old_album = array_map(function($v){return $v['id'];},$old_album_arr);//array_column($old_album_arr,'id');
        //add
        $add2 = array_diff($new_album,$old_album);
        if($add2){
            foreach ($add2 as $v){
                $save_album_data[] = [
                    'place_id'  => $id,
                    'img_id'    => $v,
                    'del_time'  => 0
                ];
            }
        }
        //删除
        foreach ($old_album_arr as $v){
            if(!in_array($v['id'],$new_album)){
                $save_album_data[] = [
                    'id'        => $v->pivot->id,
                    'del_time'  => $del_time
                ];
            }
        }

//        if(isset($data['lat']) && isset($data['lng']) && $data['lng'] && $data['lat'] && !isset($data['location_name'])){
//            $address = new \app\index\service\Address();
//            $location_info = $address->getLocation($data['lat'],$data['lng']);
//            if(!$this->isError($location_info)){
//                $data = array_merge($data,$location_info['data']);
//            }
//        }

        if($save_album_data){
            model('PlaceAlbum')->saveAll($save_album_data);
        }

        //更新停车点
        //1.新增
        $save_park_data = [];
        $new_park_arr = isset($data['park'])?$data['park']:[];
        $new_park = [];
        foreach ($new_park_arr as $k => $v){
            if(isset($v['id']) && $v['id']){
                $new_park[] = $v['id'];
            }
        }
        $old_park_arr = $res->park;

        //1.add
        foreach ($new_park_arr as $v){
            if(!isset($v['id']) || !$v['id']){
                $save_park_data[] = $this->conv([
                    'place_id'  => $id,
                    'title'     => $v['title'],
                    'bd_lng'    => $v['bd_lng'],
                    'bd_lat'    => $v['bd_lat'],
                    'touring'   => isset($v['touring'])?$v['touring']:102,
                    'del_time'  => 0,
                ]);
            }
        }

        //del
        $new_park_arr_res = $this->array_key_arr($new_park_arr,'id');
        foreach ($old_park_arr as $v){
            if(!in_array($v['id'],$new_park)){
                $save_park_data[] = [
                    'id'        => $v['id'],
                    'del_time'  => $del_time
                ];
            }else{
                //更新
                $new_park_touring = isset($new_park_arr_res[$v['id']]['touring'])?$new_park_arr_res[$v['id']]['touring']:102;
                if(
                    $new_park_arr_res[$v['id']]['title'] != $v['title']||
                    $new_park_arr_res[$v['id']]['bd_lng'] != $v['bd_lng']||
                    $new_park_arr_res[$v['id']]['bd_lat'] != $v['bd_lat']||
                    $v['touring'] != $new_park_touring
                ){
                    $save_park_data[] = $this->conv([
                        'id'            => $v['id'],
                        'place_id'      => $id,
                        'title'         => $new_park_arr_res[$v['id']]['title'],
                        'bd_lng'       => $new_park_arr_res[$v['id']]['bd_lng'],
                        'bd_lat'       => $new_park_arr_res[$v['id']]['bd_lat'],
                        'touring'       => $new_park_touring,
                        'del_time'      => 0,
                    ]);
                }
            }
        }

        if($save_park_data){
            model('PlacePark')->saveAll($save_park_data);
        }

        if(isset($data['tag']))unset($data['tag']);
        if(isset($data['album_id']))unset($data['album_id']);
        if(isset($data['park']))unset($data['park']);

        $location_data = $this->formatLocation($data);
        if(isset($location_data['title']))unset($location_data['title']);
        $data = array_merge($data,$location_data);

        if($res->allowField(true)->save($data,['id'=>$res->id])){

            return success(model('place')->where('id',$res->id)->find());
        }else{
            return error(['desc'=>'没有作任何修改']);
        }
    }

    //保存相册
    protected function saveAlbum($id,$album = []){
        $album_save = [];
        foreach ($album as $album_id){
            $album_save[] = [
                'place_id'  => $id,
                'img_id'    => $album_id
            ];
        }
        if($album_save){
            model('placeAlbum')->saveAll($album_save);
        }
        return true;
    }

    //
    protected  function array_key_arr($arr,$key){
        $res = [];
        foreach ($arr as $v){
            if(isset($v[$key])){
                $res[$v[$key]] = $v;
            }
        }
        return $res;
    }

    //输入位置信息，返回格式化好的
    protected function formatLocation($data){
        $location_value = isset($data['location_value'])?$data['location_value']:'0';
        $location_name  = isset($data['location_name'])?$data['location_name']:'0';
        $lat            = isset($data['lat'])?$data['lat']:'0';
        $lng            = isset($data['lng'])?$data['lng']:'0';

        $param = [];
        if(!$location_name){
            $param = [
                $lat,
                $lng
            ];
        }else{
            $param = [
                $location_name,
                $location_value
            ];
        }


        $res = model('Geo','service')->sl($param)->gl();
        if($this->isError($res)){
            return [];
        }else{
            return current($res['data']);
        }
//        $data = [
//            'location_name'  => isset($data['location_name'])?$data['location_name']:'',
//            'location_value' => isset($data['location_value'])?$data['location_value']:'',
//            't_code'         => isset($data['t_code'])?$data['t_code']:'',
//            'c_code'         => isset($data['c_code'])?$data['c_code']:'',
//            'p_code'         => isset($data['p_code'])?$data['p_code']:'',
//            's_code'         => isset($data['s_code'])?$data['s_code']:'',
//            'city'           => isset($data['city'])?$data['city']:'',
//            'city_title'     => isset($data['city_title'])?$data['city_title']:'',
//        ];
//
//        if(isset($data['lat']) && isset($data['lng']) && $data['lng'] && $data['lat'] && !isset($data['location_name'])){
//
//            $lo = new \app\common\service\Location();
//            $lo_info = $lo->read($data['lat'],$data['lng'],false);
//            if($lo_info){
//                $data['location_name']  = $lo_info['location_name']?:$data['location_name'];
//                $data['location_value'] = $lo_info['location_value']?:$data['location_value'];
//                $data['t_code']         = $lo_info['t_code']?:$data['t_code'];
//                $data['c_code']         = $lo_info['c_code']?:$data['c_code'];
//                $data['p_code']         = $lo_info['p_code']?:$data['p_code'];
//                $data['s_code']         = $lo_info['s_code']?:$data['s_code'];
//                $data['city']           = $lo_info['city']?:$data['city'];
//                $data['city_title']     = $lo_info['city_title']?:$data['city_title'];
//            }
//        }
//        return $data;
    }

    //
    protected function conv($data){
        if((isset($data['bd_lat']) && !isset($data['lat'])) && (isset($data['bd_lng']) && !isset($data['lng'])) && (is_numeric($data['bd_lng']) && is_numeric($data['bd_lat']))){
            $temp = bd09togcj02($data['bd_lng'],$data['bd_lat']);
            $data['lng'] = $temp[0];
            $data['lat'] = $temp[1];
        }
        return $data;
    }
}