<?php
namespace app\index\controller;
use think\Request;
class Line extends \app\common\controller\Base
{

    public function read( ){
        $result = model('line','service')->read($this->param('id'),102);
        if($this->isError($result)){
            return error($result);
        }else{
            return success($result);
        }
    }
    //新建路线
    public function save( ){
        if(!$this->user){
            return error(['desc'=>'请登录后再定制路线'],'请登录再定制路线','401');
        }
        if(!$this->check('line',['array|json','r'])){
            return error(['desc'=>'请输入路线信息'],'请输入路线信息');
        }
        $line = $this->param('line');
        if(!is_array($line)){
            $line = json_decode($line,true);
        }
        if(!$line){
            return error(['desc'=>'请选择至少1个地点']);
        }

        $result = model('line','service')->save($this->user['id'],'',$line,$this->param('coordtype','gcj02ll'));
        if($this->isError($result)){
            return error($result);
        }else{
            //更新用户的信息表
            if(model('User','service')->update($this->user['id'],['line_id'=>$result['data']['id']])){
                //同步更新用户路线信息
            }
            //如果main_id和user_id不一样
            return success(model('line','service')->read($result['data']['id']));
        }
    }

    //put 需要id
    public function update(){
        if(!$this->user){
            return error(['desc'=>'请登录后再定制路线'],'请登录再定制路线','401');
        }

        if(!$this->check('id',['number','r','min:0'])){
            return error($this->checkError());
        }
        if(!$this->check('line',['array|json'])){
            return error(['desc'=>'路线数据格式错误'],'路线数据格式错误');
        }
        $line = $this->param('line',[]);

        if(!is_array($line) && is_string($line)){
            $line = json_decode($line,true);
        }

        $result = model('line','service')->update($this->user['id'],$this->param('id','0'),$line,$this->param('coordtype','gcj02ll'));
        if($this->isError($result)){
            return error($result);
        }else{
            //更新用户的信息表
            if(model('User','service')->update($this->user['id'],['line_id'=>$result['data']['id']])){
                //同步更新用户路线信息
            }

            return success(model('line','service')->read($result['data']['id']));
        }
    }
}