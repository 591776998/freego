<?php
namespace app\index\service;
class Services extends \app\common\service\Base
{

    //返回服务列表
    public function index($now_user, $place_id = 0, $is_collect = 0, $p = 1, $ps = 10){
        $now_user_id = isset($now_user['id'])?$now_user['id']:'0';
        $model = model('services')->with([
            'img',
            'collect'
        ]);
        if($place_id){
            $model->where('place_id',$place_id);
        }
        if((101 == $is_collect) && $now_user){
            $sub_sql = model('collect')->field('target_id')->where('user_id',$now_user_id)->where('type',106)->select(false);
            $model->where('id','exp','in('.$sub_sql.')');
        }

        if($p && $ps){
            $model->page($p,$ps);
        }
        $data = $model->select();
        foreach ($data as $k => $v){
            $data[$k]['share']          = $this->getShare($v);
            $data[$k]['content_str']    = '';//trim(strip_tags($v['content']));
        }
        return $this->success($data);
    }

    public function read($now_user,$id){
        if($now_user){
            $collect_func = function($query)use($now_user){
                $query->where('user_id',$now_user['id']);
            };
        }else{
            $collect_func = [];
        }
        $data = model('services')->where('id',$id)->with([
            'content',
            'collect'=>$collect_func,
            'img',
            'plan'=>function($query){
                return $query->with(['img'])->where('status','<>','99');
            }
        ])->find();
        if(!$data){
            return $this->error(['desc'=>'未找到该服务']);
        }
        $data['share']      = $this->getShare($data);
        return $this->success($data);

    }

    protected function getShare($data){
        return [
            'title'     => $data['title'],
            'content'   => $data['desc'],
            'img_url'   => web_url('/image/read',['id'=>$data['img_id'],'width'=>80,'height'=>80],true),
            'url'       => web_url('services?id='.$data['id'],[],true)//'http://www.baidu.com'//"http://baidu.com"
        ];
    }
}