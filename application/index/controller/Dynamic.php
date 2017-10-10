<?php
namespace app\index\controller;
use think\Request;
class Dynamic extends \app\common\controller\Base
{
    //get list
    public function index(){
        if(!$this->check('user_id',['number','min:0'])||
            !$this->check('is_my_follow',['number','min:0'])||
            !$this->check('key_word','string')||
            !$this->check('page',['number','min:0'])||
            !$this->check('page_size',['number','min:0']))
        {
            return error($this->checkError());
        }
        
        //1.类型
        $data = model('Art','service')->dynamic_index($this->user, $this->param('user_id',0),$this->param('is_my_follow','0'), $this->param('key_word'), $this->param('order','show_time'),$this->param('page',1),$this->param('page_size',10));

        return success($data);
    }

    //get 需要id
    public function read(){
        if(!$this->check('id',['number','r','min:0'])){
            return error($this->checkError());
        }

        $res = model('Art','service')->dynamic_read($this->user, $this->param('id'));
        if($this->isError($res)){
            return error($res);
        }
        return success($res);
    }

    //post todo
    public function save(){
        set_time_limit(300);//设置最大运行时间5分钟，感觉应该做成分段落上传的
        $file = $this->request->file();

        if(!$this->user){
            return error(['desc'=> '请先登录,然后发表文章！'],'请先登录,然后发表文章！','401');
        }
        if(!$this->check('content_text',['string','r'])){
            return error(['desc'=>'请输入内容']);
        }
        if(!$this->check('show_time',['number','r'])){
            return error(['desc'=>'请选择日期时间']);
        }
        if( strtotime(date('Y-m-d',strtotime( "+1 day"))) < ($this->param['show_time']) ){
            return error(['desc'=>'日期不能超过今天！']);
        }
        $data['art_type']  = 104;
        $data['tag_id']    = 0;
        $data['place_id']  = $this->param('place_id','0');
        $data['lat']       = $this->param('lat','0');
        $data['lng']       = $this->param('lng','0');
        $data['coordtype'] = $this->param('coordtype','gcj02ll');
        $data['show_time'] = $this->param('show_time',floor(THINK_START_TIME));
        $data['img_id']    = $this->param('img_id',[]);
        $data['video_id']  = $this->param('video_id',0);

//        $today = strtotime(date('Y-m-d H:i:s'));
//        if(($data['show_time'] > $today) && ('00:00:00' == date('H:i:s',$data['show_time']))){
//            $data['show_time'] = strtotime(date('Y-m-d ',$data['show_time']).date('H:i:s'));
//        }

        if($this->has('add_photo')){
            $data['add_photo'] = $this->param('add_photo',0);
        }

        if(!$this->param('title')){
            $data['title'] = $this->getLocation($this->param);
        }else{
            $data['title'] = $this->param('title');
        }

        if($data['place_id']) {
            $p = model('place')->where('id',$data['place_id'])->find();
            if($p){
                $data['location_name']  = $p['location_name'];
                $data['location_value'] = $p['location_value'];
            }
        }
        $data['content']    = [];
        $data['content'][]  = [
                                'para_type'     => '101',
                                'content'       => $this->param('content_text'),
                                'img_id'        => '0',
                                'video_id'      => '0',
                              ];

        if($file){
            if(isset($file['video']) && $file['video']){
                //上传视频
                if(isset($file['img'])){
                    if(!is_array($file['img'])){
                        $file['img'] = [$file['img']];
                    }
                    $img = model('image','service');
                    $img_result = $img->upload($file['img'],104);
                    $first = current($img_result);
                    $video_img = $first['id'];
                }else{
                    $video_img = 0;
                }
                $img = model('video','service');
                $video_res = $img->upload($file['video'],104,$video_img);
                $data['content'][] = [
                    'para_type'     => '106',
                    'content'       => '',
                    'img_id'        => $video_img,
                    'video_id'      => $video_res['id'],
                ];
                $data['img_id'] = $video_img;
            }else{
                if(isset($file['img']) && $file['img']){
                    if(!is_array($file['img'])){
                        $file['img'] = [$file['img']];
                    }
                    $img = model('image','service');
                    $img_result = $img->upload($file['img'],104);
                    $first = current($img_result);
                    foreach ($img_result as $k => $v){
                        $data['content'][] = [
                            'para_type'     => '102',
                            'content'       => '',
                            'img_id'        => $v['id'],
                            'video_id'      => '0',
                        ];
                    }
                    $data['img_id'] = $first['id'];
                }else{
                    $data['img_id'] = '0';
                }
            }
        }else{
            if(isset($data['video_id']) && $data['video_id']){
                if(isset($data['img_id'])){
                    $video_img = $data['img_id'][0];
                }else{
                    $video_img = 0;
                }
                $data['content'][] = [
                    'para_type'     => '106',
                    'content'       => '',
                    'img_id'        => $video_img,
                    'video_id'      => $data['video_id'],
                ];
                $data['img_id'] = $video_img;
            }else{
                if(isset($data['img_id']) && $data['img_id']){
                    $first = $data['img_id'][0];
                    foreach ($data['img_id'] as $k => $v){
                        $data['content'][] = [
                            'para_type'     => '102',
                            'content'       => '',
                            'img_id'        => $v,
                            'video_id'      => '0',
                        ];
                    }
                    $data['img_id'] = $first;
                }else{
                    $data['img_id'] = '0';
                }
            }
        }

        //1.先创建文章
        $result = model('Art','service')->addMain($this->user['id'], $data);
        if($this->isError($result)){
            return error($result);
        }

        return success(model('Art','service')->read($this->user,$result['data']['id']));
    }

    //put 需要id
    public function update(){}

    //delete 需要id
    public function delete(){}


    //
    protected function getLocation($data){
        //获取地点信息
        return '';
    }
}

