<?php
namespace app\branch\controller;
class Top extends \app\common\controller\BranchBase
{
    //推首记录
    public function index(){
//        return success([
//            'id'            => 1,
//            'art_id'        => 1,
//            'order_weight'  => 1,
//            'status'        => 101,
//            'del_time'      => 0,
//            'exp_time'      => 10,
//            't_code'        => '',
//            'c_code'       => '',
//            'p_code'       => '',
//            's_code'       => '',
//            'city'          => '',
//            'location_name'       => '',
//            'location_value'       => '',
//            'admin_id'       => '0',
//            'c_time'        => floor(THINK_VERSION),
//            'art_title' => 'xxxx文章',
//        ]);
        $branch_id = $this->param('branch_id',0);
        if(!$branch_id){
            $branch_id = $this->branch['branch_id'];
        }
        $b = model('branch')->where('branch_id',$branch_id)->find();

        if(!$b){
            return [];
        }else{
            $n = $b['location_name'];
            $v = $b['location_value'];
        }
        $sub_sql = model('forumTop')
            ->distinct(true)
            ->field('art_id')
            ->where('location_name',$n)
            ->where('location_value',$v)
            ->where('status',101)
            ->where('exp_time','>',floor(THINK_START_TIME))
            ->order('order_weight desc')
            ->order('id desc')
            ->select(false);

        $data = model('art')
            ->with(['isTop'=>function($query)use($n,$v){
                return $query->where('location_name',$n)->where('location_value',$v);
            }])
            ->where('id','exp','in('.$sub_sql.')')
            ->page($this->param('page',1),$this->param('page_size',10))

            ->select();

        return success($data);
    }

    public function delete(){
        $data = model('forumTop')->where('id',$this->param('id',0))->find();
        if(!$data){
            return error(['desc'=>'未找到该记录']);
        }else{
            if(model('forumTop')->destroy($data['id'])){
                return success([]);
            }else{
                return error(['desc'=>'删除失败']);
            }
        }
    }

    public function save(){
        if(!$this->branch)return $this->errorLogin();
        if(!$this->check('id',['number','r','min:0'])){
            return error($this->checkError());
        }
        $art_id = $this->param('id',0);
        $exp_time = $this->param('exp_time',floor(THINK_START_TIME)+$this->param('exp_time','604800'));

        $branch_id = $this->param('branch_id',[]);
        if($branch_id && is_array($branch_id)){
            if($this->push($art_id,$branch_id,$exp_time)){
                return success();
            }else{
                return error(['desc'=>'推送失败了']);
            }
        }else{
            $location_name = $this->branch['location_name'];
            $location_value = $this->branch['location_value'];

            $record = model('forumTop')->where('art_id',$art_id)->where('location_name',$location_name)->where('location_value',$location_value)->find();
            if(!$record){
                if(model('forumTop')->data([
                    'art_id'            => $art_id,
                    'order_weight'      => floor(THINK_START_TIME),
                    'status'            => '101',
                    'exp_time'          => $exp_time,
                    'location_name'     => $location_name,
                    'location_value'    => $location_value,
                    'admin_id'          => 0,//$this->branch[]
                    'c_time'            => floor(THINK_START_TIME),
                ])->save()){
                    return success([]);
                }else{
                    return error(['desc'=>'设置失败']);
                }
            }else{
                if(102 != $record['status'])
                {
                    return error(['desc'=>'错误的状态']);
                }

                if(model('forumTop')->where('id',$record['id'])->update(['order_weight'=>floor(THINK_START_TIME),'status'=>'101','exp_time'=>$exp_time,'c_time'=>floor(THINK_START_TIME)])){
                    return success([]);
                }else{
                    return error(['desc'=>'没有做任何修改']);
                }
            }
        }

    }

    //单个推首
    protected function pushOne($art_id,$location_name,$location_value,$exp_time){
        $record = model('forumTop')->where('art_id',$art_id)->where('location_name',$location_name)->where('location_value',$location_value)->find();
        if(!$record){
            if(model('forumTop')->data([
                'art_id'            => $art_id,
                'order_weight'      => floor(THINK_START_TIME),
                'status'            => '101',
                'exp_time'          => $exp_time,
                'location_name'     => $location_name,
                'location_value'    => $location_value,
                'admin_id'          => 0,//$this->branch[]
                'c_time'            => floor(THINK_START_TIME),
            ])->isUpdate(false)->save()){
                return true;
            }else{
                return false;
            }
        }else{
            if(102 != $record['status']){
                return false;
            }
            if(model('forumTop')->where('id',$record['id'])->update(['order_weight'=>floor(THINK_START_TIME),'status'=>'101','exp_time'=>$exp_time,'c_time'=>floor(THINK_START_TIME)])){
                return true;
            }else{
                return false;
            }
        }
    }

    //批量推首
    protected function push($art_id,$branch_id,$exp_time){
        $b = model('branch')->where('branch_id','in',$branch_id)->select();
        if($b){
            $save = array_map(function ($vo)use($art_id,$exp_time){
                return [
                    'art_id'        => $art_id,
                    'location_name' => $vo['location_name'],
                    'location_value'=> $vo['location_value'],
                    'exp_time'      => $exp_time,
                ];
            },$b);

            if($save){
                foreach ($save as $vo){
                    $this->pushOne($vo['art_id'],$vo['location_name'],$vo['location_value'],$vo['exp_time']);
                }
            }
        }
        return true;
    }

}