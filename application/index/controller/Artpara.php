<?php
namespace app\index\controller;
use think\Request;
class Artpara extends \app\common\controller\Base
{

    //新增
    public function save(){
        $file = $this->request->file();
        if(
            !$this->check('art_id',['number','min:0'])||
            !$this->check('type',['number','r','min:0'])||
            !$this->check('content','string')||
            !$this->check('order_weight',['number','r','min:0'])
        ){
            return error($this->checkError());
        }

        $this->param['main_type'] =  $this->param('main_type',103);
        $main_type =  $this->param['main_type'];

        if('104' == $main_type){
            $main = model('services')->where('id',$this->param('services_id'))->find();
            if(!$main){
                return error(['desc'=>'该商品不存在']);
            }
            $this->param['main_id']   =  $this->param('services_id');
        }else{
            $main = model('art')->where('id',$this->param('art_id'))->find();
            if(!$main){
                return error(['desc'=>'该文章不存在']);
            }
            $this->param['main_id']   =  $this->param('art_id');
        }

        //101段落文字 102图片 103地点 104服务 105标题 106视频 107经纬度地点
        $para_type = $this->param('type','');
        switch ($para_type){
            case '101':
                //文字
                break;
            case '102':
                if($file && isset($file['img']) && $file['img']){
                    if(is_array($file['img'])){
                        $file['img'] = $file['img'][0];
                    }
                    $img = model('image','service');
                    $img_result = $img->upload($file['img'],isset($main['type'])?$main['type']:114);
                    if(!$img_result['id']){
                        return error(['desc'=>$img_result['desc']]);
                    }
                    $this->param['img_id'] = $img_result['id'];
                }elseif(!isset($this->param['img_id']) || !$this->param['img_id']){
                    return error(['desc'=>'请选择1张图片！']);
                }
                if(is_array($this->param['img_id'])){
                    $this->param['img_id'] = current($this->param['img_id']);
                }
                break;
            case '103':
                //地点
                $place = model('place')->where('id',$this->param('place_id'))->find();
                if($place){
                    $location = [
                        'location_name'  => $place['location_name'],
                        'location_value' => $place['location_value'],
                        't_code'         => $place['t_code'],
                        'c_code'         => $place['c_code'],
                        'p_code'         => $place['p_code'],
                        's_code'         => $place['s_code'],
                        'city'           => $place['city'],
                        'place_id'       => $this->param('place_id',0),
                        'address_title'  => $this->param('address_title',$place['title'])
                    ];
                    foreach ($location as $key => $v){
                        $this->param[$key] = $v;
                    }
                }
                break;
            case '104':

                break;
            case '105':

                break;
            case '106':
                //处理图片
                if($file && isset($file['img']) && $file['img']){
                    if(is_array($file['img'])){
                        $file['img'] = $file['img'][0];
                    }
                    $img = model('image','service');
                    $img_result = $img->upload($file['img'],isset($main['type'])?$main['type']:114);
                    if(!$img_result['id']){
                        return error(['desc'=>$img_result['desc']]);
                    }
                    $this->param['img_id'] = $img_result['id'];
                }elseif(!isset($this->param['img_id']) || !$this->param['img_id']){
                    return error(['desc'=>'请选择1张图片！']);
                }
                if(is_array($this->param['img_id'])){
                    $this->param['img_id'] = current($this->param['img_id']);
                }

                //处理视频
                if($file && isset($file['video']) && $file['video']){
                    $video = model('video','service');
                    $video_res = $video->upload($file['video'],isset($main['type'])?$main['type']:114,$this->param['img_id']);
                    if(!$video_res['id']){
                        return error(['desc'=>$video_res['desc']]);
                    }
                    $this->param['video_id'] = $video_res['id'];
                }elseif(!isset($this->param['video_id']) || !$this->param['video_id']){
                    return error(['desc'=>'请上传视频文件！']);
                }
                break;
            case '107':
                //经纬度 反地理编码
                $location_data = model('address','service')->getLocationV2($this->param('lat',0),$this->param('lng',0));
                if(!$this->isError($location_data)){
                    $location = [
                        'location_name'  => isset($location_data['data']['location_name'])?$location_data['data']['location_name']:'',
                        'location_value' => isset($location_data['data']['location_value'])?$location_data['data']['location_value']:'',
                        't_code'         => isset($location_data['data']['t_code'])?$location_data['data']['t_code']:'',
                        'c_code'         => isset($location_data['data']['c_code'])?$location_data['data']['c_code']:'',
                        'p_code'         => isset($location_data['data']['p_code'])?$location_data['data']['p_code']:'',
                        's_code'         => isset($location_data['data']['s_code'])?$location_data['data']['s_code']:'',
                        'city'           => isset($location_data['data']['city'])?$location_data['data']['city']:'',
                        'place_id'       => '0',
                        'address_title'  => $this->param('address_title',isset($location_data['title'])?$location_data['title']:''),
                    ];
                    foreach ($location as $key => $v){
                        $this->param[$key] = $v;
                    }
                }else{
                    return error($location_data);
                }
                break;
            default:
                return error(['desc'=>'段落类型错误！']);
        }
        if(!isset($this->param['coordtype'])){
            $this->param['coordtype'] = 'gcj02ll';
        }

        $result = model('artPara','service')->save($this->param);
        if($this->isError($result)){
            return error($result);
        }else{
            //保存用户足迹
            if(103 == $para_type || 107 == $para_type){
                if($this->user && isset($location)){
                    model('UserFootprint','service')->add($this->user['id'],$location);
                }
            }

            if(104 == $main_type){
                $para = model('servicesPara');
            }else{
                $para = model('artPara');
            }
            //1.查询当前段落的类型
            return success($para->where('id',$result['data']['id'])->find());
        }
    }

