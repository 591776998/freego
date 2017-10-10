<?php
namespace app\index\controller;
use think\Request;
class Art extends \app\common\controller\Base
{
    //get list
    public function index(){
        if(!$this->check('art_type',['number','r','min:0'])||
                !$this->check('user_id',['number','min:0'])||
                !$this->check('forum_id',['number','min:0'])||
                !$this->check('key_word','string')||
                !$this->check('place_id',['number','min:0'])||
                !$this->check('place_type',['number','min:0'])||
                !$this->check('is_my_follow',['number','min:0'])||
                !$this->check('page',['number','min:0'])||
                !$this->check('page_size',['number','min:0'])||
                !$this->check('is_best',['number','min:0'])||
                !$this->check('status',['number','min:0'])||
                !$this->check('is_collect',['number','min:0'])||
                !$this->check('is_joined',['number','min:0'])||
                !$this->check('location_name',['string'])||
                !$this->check('location_value',['string'])
        ){
            return error($this->checkError());
        }

        if($this->has('forum_id')){
            $forum_id = $this->param('forum_id',0);
            if(0 == $forum_id){
                $forum_id = 'all';
            }
        }else{
            $forum_id = 0;
        }

        $location_name  = $this->param('location_name','');
        $location_value = $this->param('location_value','');
        $lat = $this->param('lat','0');
        $lng = $this->param('lng','0');

        if(!$this->has('location_name') && $this->has('lat') && $this->has('lng') && $lat && $lng){
            $lo = model('address','service')->getLocation($this->param('lat'),$this->param('lng'));
            if('200' == $lo['code']){
                $location_name = $lo['data']['location_name'];
                $location_value = $lo['data']['location_value'];
            }
        }

        //1.类型
        return success(model('Art','service')->index($this->user, $this->param('art_type'),$this->param('tag_id',0), $this->param('user_id',0),$forum_id, $this->param('key_word'),0,0,$this->param('order','id'),$this->param('is_my_follow','0'),$this->param('page',1),$this->param('page_size',10),$this->param('is_best',0),$this->param('status',101), $this->param('is_collect',0), $this->param('is_joined',0),$location_name,$location_value));
    }

    //get 需要id
    public function read(){
        if(!$this->check('id',['number','r','min:0'])){
            return error($this->checkError());
        }
        
        $res = model('Art','service')->read($this->user, $this->param('id'));
        if($this->isError($res)){
            return error($res);
        }
        return success($res);
    }

