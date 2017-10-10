<?php
namespace app\index\controller;
use think\Request;
class Destination extends \app\common\controller\Base
{

    //1.返回
    public function index(){

        if(!$this->check('location_name',['string'])||
            !$this->check('location_value',['string']))
        {
            return error($this->checkError());
        }

        $data = [
            //banner
            'banner'        => model('Address','service')->info( $this->param('location_name','c_code'), $this->param('location_value','520100')),
            //
            'food_count'    => model('Place','service')->count( 102,$this->param('location_name','c_code'), $this->param('location_value','520100')),

            'scenic_count'  => model('Place','service')->count( 101,$this->param('location_name','c_code'), $this->param('location_value','520100')),
            //导航
            'nav'           => model('Place','service')->map(),
        ];

        return success($data);
    }

}