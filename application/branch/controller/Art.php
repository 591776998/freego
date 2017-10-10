<?php
namespace app\branch\controller;
class Art extends \app\common\controller\BranchBase
{
    public function index()
    {
        if(!$this->branch)return $this->errorLogin();
        $location_name = $this->branch['location_name'];
        $location_value = $this->branch['location_value'];

        $data = model('art')->field('art.*')->with([
            'img',
            'user',
            'is_top'=>function($query)use($location_name,$location_value){
                return $query->where('location_name',$location_name)->where('location_value',$location_value);
            },
            'is_best'=>function($query)use($location_name,$location_value){
                return $query->where('location_name',$location_name)->where('location_value',$location_value);
            }
            ]);
        if($this->has('type') && $this->param('type',0)){
            $data->where('type',$this->param('type',0));
        }

        //筛选出当前论坛的文章
        if($location_name){
            if('all' == $location_name){

                $art_id_sql = model('forumTop')->field('art_id')->where('status','<>',100)->select(false);

            }else{
                //获取子集和当前的位置的
                $art_id_sql = model('locationArtRelation')->field('art_id')
                    ->where($location_name,$location_value)
                    ->select(false);
            }
            $data->where('art.id','exp','in('.$art_id_sql.')');
        }

        $data = $data
                ->join('forum_top top',"art.id = top.art_id and (top.status=101 or top.status=102) and top.del_time=0 and (top.exp_time=0 or top.exp_time>UNIX_TIMESTAMP()) and (top.location_name='$location_name') and (top.location_value='$location_value')",'LEFT')
                ->field('top.order_weight,top.status top_status')
                ->order('top.order_weight desc')
                ->order('id desc')
                ->page($this->param('page'),$this->param('page_size'))
                ->select();

        $data = array_map(function($vo){
            if(is_null($vo['order_weight'])){
                $vo['order_weight'] = '0';
            }
            return $vo;
        },$data);
        return success($data);
    }

    public function main(){
        return $this->fetch();
    }

    public function main1(){
        return $this->fetch();
    }

    //
    public function repulse(){
        $this->assign('id',$this->param('id',0));
        return $this->fetch();
    }

    public function rpush(){
        $branch = [
            'id'             => $this->branch['branch_id'],
            'branch_id'      => $this->branch['branch_id'],
            'name'           => $this->branch['name'],
            'location_name'  => $this->branch['location_name'],
            'location_value' => $this->branch['location_value']
        ];

        $this->assign('branch',$branch);
        $this->assign('id',$this->param('id',0));
        return $this->fetch();
    }

    public function putrecord(){
        $branch = [
            'id'             => $this->branch['branch_id'],
            'branch_id'      => $this->branch['branch_id'],
            'name'           => $this->branch['name'],
            'location_name'  => $this->branch['location_name'],
            'location_value' => $this->branch['location_value']
        ];

        $this->assign('branch',$branch);
        return $this->fetch();
    }
}