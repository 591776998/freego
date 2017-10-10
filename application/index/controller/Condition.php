<?php
namespace app\index\controller;
use think\Request;
class Condition extends \app\common\controller\Base
{
    public function index(){
        if(!$this->check('type',['number','min:0'])||
            !$this->check('sub_type',['number','min:0'])||
            !$this->check('code_name',['string'])||
            !$this->check('code_value',['string'])){
            return error($this->checkError());
        }

        $result = [];

        $tag = model('ArtTag','service')->index($this->param('type',0),$this->param('sub_type',0));
        if(!$this->isError($tag)){
            $result['tag'] = $tag['data'];
        }else{
            $result['tag'] = [];
        }
        $order = model('ArtTag','service')->order($this->param('type',0),$this->param('sub_type',0));
        if(!$this->isError($tag)){
            $result['order'] = $order['data'];
        }else{
            $result['order'] = [];
        }

        $address = model('Address','service')->index(102 ,'all','',$this->param('code_name',''),$this->param('code_value',''));
        if($address){
            $result['address'] = $address['data'];
        }else{
            $result['address'] = [];
        }

        return success($result);
    }

}