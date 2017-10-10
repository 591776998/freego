<?php
namespace app\index\controller;
use think\Request;
class Place extends \app\common\controller\Base
{
    //地点列表
    public function index(){
        if( !$this->check('place_type',['number','min:0'])||
            !$this->check('order',['string'])||
            !$this->check('key_word','string')||
            !$this->check('lat',['number'])||
            !$this->check('lng',['number'])||
            !$this->check('location_name',['string'])||
            !$this->check('location_value',['string'])||
            !$this->check('range',['number'])||
            !$this->check('page',['number','min:1'])||
            !$this->check('page_size',['number','min:1'])||
            !$this->check('is_collect',['number','min:0'])
        ){
            return error($this->checkError());
        }
        $data = model('place','service')->index($this->user,$this->param('key_word',''),$this->param('place_type',''),$this->param('order',''),$this->param('lat',0),$this->param('lng',0),$this->param('range',0),$this->param('page',1),$this->param('page_size',10),$this->param('is_collect',0),$this->param('location_name',''),$this->param('location_value',''),$this->param('tag_id','0'));
        return success($data);
    }

    //地点详情
    public function read(){
        if(!$this->check('id',['number','r','min:0'])){
            return error($this->checkError());
        }

        $data = model('place','service')->read($this->user,$this->param('id'));
        if($this->isError($data)){
            return error($data);
        }else{
            return success($data);
        }

    }

}