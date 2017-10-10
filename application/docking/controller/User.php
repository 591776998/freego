<?php
namespace app\docking\controller;
class User extends \app\common\controller\DockingBase
{
    public function index(){
        $next_id = $this->param('next_id',0);
        $p = $this->param('page','');
        $ps = $this->param('page_size','');
        $data = model('user')->with(['userLogin'=>function($query){
            return $query->where('login_type',102);
        }]);
        if($p && $ps){
            $data->page($p,$ps);
        }
        if($next_id){
            $data->where('id','>',$next_id);
        }
        if($this->param('order')){
            $data->order($this->param('order').' desc');
        }
        return success(array_map(function($u){
            $u = $u->toArray();
            if(isset($u['user_login']['only_sign'])){
                $u['uidmd5'] = $u['user_login']['only_sign'];
            }else{
                $u['uidmd5'] = '';
            }
            unset($u['user_login']);
            return $u;
        },$data->select()));
    }

    public function read(){
        $id = $this->param('id',0);
        $data = model('user')->with(['userLogin'=>function($query){
            return $query->where('login_type',102);
        }])->where('id',$id)->find();
        if($data){
            $data = $data->toArray();
            if(isset($data['user_login']['only_sign'])){
                $data['uidmd5'] = $data['user_login']['only_sign'];
            }else{
                $data['uidmd5'] = '';
            }
            unset($data['user_login']);
        }
        return success($data);
    }
}
