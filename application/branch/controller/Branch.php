<?php
namespace app\branch\controller;
class Branch extends \app\common\controller\BranchBase
{
    public function index(){

        $id = $this->param('branch_id',0);
        $art_id = $this->param('art_id',0);

        if(!$id)$id = $this->branch['branch_id'];

        $data = model('branch')->where('p_id',$id);
        if($art_id){
            $locationArtRelation = model('locationArtRelation')->where('art_id',$art_id)->select();
            $condition = $this->allKeyValue($locationArtRelation);
            foreach ($condition as $k => $v){
                if($v){
                    $data->where($k,'in',$v);
                }
            }
        }
        $data = $data->select();
        return success($data);
    }

    //获取全部的value和key
    protected function allKeyValue($data){
        $key = [
            't_code'    => [],
            'c_code'    => [],
            'p_code'    => [],
            's_code'    => [],
            'city'      => [],
        ];
        foreach ($data as $k1 => $v1){
            foreach ($key as $k => $v){
                if(isset($v1[$k]) && $v1[$k]){
                    $key[$k][] = $v1[$k];
                }
            }
        }
        return $key;
    }

    //
    public function update(){
        if(!$this->check('id',['number','r','min:0'])){
            return error(['desc'=>'错误的id']);
        }
        if(!$this->check('pwd',['string','r'])){
            return error(['desc'=>'请输入正确的密码']);
        }
        $r = model('branchLogin')->where('id',$this->param('id',0))->find();
        if(!$r){
            return error(['desc'=>'未找到该账号']);
        }
        $r->pwd = $this->param('pwd','');
        if($r->save()){
            return success($r);
        }else{
            return error(['desc'=>'没有做任何修改']);
        }
    }

    //新建
    public function save(){
        if(!$this->check('branch_id',['number','r','min:0'])){
            return error(['desc'=>'错误的id']);
        }
        if(!$this->check('pwd',['string','r'])){
            return error(['desc'=>'请输入正确的密码']);
        }
        if(!$this->check('phone',['number','r','phone'])){
            return error(['desc'=>'请输入正确的手机号']);
        }
        $r = model('branchLogin')->where('phone',$this->param('phone',''))->find();
        if($r){
            return error(['desc'=>'该手机号已经注册']);
        }else{
            $res = model('branchLogin')->data([
                'branch_id' => $this->param('branch_id',0),
                'name'      => $this->param('phone',''),
                'role'      => $this->param('role',102),
                'phone'     => $this->param('phone',''),
                'pwd'       => $this->param('pwd','')
            ])->isUpdate(false)->save();
            if($res){
                return success($res);
            }else{
                return error(['desc'=>'创建失败']);
            }
        }
    }

    //删除
    public function delete(){
        if(!$this->check('id',['number','r','min:0'])){
            return error(['desc'=>'错误的id']);
        }

        $r = model('branchLogin')->where('id',$this->param('id',''))->find();
        if(!$r){
            return error(['desc'=>'未找到该管理员']);
        }else{
            if($r->destroy($r->id)){
                return success($r);
            }else{
                return error(['desc'=>'创建失败']);
            }
        }
    }

}