    //修改
    public function update(){
        $file = $this->request->file();
        if(
            !$this->check('art_id',['number','min:0'])||
            !$this->check('type',['number','min:0'])||
            !$this->check('content','string')||
            !$this->check('order_weight',['number','min:0'])
        ){
            return error($this->checkError());
        }

        // todo 这步可以加缓存
        $this->param['main_type'] = $this->param('main_type',103);
        if($this->has('main_type') && '104' == $this->param('main_type')){
            $art = model('services')->where('id',$this->param('services_id'))->find();
            if(!$art){
                return error(['desc'=>'该商品不存在']);
            }
            $this->param['main_type'] =  $this->param('main_type');
            $this->param['main_id']   =  $this->param('services_id');
        }else{
            $art = model('art')->where('id',$this->param('art_id'))->find();
            if(!$art){
                return error(['desc'=>'该文章不存在']);
            }
            $this->param['main_type'] = 103;
            $this->param['main_id']   =  $this->param('art_id');
        }

        //101段落文字 102图片 103地点 104服务 105标题 106视频 107经纬度地点
        $type = $this->param('type',0);
        switch ($type){
            case '101':
                //文字
                break;
            case '102':
                if($file && isset($file['img']) && $file['img']){
                    if(is_array($file['img'])){
                        $file['img'] = current($file['img']);
                    }
                    $img_result = model('image','service')->upload($file['img'],$art['type']);
                    if(!$img_result['id']){
                        return error(['desc'=>$img_result['desc']]);
                    }
                    $this->param['img_id'] = $img_result['id'];
                }elseif(!isset($this->param['img_id']) || !$this->param['img_id']){
                    return error(['desc'=>'请选择1张图片！']);
                }
                if(is_array($this->param['img_id'])){
                    $this->param['img_id'] = current($this->param['img_id']);
                }
                if(!$this->param['img_id']){
                    return error(['desc'=>'图片上传失败！']);
                }
                break;
            case '103':
                //地点
                $place = model('place')->where('id',$this->param('place_id'))->find();
                if($place){
                    $location = [
                        'location_name'  => $place['location_name'],
                        'location_value' => $place['location_value'],
                        't_code'         => $place['t_code'],
                        'c_code'         => $place['c_code'],
                        'p_code'         => $place['p_code'],
                        's_code'         => $place['s_code'],
                        'city'           => $place['city'],
                        'place_id'       => $this->param('place_id',0),
                        'address_title'  => $this->param('address_title',$place['title'])
                    ];
                    foreach ($location as $key => $v){
                        $this->param[$key] = $v;
                    }
                }
                break;
            case '104':

                break;
            case '105':

                break;
            case '106':
                //处理图片
                if($file && isset($file['img']) && $file['img']){
                    if(is_array($file['img'])){
                        $file['img'] = $file['img'][0];
                    }
                    $img = model('image','service');
                    $img_result = $img->upload($file['img'],$art['type']);
                    if(!$img_result['id']){
                        return error(['desc'=>$img_result['desc']]);
                    }
                    $this->param['img_id'] = $img_result['id'];
                }elseif(!isset($this->param['img_id']) || !$this->param['img_id']){
                    return error(['desc'=>'请选择1张图片！']);
                }
                if(is_array($this->param['img_id'])){
                    $this->param['img_id'] = current($this->param['img_id']);
                }
                if(!$this->param['img_id']){
                    return error(['desc'=>'上传失败！']);
                }
                //处理视频
                if($file && isset($file['video']) && $file['video']){
                    $video = model('video','service');
                    $video_res = $video->upload($file['video'],$art['type'],$this->param['img_id']);
                    if(!$video_res['id']){
                        return error(['desc'=>$video_res['desc']]);
                    }
                    $this->param['video_id'] = $video_res['id'];
                }elseif(!isset($this->param['video_id']) || !$this->param['video_id']){
                    return error(['desc'=>'请上传视频文件！']);
                }
                break;
            case '107':
                //经纬度 反地理编码
                $location_data = model('address','service')->getLocationV2($this->param('lat',0),$this->param('lng',0));
                if(!$this->isError($location_data)){
                    $location = [
                        'location_name'  => isset($location_data['data']['location_name'])?$location_data['data']['location_name']:'',
                        'location_value' => isset($location_data['data']['location_value'])?$location_data['data']['location_value']:'',
                        't_code'         => isset($location_data['data']['t_code'])?$location_data['data']['t_code']:'',
                        'c_code'         => isset($location_data['data']['c_code'])?$location_data['data']['c_code']:'',
                        'p_code'         => isset($location_data['data']['p_code'])?$location_data['data']['p_code']:'',
                        's_code'         => isset($location_data['data']['s_code'])?$location_data['data']['s_code']:'',
                        'city'           => isset($location_data['data']['city'])?$location_data['data']['city']:'',
                        'place_id'       => '0',
                        'address_title'  => $this->param('address_title',isset($location_data['title'])?$location_data['title']:''),
                    ];
                    foreach ($location as $key => $v){
                        $this->param[$key] = $v;
                    }
                }else{
                    return error($location_data);
                }
                break;
            default:
                return error(['desc'=>'段落类型错误！']);
        }

        $result = model('artPara','service')->save($this->param);
        if($this->isError($result)){
            return error($result);
        }else{

            if(103 == $type || 107 == $type){
                if($this->user && isset($location)){
                    model('UserFootprint','service')->add($this->user['id'],$location);
                }
            }

            if(104 == $this->param['main_type']){
                $para = model('servicesPara');
            }else{
                $para = model('artPara');
            }
            return success($para->where('id',$result['data']['id'])->find());
        }
    }

    //删除
    public function delete(){
        if(!$this->check('id',['number','r','min:0'])){
            return error($this->checkError());
        }
        if($this->has('main_type') && '104' == $this->param('main_type')){
            $para =  model('ServicesPara');
        }else{
            $para = model('ArtPara');
        }
        $record = $para->where('id',$this->param('id','0'))->find();
        if($record){
            $order_weight = $record['order_weight'];
            $main_id       = $record['main_id'];
            if($record->destroy($this->param('id','0'))){
                if($this->has('main_type') && '104' == $this->param('main_type')){
                    $p =  model('ServicesPara');
                }else{
                    $p = model('ArtPara');
                }
                $p->where('main_id',$main_id)->where('del_time','0')->where('order_weight','>',$order_weight)->setDec('order_weight');
                return success([]);
            }else{
                return error(['desc'=>'删除失败！']);
            }
        }else{
            return error(['desc'=>'没找到该段落！']);
        }
    }

    public function order_weight(){

    }

}