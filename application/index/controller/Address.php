<?php
namespace app\index\controller;
use think\Request;
class Address extends \app\common\controller\Base
{
    //
    public function index(){

        if(!$this->check('type',['number','min:0'])|| 
            !$this->check('depth',['number','min:0'])||
            !$this->check('code_name',['string'])||
            !$this->check('code_value',['string']))
        {
            return error($this->checkError());
        }
        return success(model('Address','service')->index($this->param('type',101),$this->param('code_name','all'),$this->param('code_value',''),$this->param('depth',3)));
    }


}