    //post todo
    public function save(){
        set_time_limit(300);//设置脚本最大运行时间5分钟，感觉应该做成分段落上传的，最后确实做成分段落上传了
        $file = $this->request->file();

        if(!$this->user){
            return error(['desc'=> '请先登录,然后发表文章！'],'请先登录,然后发表文章！','401');
        }

        if(!$this->check('art_type',['number','r','min:0'])){
            return error(['desc'=>'错误的文章类型']);
        }
        if(104 != $this->param('art_type')){
            if(!$this->check('title',['string','r'])){
                return error(['desc'=>'请输入标题']);
            }
        }

        if(!$this->check('tag_id',['number|array'])){
            return error(['desc'=>'请选择文章类型！']);
        }
        if($this->has('show_time')){
            if( strtotime(date('Y-m-d',strtotime( "+1 day"))) < ($this->param['show_time']) ){
                return error(['desc'=>'日期不能超过今天！']);
            }
        }else{
            $this->param['show_time'] = THINK_START_TIME;
        }
        switch ($this->param['art_type']){

            case 101:
                //游记

                break;
            case 102:
                //活动
                if(!$this->check('total_number',['number','r'])){
                    return error(['desc'=>'请输入限制人数']);
                }
                if(!$this->check('has_car',['number','r'])){
                    return error(['desc'=>'请选择是否允许提供顺风车']);
                }

                //检查图片和文章名称是否存在

                break;
            case 103:
                //提问

                break;

            case 104:
                //动态

                break;
        }

        //如果是游记和活动，先创建路线
        if($this->has('line_id') && $this->param['line_id']) {
            $res_line = model('line','service')->copyLine($this->param['line_id']);
            if(!$this->isError($res_line)){
                $this->param['line_id'] = $res_line['data']['id'];
            }else{
                $this->param['line_id'] = '0';
            }
        }else{
            if($this->has('line') && $this->param['line']) {
                $line_result = model('line','service')->save($this->user['id'], '路线', $this->param['line']);
                if ($this->isError($line_result)) {
                    return error(['desc' => '路线信息保存失败']);
                } else {
                    $this->param['line_id'] = $line_result['data']['id'];
                }
            } else {
                $this->param['line_id'] = '0';
            }
        }

        if($file){
            if(isset($file['video']) && $file['video']){
                //上传视频
                $video_img = 0;
                if($file['img']){
                    if(!is_array($file['img']))$file['img'] = [$file['img']];
                    $img_result = model('image','service')->upload($file['img'],$this->param['art_type']);
                    $first = current($img_result);
                    $video_img = $first['id'];
                }
                if(!is_array($file['video']))$file['video'] = [$file['video']];
                $video_res = model('video','service')->upload($file['video'],$this->param['art_type'],$video_img);
                $this->param['img_id'] = $video_img;
            }else{
                if(!is_array($file['img']))$file['img'] = [$file['img']];
                $img_result = model('image','service')->upload($file['img'],$this->param['art_type']);
                $first = current($img_result);
                $this->param['img_id'] = $first['id'];
            }
        }else{
            if(!$this->has('img_id'))$this->param['img_id'] = '0';
        }

        if(102 == $this->param['art_type']){
            if(!isset($this->param['img_id'])){
                return error(['desc'=>'请上传封面图！']);
            }
            $activity_data = isset($this->param['activity'])?$this->param['activity']:[];
            $activity_data = $this->activityParam($this->user['id'],$activity_data,$this->param('title'),isset($this->param['img_id'])?$this->param['img_id']:0);
            if(!$activity_data && (101 == $this->param('status',101))){
                return error(['desc'=>'请填写活动相关属性！']);
            }
        }

        if($this->has('content')) {
            //验证和处理内容
            foreach ($this->param['content'] as $k => $v){
                $this->param['content'][$k]['para_type'] = $v['type'];
                $this->param['content'][$k]['img_id'] = '0';
                $this->param['content'][$k]['video_id'] = '0';

                if(!isset($v['file_index']))$v['file_index'] = 0;
                if(102 == $v['type']){
                    if(!isset($v['img_id']) || !$v['img_id']){
                        $this->param['content'][$k]['img_id'] = isset($img_result[$v['file_index']])?$img_result[$v['file_index']]['id']:'0';
                    }
                    //图片
                }elseif(106 == $v['type']){
                    if(!isset($v['video_id']) || !$v['video_id']){
                        $this->param['content'][$k]['video_id'] = isset($video_res[$v['file_index']])?$video_res[$v['file_index']]['id']:'0';
                    }
                }elseif(107 == $v['type']){
                    //反地理编码，

                }
                unset($this->param['content'][$k]['file_index']);
            }
        }

        //1.先创建文章
        $result = model('Art','service')->addMain($this->user['id'], $this->param);
        if($this->isError($result)){
            return error($result);
        }

        if(isset($activity_data) && $activity_data){
            $activity_record = model('artActivity')->where('id',$result['data']['id'])->find();
            if($activity_record){
                $activity_record->allowField(true)->where('id',$activity_record['id'])->update($activity_data);
            }else{
                if(!isset($activity_data['id']))$activity_data['id'] = $result['data']['id'];
                $activity_record = model('artActivity')->allowField(true)->data($activity_data)->isUpdate(false)->save();
            }
        }

        //修改对应的路线的信息
        if($this->param['line_id']){
            model('Line')->where('id',$this->param['line_id'])->setField('main_id',$result['data']['id']);

            //保存文章对应的路线中的位置信息
            model('art','service')->saveLineLocation($result['data']['id'],$this->param['line_id']);
        }

        //抽取路线信息和段落的位置信息
//        if(isset($this->param['line_id']) && $this->param['line_id']){
//            $l_data = model('line_item')->where('line_id',$this->param['line_id'])->where('location_name','<>','')->where('location_value','<>','')->select();
//            foreach ($l_data as $t){
//                model('art','service')->saveLocation($result['data']['id'],$t['location_name'],$t['location_value']);
//            }
//            unset($l_data);
//            $p_data  = model('artPara')->where('main_id',$result['data']['id'])->where('para_type','in',[103,107])->where('location_name','<>','')->where('location_value','<>','')->select();
//
//            foreach ($p_data as $t){
//                model('art','service')->saveLocation($result['data']['id'],$t['location_name'],$t['location_value']);
//            }
//        }

        //保存套餐信息
        if($this->has('art_plan') && $this->param['art_plan']){
            $line_result = model('ArtPlan','service')->save( $result['data']['id'], $this->param['art_plan']);
            if ($this->isError($line_result)) {
                return error(['desc' => '付费信息保存失败！']);
            }
        }

        return success(model('Art','service')->read($this->user,$result['data']['id']));
    }

