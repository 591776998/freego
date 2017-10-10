<?php
namespace app\index\controller;
use think\Request;
class Directory extends \app\common\controller\Base
{

    //1.通讯录好友列表
    public function index(){
        if(!isset($this->param['contacts'])){
            return success([]);
        }

        $data = $this->filterPhoneNumber($this->param['contacts']);

        //1.查询这些
        if(!$data){
            return success([]);
        }
        if(!$this->user){
            return error(['desc'=> '请先登录,然后查看通讯录好友！'],'请先登录,然后查看通讯录好友！','401');
        }

        $u_l_arr = model('userLogin')->where('phone','in',array_map(function($d){return $d['phone'];},$data))->select();

        $id_arr = [];
        $id_name_arr = [];
        foreach ($u_l_arr as $k => $v){
            $id_arr[] = $v['id'];
            foreach ($data as $key => $val){
                if($val['phone'] == $v['phone']){
                    $id_name_arr[$v['id']] = $val['name'];
                    break;
                }
            }
        }

        $all = model('user_follow')->where(function ($query)use($id_arr){
            $query->where(function ($query)use($id_arr){
                $query->where('user_id',$this->user['id'])->where('b_user_id','in',$id_arr);
            })->whereOr(function($query)use($id_arr){
                $query->where('b_user_id',$this->user['id'])->where('user_id','in',$id_arr);
            });
        })->select();

        //求出交集
        $f = array_intersect(array_map(function($a){
            return $a['user_id'];
        },$all),array_map(function($a){
            return $a['b_user_id'];
        },$all));

        $f[] = $this->user['id'];

        //除去这些人里的好友
        foreach ($id_arr as $k => $v){
            if(in_array($v,$f)){
                unset($id_arr[$k]);
            }
        }

        if($id_arr){
            $model = model('user','service')->getUser($this->user ,array_values($id_arr));
            $model = array_map(function($u)use($id_name_arr){
                $u = $u->toArray();
                $u['phone_name'] = isset($id_name_arr[$u['id']])?$id_name_arr[$u['id']]:'';
                return $u;
            },$model);
        }else{
            $model = [];
        }
        return success($model);
    }

    //过滤号码
    protected function filterPhoneNumber($arr){
        $res = [];
        foreach ($arr as $k => $v){
            $phone = trim(strtr($v['phone'],[' '=>'','-'=>'','/'=>'']));
            if(is_numeric($phone) ){
                $name  = trim($v['name']);
                $res[] = [
                    'phone' => $phone,
                    'name'  => $name
                ];
            }
        }
        return $res;
    }

}