    //put 需要id
    public function update(){
        $file = $this->request->file();
        if(!$this->user){
            return error(['desc'=> '请先登录,然后发表文章！'],'请先登录,然后发表文章！','401');
        }
        if(
            !$this->check('id',['number','r','min:0'])||
            !$this->check('status',['number','min:1'])
        ){
            return error($this->checkError());
        }
        $art_id = $this->param['id'];

        $art = model('Art')->with(['act'])->where('id',$art_id)->find();
        if(!$art){
            return error(['desc'=>'您要修改的文章不存在！']);
        }

        if($art['user_id'] != $this->user['id']){
            return error(['desc'=>'您不能修改该文章！']);
        }
        if(!isset($this->param['art_type'])){
            $this->param['art_type'] = $art['type'];
        }
        if(!isset($this->param['img_id']))
        {
            $this->param['img_id'] = $art['img_id'];
        }

        //只能从 103改为 101 或者 103  或者从101 改成101
        $status = $this->param('status',$art['status']);
        if($this->has('status') && $this->param('status')){
            if($art['status'] != $this->param('status')){
                if((103 == $art['status']) && ( 101 == $this->param('status'))){

                }else{
                    return error(['desc'=>'只能发布文章！']);
                }
            }
        }
        $temp_line_id = $art['line_id'];
        //处理路线
        if($this->has('line')){
            $line_result = model('line','service')->save($this->user['id'], '路线', $this->param['line']);
            if (!$this->isError($line_result)) {
                $this->param['line_id'] = $line_result['data']['id'];
                $temp_line_id =  $line_result['data']['id'];
            }
        }elseif($this->has('line_id') && $this->param['line_id']){
            //该路线和文章路线不同时，拷贝一份路线
            if($this->param('line_id',0) != $art['line_id']) {
                $res_line = model('line', 'service')->copyLine($this->param['line_id']);
                if (!$this->isError($res_line)) {
                    $this->param['line_id'] = $res_line['data']['id'];
                    $temp_line_id = $res_line['data']['id'];
                }
            }else{
                unset($this->param['line_id']);
            }
        }

        //封面图
        if($this->has('img_id') && $this->param['img_id']) {
            if($this->param['img_id'] == $art['img_id']){
                unset($this->param['img_id']);
            }
        }else if(isset($file['img']) && $file['img']){
            if(!is_array($file['img']))$file['img'] = [$file['img']];
            $img_result = model('image','service')->upload($file['img'],$this->param['art_type']);
            $first = current($img_result);
            $this->param['img_id'] = $first['id'];
        }

        if(102 == $this->param['art_type']){
            if(!isset($this->param['img_id'])){
                return error(['desc'=>'请上传封面图！']);
            }
            $activity_data = isset($this->param['activity'])?$this->param['activity']:[];
            if($art['act']) {
                if ($activity_data) {
                    $activity_data = array_merge($art['act'], $activity_data);
                } else {
                    $activity_data = $art['act'];
                }
            }
            $activity_data = $this->activityParam($this->user['id'],$activity_data,$this->param('title'),isset($this->param['img_id'])?$this->param['img_id']:0);
            if(!$activity_data && (101 == $status)){
                return error(['desc'=>'请填写活动相关属性！']);
            }
        }

        //添加同行的伙伴
        if(isset($this->param['companion'])){
            //$old_companion
            $old_companion = model('artCompanion')->where('main_id',$art_id)->select();
            //1.找出删除的，
            $old_user_arr = array_column($old_companion,'user_id','id');
            $del_arr      = array_diff($old_user_arr,$this->param['companion']);
            if($del_arr){
                model('artCompanion')->destroy(array_keys($del_arr));
            }
            //2，找出新增的
            $add = array_diff($this->param['companion'],$old_user_arr);
            if($add){
                $save_companion = array_map(function($a)use($art_id){return [
                    'main_id'   => $art_id,
                    'user_id'   => $a,
                    'del_time'  => '0',
                    'c_time'    => floor(THINK_START_TIME)
                ];},$add);
                if($save_companion){
                    model('artCompanion')->saveAll($save_companion);
                }
            }
        }

        //控制能修改的字段
        $save_data = [];
        if(isset($this->param['img_id']) && ($this->param['img_id'] != $art['img_id']))$save_data['img_id']        = $this->param['img_id'];
        if(isset($this->param['line_id']) && ($this->param['line_id'] != $art['line_id']))$save_data['line_id']    = $this->param['line_id'];
        if($this->has('status') && ($this->param('status') != $art['status'])){
            $save_data['status']        = $this->param['status'];
            $save_data['comment_time']  = floor(THINK_START_TIME);
        }
        if($this->has('title') && ($this->param('title') != $art['title']))$save_data['title']            = $this->param['title'];

        //控制标签
        if($this->has('tag_id') && $this->param['tag_id']){
            if(!is_array($this->param('tag_id'))){
                $tag_id = [$this->param('tag_id')];
            }else{
                $tag_id = $this->param('tag_id');
            }
            $old_tag = model('artTagRelation')->where('art_id',$art_id)->select();
            //
            $old_id_tag = array_column($old_tag,'tag_id','id');
            $del_tag = array_diff($old_id_tag,$tag_id);
            if($del_tag){
                model('artTagRelation')->destroy(array_keys($del_tag));
            }
            $add_tag = array_diff($tag_id,$old_id_tag);
            if($add_tag){
                $save_tag = array_map(function($a)use($art_id){return [
                    'art_id'   => $art_id,
                    'tag_id'   => $a,
                    'del_time' => '0',
                ];},$add_tag);
                if($save_tag){
                    model('artTagRelation')->saveAll($save_tag);
                }
            }
        }

        if($this->has('car_model_id') && $this->param('car_model_id') != $art['car_model_id'])$save_data['car_model_id']      = $this->param['car_model_id'];
        if($this->has('car_name') && $this->param('car_name') != $art['car_name'])$save_data['car_name']                  = $this->param['car_name'];
        if($this->has('car_full_name') && $this->param['car_full_name'] != $art['car_full_name'])$save_data['car_full_name']   = $this->param['car_full_name'];

        $save_res = $art->where('id',$art_id)->update($save_data);

        if(102 == $this->param['art_type'] && isset($activity_data))
        {

            $activity_record = model('artActivity')->where('id',$art_id)->find();
            if($activity_record){
                if(is_object($activity_data)){
                    $activity_record->allowField(true)->where('id',$activity_record['id'])->update($activity_data);
                }else{

                    $activity_record->allowField(true)->where('id',$activity_record['id'])->update($activity_data);
                }
            }else{
                if(!isset($activity_data['id']))$activity_data['id'] = $art_id;
                $activity_record = model('artActivity')->allowField(true)->data($activity_data)->isUpdate(false)->save();
            }
        }

        if($temp_line_id){
            model('art','service')->saveLineLocation($art_id,$temp_line_id);
        }

        //保存二级路线信息
        model('art','service')->saveLineNavV2($art['id'],model('lineItem')->where('line_id',$art['line_id'])->count());
        if(101 == $this->param('show_home_page')){
            //上首页就是保存一条无法删除的位置关系到文章-位置 关系表
            model('art','service')->saveLocation($art_id,'all','',false);
			model('forumTop')->save([
				'art_id'		=> $art_id,
				'order_weight'	=> floor(THINK_START_TIME),
				'status'		=> '102',
				'location_name'	=> 'all',
				'c_time'		=> floor(THINK_START_TIME),
			]);
        }
        return success(model('Art','service')->read($this->user,$this->param['id']));
    }


    //delete 需要id
    public function delete(){
        if(!$this->user){
            return error(['desc'=> '请先登录,然后发表文章！'],'请先登录,然后发表文章！','401');
        }
        if(!$this->check('id',['number','r','min:0'])){
            return error($this->checkError());
        }
        $art = model('art')->where('id',$this->param('id',0))->find();
        if(!$art){
            return error(['desc'=>'没找到该文章！']);
        }
        if($this->user['id'] != $art['user_id']){
            return error(['desc'=>'只能删除自己的文章！']);
        }
        if($art->destroy($art['id'])){
            return success($art);
        }else{
            return error(['desc'=>'删除失败！']);
        }
    }

    //创建一个活动群组
    protected function newGroup($user_id = 0,$title = '',$img = ''){
        if(is_numeric($img) && $img){
            $img = model('image','service')->getUrl( $img);
            if($img){
                $img = isset($img['url'])?$img['url']:'';
            }
        }

        $res = model('Netease','service')->request('create_group',[
            'tname'         => $title,
            'owner'         => $user_id,
            'icon'          => $img,
            'members'       => json_encode([$user_id]),
            'msg'           => '您好，欢迎加入'.$title,
            'magree'        => 0,
            'joinmode'      => 0,
            'beinvitemode'  => 1,
            'invitemode'    => 1,
            'uptinfomode'   => 1,
            'upcustommode'  => 0
        ]);

        if($this->isError($res)){
            return 0;
        }
        return $res['data']['tid'];
    }

    //处理活动参数
    public function activityParam($user_id,$data = [],$title,$img){
        if($data){
            if(!isset($data['tid'])||!$data['tid']){
                $data['tid'] = $this->newGroup($user_id,$title,$img);
            }
            if(isset($data['del_time']))unset($data['del_time']);
//            if(!isset($data['free_ride']))$data['free_ride']=102;
//            if(!isset($data['has_car']))$data['has_car']=102;
//            if(!isset($data['mast_insur']))$data['mast_insur']=102;
        }
        return json_decode(json_encode($data),true);
    }
}

